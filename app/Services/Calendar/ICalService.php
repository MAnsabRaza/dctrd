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

            $date = $order->booking_date ?: data_get($order->specifications, 'booking_date');
            $startTime = $order->start_time ?: data_get($order->specifications, 'start_time');
            $endTime = $order->end_time ?: data_get($order->specifications, 'end_time');

            if (empty($date) || empty($startTime)) {
                continue;
            }

            $start = $this->toIcsDate($date, $startTime);
            $end   = $this->toIcsDate($date, $endTime, $startTime);
            $summary = trim(($order->buyer->full_name ?? $order->buyer->name ?? '') . ' - ' . ($order->booking->title ?? 'Booking'));
            $description = $this->description($order);

            $ics .= "BEGIN:VEVENT\r\n";
            $ics .= "UID:booking-order-{$order->id}@rocketlms\r\n";
            $ics .= "SUMMARY:" . $this->escape($summary) . "\r\n";
            $ics .= "DESCRIPTION:" . $this->escape($description) . "\r\n";
            if (!empty($resource->name)) {
                $ics .= "LOCATION:" . $this->escape($resource->name) . "\r\n";
            }
            $ics .= "DTSTART:{$start}\r\n";
            $ics .= "DTEND:{$end}\r\n";
            $ics .= "STATUS:CONFIRMED\r\n";
            $ics .= "SEQUENCE:" . $this->sequence($order) . "\r\n";
            $ics .= "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n";
            if (!empty($order->buyer->email)) {
                $ics .= "ATTENDEE;CN=" . $this->escape($order->buyer->full_name ?? $order->buyer->name ?? '') . ":MAILTO:" . $this->escape($order->buyer->email) . "\r\n";
            }
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

    private function toIcsDate(?string $date, ?string $time, ?string $fallbackStartTime = null): string
    {
        if (empty($date)) {
            return now()->utc()->format('Ymd\THis\Z');
        }

        if (empty($time) && !empty($fallbackStartTime)) {
            try {
                return \Illuminate\Support\Carbon::parse("{$date} {$fallbackStartTime}")
                    ->addHour()
                    ->utc()
                    ->format('Ymd\THis\Z');
            } catch (\Throwable $e) {
                return now()->utc()->addHour()->format('Ymd\THis\Z');
            }
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
        return str_replace(["\\", ',', ';', "\r\n", "\r", "\n"], ['\\\\', '\,', '\;', '\n', '\n', '\n'], $value);
    }

    private function description(BookingOrder $order): string
    {
        return implode("\n", array_filter([
            'Booking ID: ' . $order->id,
            'Customer: ' . ($order->buyer->full_name ?? $order->buyer->name ?? ''),
            'Email: ' . ($order->buyer->email ?? ''),
            'Status: ' . $order->status,
            'Product: ' . ($order->booking->title ?? ''),
        ]));
    }

    private function sequence(BookingOrder $order): int
    {
        $value = $order->updated_at ?? $order->created_at ?? null;

        if ($value instanceof \DateTimeInterface) {
            return $value->getTimestamp();
        }

        if (is_numeric($value)) {
            return (int) $value;
        }

        $timestamp = strtotime((string) $value);

        return $timestamp ?: time();
    }
}
