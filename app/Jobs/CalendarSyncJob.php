<?php

namespace App\Jobs;

use App\Models\BookingOrder;
use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarMapping;
use App\Models\CalendarSetting;
use App\Services\Calendar\CalendarSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalendarSyncJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        private CalendarIntegration $integration,
        private BookingOrder $order,
        private string $action
    ) {}

    public function handle(CalendarSyncService $syncService): void
    {
        $service = $syncService->getService($this->integration->provider);

        $settings = CalendarSetting::where('user_id', $this->integration->user_id)
            ->where('provider', $this->integration->provider)
            ->first();

        // Status filter: only skip when a filter is explicitly configured and doesn't include this status
        if ($settings && !empty($settings->sync_status_filter)
            && !in_array($this->order->status, $settings->sync_status_filter, true)) {
            return;
        }

        $bookingData = $this->buildBookingData();

        $mapping = CalendarMapping::where([
            'user_id'            => $this->integration->user_id,
            'rocket_entity_type' => 'booking_order',
            'rocket_entity_id'   => $this->order->id,
            'provider'           => $this->integration->provider,
        ])->first();

        try {
            if ($this->action === 'create' && !$mapping) {
                $providerEventId = $service->createEvent($this->integration, $bookingData);

                if ($providerEventId) {
                    CalendarMapping::create([
                        'user_id'            => $this->integration->user_id,
                        'rocket_entity_type' => 'booking_order',
                        'rocket_entity_id'   => $this->order->id,
                        'rocket_event_id'    => $this->order->id,
                        'provider'           => $this->integration->provider,
                        'provider_event_id'  => $providerEventId,
                        'last_synced_at'     => now(),
                    ]);
                }
            } elseif ($this->action === 'create' && $mapping) {
                // Already synced - avoid duplicate events, fall back to update
                $service->updateEvent($this->integration, $mapping->provider_event_id, $bookingData);
                $mapping->update(['last_synced_at' => now()]);
            } elseif ($this->action === 'update' && $mapping) {
                $service->updateEvent($this->integration, $mapping->provider_event_id, $bookingData);
                $mapping->update(['last_synced_at' => now()]);
            } elseif ($this->action === 'cancel' && $mapping) {
                $service->cancelEvent($this->integration, $mapping->provider_event_id);
                $mapping->delete();
            }

            CalendarLog::create([
                'user_id'          => $this->integration->user_id,
                'provider'         => $this->integration->provider,
                'action'           => $this->action,
                'status'           => 'success',
                'booking_order_id' => $this->order->id,
            ]);
        } catch (\Exception $e) {
            CalendarLog::create([
                'user_id'          => $this->integration->user_id,
                'provider'         => $this->integration->provider,
                'action'           => $this->action,
                'status'           => 'failed',
                'booking_order_id' => $this->order->id,
                'error_message'    => $e->getMessage(),
            ]);

            throw $e; // triggers retry with backoff
        }
    }

    private function buildBookingData(): array
    {
        $item = $this->order->items->first();

        return [
            'id'             => $this->order->id,
            'customer_name'  => $this->order->user->name ?? '',
            'customer_email' => $this->order->user->email ?? '',
            'title'          => $item->booking->title ?? '',
            'status'         => $this->order->status,
            'start_at'       => ($item->booking_date ?? '') . 'T' . ($item->start_time ?? '00:00:00'),
            'end_at'         => ($item->booking_date ?? '') . 'T' . ($item->end_time ?? '00:00:00'),
            'resource_name'  => $item->resource->name ?? '',
        ];
    }
}
