<?php

namespace App\Services\Calendar;

use App\Models\BookingOrder;
use App\Models\CalendarSetting;
use Illuminate\Support\Str;

class ICalService
{
    // Generate .ics file content for the given INSTRUCTOR/ORG (not the buyer).
    // BookingOrder belongs to the provider through seller_id/booking.creator_id,
    // which is the same owner CalendarSyncService uses for outbound sync.
    public function generateIcs(int $userId): string
    {
        $bookings = BookingOrder::whereIn('status', ['success', 'confirmed'])
            ->where(function ($q) use ($userId) {
                $q->where('seller_id', $userId)
                    ->orWhereHas('booking', fn($qq) => $qq->where('creator_id', $userId));
            })
            ->with(['booking', 'buyer'])
            ->get();

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Rocket LMS//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";

        foreach ($bookings as $order) {
            if (($order->seller_id ?: ($order->booking->creator_id ?? null)) != $userId) {
                continue;
            }

            $resource = null;
            if (!empty($order->resource_id)) {
                $resource = \App\Models\BookingResource::find($order->resource_id);
            }

            $start = $this->toIcsDate($order->booking_date, $order->start_time ?? null);
            $end   = $this->toIcsDate($order->booking_date, $order->end_time ?? null);

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:booking-order-{$order->id}@rocketlms\r\n";
            $ics .= "SUMMARY:" . $this->escape($order->booking->title ?? '') . "\r\n";
            if (!empty($resource->name)) {
                $ics .= "LOCATION:" . $this->escape($resource->name) . "\r\n";
            }
            $ics .= "DTSTART:{$start}\r\n";
            $ics .= "DTEND:{$end}\r\n";
            $ics .= "STATUS:CONFIRMED\r\n";
            $ics .= "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n";
            $ics .= "END:VEVENT\r\n";
        }

        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    // Generate signed token for iCal URL (also used on regenerate - overwrites
    // the old token so the previous feed URL stops working immediately).
    public function generateToken(int $userId): string
    {
        $token = Str::random(64);

        CalendarSetting::updateOrCreate(
            ['user_id' => $userId, 'provider' => 'ical'],
            ['ical_token' => $token]
        );

        return $token;
    }

    private function toIcsDate(?string $date, ?string $time): string
    {
        if (empty($date)) {
            return now()->utc()->format('Ymd\THis\Z');
        }

        $value = $time ? "{$date} {$time}" : $date;

        try {
            return \Illuminate\Support\Carbon::parse($value)->utc()->format('Ymd\THis\Z');
        } catch (\Throwable $e) {
            return now()->utc()->format('Ymd\THis\Z');
        }
    }

    private function escape(string $value): string
    {
        return str_replace([',', ';', "\n"], ['\,', '\;', '\n'], $value);
    }
}
