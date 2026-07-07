<?php

namespace App\Services\Calendar;

use App\Models\BookingOrder;
use App\Models\CalendarSetting;
use Illuminate\Support\Str;

class ICalService
{
    // Generate .ics file content
    public function generateIcs(int $userId): string
    {
        $bookings = BookingOrder::where('user_id', $userId)
            ->where('status', 'confirmed')
            ->with('items.booking')
            ->get();

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Rocket LMS//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";

        foreach ($bookings as $order) {
            foreach ($order->items as $item) {
                $start = $this->toIcsDate($item->booking_date, $item->start_time ?? null);
                $end   = $this->toIcsDate($item->booking_date, $item->end_time ?? null);

                $ics .= "BEGIN:VEVENT\r\n";
                $ics .= "UID:{$order->order_number}-{$item->id}@rocketlms\r\n";
                $ics .= "SUMMARY:" . $this->escape($item->booking->title ?? '') . "\r\n";
                $ics .= "DTSTART:{$start}\r\n";
                $ics .= "DTEND:{$end}\r\n";
                $ics .= "STATUS:CONFIRMED\r\n";
                $ics .= "DTSTAMP:" . now()->utc()->format('Ymd\THis\Z') . "\r\n";
                $ics .= "END:VEVENT\r\n";
            }
        }

        $ics .= "END:VCALENDAR\r\n";

        return $ics;
    }

    // Generate signed token for iCal URL
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
