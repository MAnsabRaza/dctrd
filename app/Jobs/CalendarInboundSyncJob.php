<?php

namespace App\Jobs;

use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarMapping;
use App\Services\Calendar\CalendarSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Runs every 15 minutes via the scheduler.
 * Rocket LMS bookings are the source of truth: this job only detects and LOGS
 * external changes/conflicts (e.g. an instructor manually edited or deleted the
 * event in Google/Outlook). It never overwrites a Rocket LMS booking based on
 * external calendar data.
 */
class CalendarInboundSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

    public function handle(CalendarSyncService $syncService): void
    {
        $integrations = CalendarIntegration::where('status', 'connected')
            ->whereIn('provider', ['google', 'outlook'])
            ->get();

        foreach ($integrations as $integration) {
            $this->syncIntegration($integration, $syncService);
        }
    }

    private function syncIntegration(CalendarIntegration $integration, CalendarSyncService $syncService): void
    {
        $service = $syncService->getService($integration->provider);

        $result = $service->fetchChanges($integration, $integration->sync_token);

        if ($result['full_resync_required']) {
            // syncToken expired on provider side - clear it so next run does a full pull
            $integration->update(['sync_token' => null]);
        }

        $mappings = CalendarMapping::where('user_id', $integration->user_id)
            ->where('provider', $integration->provider)
            ->get()
            ->keyBy('provider_event_id');

        foreach ($result['items'] as $externalEvent) {
            $eventId = $externalEvent['id'] ?? null;
            if (!$eventId) {
                continue;
            }

            $mapping = $mappings->get($eventId);
            $isCancelled = ($externalEvent['status'] ?? null) === 'cancelled';

            if (!$mapping) {
                // Event exists externally but Rocket LMS has no record of it - informational only
                continue;
            }

            if ($isCancelled) {
                CalendarLog::create([
                    'user_id'          => $integration->user_id,
                    'provider'         => $integration->provider,
                    'action'           => 'sync',
                    'status'           => 'pending',
                    'booking_order_id' => $mapping->rocket_entity_id,
                    'request_data'     => ['provider_event_id' => $eventId],
                    'response_data'    => ['message' => 'Event was cancelled/removed externally. Rocket LMS booking kept unchanged (source of truth).'],
                ]);

                continue;
            }

            // Detect drift between provider event and what we last synced
            $providerUpdatedAt = $externalEvent['updated'] ?? $externalEvent['lastModifiedDateTime'] ?? null;
            if ($providerUpdatedAt && $mapping->last_synced_at && strtotime($providerUpdatedAt) > $mapping->last_synced_at->timestamp) {
                CalendarLog::create([
                    'user_id'          => $integration->user_id,
                    'provider'         => $integration->provider,
                    'action'           => 'sync',
                    'status'           => 'pending',
                    'booking_order_id' => $mapping->rocket_entity_id,
                    'request_data'     => ['provider_event_id' => $eventId],
                    'response_data'    => ['message' => 'Conflict detected: event was modified externally. Rocket LMS remains source of truth; no changes applied.'],
                ]);
            }
        }

        $integration->update(['last_sync_at' => now()]);
    }
}
