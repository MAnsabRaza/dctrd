<?php

namespace App\Services\Calendar;

use App\Jobs\CalendarSyncJob;
use App\Models\BookingOrder;
use App\Models\CalendarIntegration;

class CalendarSyncService
{
    public function __construct(
        private GoogleCalendarService $google,
        private OutlookCalendarService $outlook,
        private ICalService $ical
    ) {}

    // MAIN: Sync booking to all connected calendars (google/outlook only - ical is pull-based)
    public function syncBooking(BookingOrder $order, string $action): void
    {
        $sellerId = optional(optional($order->items->first())->booking)->creator_id;

        if (!$sellerId) {
            return;
        }

        $integrations = CalendarIntegration::where('user_id', $sellerId)
            ->where('status', 'connected')
            ->whereIn('provider', ['google', 'outlook'])
            ->get();

        foreach ($integrations as $integration) {
            CalendarSyncJob::dispatch($integration, $order, $action);
        }
    }

    // Get correct service for provider
    public function getService(string $provider)
    {
        return match ($provider) {
            'google'  => $this->google,
            'outlook' => $this->outlook,
            'ical'    => $this->ical,
            default   => throw new \Exception("Unknown provider: {$provider}"),
        };
    }
}
