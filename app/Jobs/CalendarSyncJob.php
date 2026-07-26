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
        $this->order->loadMissing(['booking', 'buyer', 'seller']);

        $service = $syncService->getService($this->integration->provider);

        $settings = CalendarSetting::where('user_id', $this->integration->user_id)
            ->where('provider', $this->integration->provider)
            ->first();

        $action = $this->normaliseAction($this->action, $this->order->status);
        $statusAliases = $this->statusAliases($this->order->status);

        // Status filter: only skip when a filter is explicitly configured and doesn't include this status
        if ($action !== 'cancel' && $settings && !empty($settings->sync_status_filter)
            && empty(array_intersect($statusAliases, $settings->sync_status_filter))) {
            return;
        }

        $bookingData = $action === 'cancel' ? [] : $this->buildBookingData();

        if ($action !== 'cancel' && (empty($bookingData['start_at']) || empty($bookingData['end_at']))) {
            CalendarLog::create([
                'user_id'          => $this->integration->user_id,
                'provider'         => $this->integration->provider,
                'action'           => $action,
                'status'           => 'failed',
                'booking_order_id' => $this->order->id,
                'error_message'    => 'Booking order is missing booking date/start/end time.',
            ]);

            return;
        }

        $mapping = CalendarMapping::where([
            'user_id'            => $this->integration->user_id,
            'rocket_entity_type' => 'booking_order',
            'rocket_entity_id'   => $this->order->id,
            'provider'           => $this->integration->provider,
        ])->first();

        try {
            if ($action === 'create' && !$mapping) {
                $providerEventId = $service->createEvent($this->integration, $bookingData);

                if (!$providerEventId) {
                    throw new \RuntimeException("Calendar {$this->integration->provider} event create failed.");
                }

                CalendarMapping::create([
                    'user_id'            => $this->integration->user_id,
                    'rocket_entity_type' => 'booking_order',
                    'rocket_entity_id'   => $this->order->id,
                    'rocket_event_id'    => $this->order->id,
                    'provider'           => $this->integration->provider,
                    'provider_event_id'  => $providerEventId,
                    'last_synced_at'     => now(),
                ]);
            } elseif ($action === 'create' && $mapping) {
                // Already synced - avoid duplicate events, fall back to update
                if (!$service->updateEvent($this->integration, $mapping->provider_event_id, $bookingData)) {
                    throw new \RuntimeException("Calendar {$this->integration->provider} event update failed.");
                }
                $mapping->update(['last_synced_at' => now()]);
            } elseif ($action === 'update' && $mapping) {
                if (!$service->updateEvent($this->integration, $mapping->provider_event_id, $bookingData)) {
                    throw new \RuntimeException("Calendar {$this->integration->provider} event update failed.");
                }
                $mapping->update(['last_synced_at' => now()]);
            } elseif ($action === 'cancel' && $mapping) {
                if (!$service->cancelEvent($this->integration, $mapping->provider_event_id)) {
                    throw new \RuntimeException("Calendar {$this->integration->provider} event cancel failed.");
                }
                $mapping->delete();
            }

            CalendarLog::create([
                'user_id'          => $this->integration->user_id,
                'provider'         => $this->integration->provider,
                'action'           => $action,
                'status'           => 'success',
                'booking_order_id' => $this->order->id,
            ]);
        } catch (\Exception $e) {
            CalendarLog::create([
                'user_id'          => $this->integration->user_id,
                'provider'         => $this->integration->provider,
                'action'           => $action,
                'status'           => 'failed',
                'booking_order_id' => $this->order->id,
                'error_message'    => $e->getMessage(),
            ]);

            throw $e; // triggers retry with backoff
        }
    }

    // Called automatically by the queue worker once all retry attempts (3, with
    // 1/5/15 min backoff) have been exhausted. Spec section 9 requires the
    // instructor to be notified when a sync permanently fails.
    public function failed(\Throwable $exception): void
    {
        CalendarLog::create([
            'user_id'          => $this->integration->user_id,
            'provider'         => $this->integration->provider,
            'action'           => $this->action,
            'status'           => 'failed',
            'booking_order_id' => $this->order->id,
            'error_message'    => 'All retry attempts exhausted: ' . $exception->getMessage(),
        ]);

        // TODO: hook this up to your notification system once available, e.g.:
        // $this->integration->user?->notify(new CalendarSyncFailedNotification($this->order, $this->integration->provider));
    }

    private function buildBookingData(): array
    {
        $booking = $this->order->booking;
        $resource = null;

        if (!empty($this->order->resource_id)) {
            $resource = \App\Models\BookingResource::find($this->order->resource_id);
        }

        $startAt = null;
        $endAt = null;

        if (!empty($this->order->booking_date) && !empty($this->order->start_time)) {
            $startAt = $this->formatDateTime($this->order->booking_date, $this->order->start_time);
        }

        if (!empty($this->order->booking_date) && !empty($this->order->end_time)) {
            $endAt = $this->formatDateTime($this->order->booking_date, $this->order->end_time);
        }

        return [
            'id'             => $this->order->id,
            'customer_name'  => $this->order->buyer->full_name ?? $this->order->buyer->name ?? '',
            'customer_email' => $this->order->buyer->email ?? '',
            'title'          => $booking->title ?? '',
            'status'         => $this->order->status,
            'start_at'       => $startAt,
            'end_at'         => $endAt,
            'resource_name'  => $resource->name ?? '',
            'location'       => $resource->name ?? ($booking->address ?? ''),
        ];
    }

    private function formatDateTime(string $date, string $time): string
    {
        return \Illuminate\Support\Carbon::parse("{$date} {$time}")->utc()->toIso8601String();
    }

    private function normaliseAction(string $action, ?string $status): string
    {
        return in_array($status, ['canceled', 'cancelled'], true) ? 'cancel' : $action;
    }

    private function statusAliases(?string $status): array
    {
        return match ($status) {
            'success' => ['success', 'confirmed'],
            'confirmed' => ['confirmed', 'success'],
            'canceled', 'cancelled' => ['canceled', 'cancelled', 'cancel'],
            default => array_filter([(string) $status]),
        };
    }
}
