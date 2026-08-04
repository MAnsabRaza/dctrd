<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingSlot;
use App\Models\BookingTimeSlot;
use Carbon\Carbon;
use App\Models\BookingOrder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
class SlotEngine
{
    private function getBusinessTimezone(): string
    {
        return function_exists('getGeneralSettings')
            ? (getGeneralSettings('default_time_zone') ?: config('app.timezone', 'UTC'))
            : config('app.timezone', 'UTC');
    }

    /**
     * Get available time slots for a booking on a specific date.
     *
     * @param  Booking $booking
     * @param  Carbon  $date
     * @param  int|null $resourceId
     * @return Collection
     */
    public function getAvailableSlots(
    Booking $booking,
    Carbon $date,
    ?int $resourceId = null
): Collection {
    $ordersFingerprint = $this->ordersFingerprint($booking, $date, $resourceId);
    $timezone = $this->getBusinessTimezone();
    $now = Carbon::now($timezone);
    $sameDayClockFingerprint = $date->toDateString() === $now->toDateString()
        ? $now->format('YmdHi')
        : '';

    $cacheKey = 'booking_slots:' . md5(
        $booking->id . '|' . $date->toDateString() . '|' . $resourceId
        . '|' . optional($booking->updated_at)->timestamp
        . '|' . $ordersFingerprint
        . '|' . $sameDayClockFingerprint
    );

    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($booking, $date, $resourceId) {
        if (!$this->isDateOpen($booking, $date, $resourceId)) {
                return collect();
            }

            $slots = $this->getTimeSlotsForDate($booking, $date, $resourceId);

            $bookedSlots = $this->getBookedSlots($booking, $date, $resourceId);
            $timezone = $this->getBusinessTimezone();
            $now = Carbon::now($timezone);

            return $slots->filter(function ($slot) use ($bookedSlots, $date, $booking, $timezone, $now) {
                $slotStart = Carbon::parse($date->toDateString() . ' ' . $slot['start_time'], $timezone);

                if ($slotStart->lessThanOrEqualTo($now)) {
                    return false;
                }

                $leadHours = (int) $booking->lead_time_hours;
                if ($leadHours > 0 && $slotStart->diffInHours($now, false) > -$leadHours) {
                    return false;
                }

                $cutoffHours = (int) $booking->cutoff_time_hours;
                if ($cutoffHours > 0 && $now->greaterThan($slotStart->copy()->subHours($cutoffHours))) {
                    return false;
                }

                $key = $slot['start_time'] . '-' . $slot['end_time'];
                $bookedCount = $bookedSlots[$key] ?? 0;

                return $bookedCount < (int) ($slot['max_bookings'] ?? $slot['capacity'] ?? 1);
            })->values();
        });
    }

    /**
     * Generate a list of date-slot objects for a booking's schedule.
     */
    public function generateSchedule(Booking $booking, Carbon $from, Carbon $to): Collection
    {
        $schedule = collect();
        $current = $from->copy();

        while ($current->lte($to)) {
            $slots = $this->getAvailableSlots($booking, $current->copy());
            if ($slots->isNotEmpty()) {
                $schedule->push([
                    'date' => $current->toDateString(),
                    'slots' => $slots,
                ]);
            }
            $current->addDay();
        }

        return $schedule;
    }

    /**
     * Check if a specific slot is available (for booking attempt).
     */
    public function isSlotAvailable(
        Booking $booking,
        Carbon $date,
        string $startTime,
        string $endTime,
        ?int $resourceId = null
    ): bool {
        $available = $this->getAvailableSlots($booking, $date, $resourceId);

        return $available->contains(function ($slot) use ($startTime, $endTime) {
            return $slot['start_time'] === $startTime && $slot['end_time'] === $endTime;
        });
    }

    // ─── Private Helpers ──────────────────────────────────────────────────

    private function isDateOpen(Booking $booking, Carbon $date, ?int $resourceId = null): bool
    {
        // Check explicit availability table
        $avail = $booking->availabilities()
            ->where('date', $date->toDateString())
            ->when($resourceId ?? null, fn($q) => $q->where('resource_id', $resourceId))
            ->first();

        if ($avail) {
            return (bool) $avail->is_available;
        }

        // Default: open (all dates available unless marked closed)
        return true;
    }

  private function getTimeSlotsForDate(
    Booking $booking,
    Carbon $date,
    ?int $resourceId
): Collection {
    $dayOfWeek = $date->dayOfWeek; // 0 = Sunday

    // ── booking_slots (explicit override) table STEP 1 hata di gayi ──
    // Ab hum seedha booking_time_slots (weekly recurring template) use karenge.

    $slotTemplates = BookingTimeSlot::where('booking_id', $booking->id)
        ->where('status', true)
        ->when($resourceId, fn($q) => $q->where('resource_id', $resourceId))
        ->get()
        ->filter(function ($template) use ($dayOfWeek) {
            if (!$template->day_of_week)
                return true; // applies all days

            $days = is_array($template->day_of_week)
                ? array_map('intval', $template->day_of_week)
                : array_map('intval', explode(',', $template->day_of_week));

            return in_array($dayOfWeek, $days);
        });

    $slots = collect();

    foreach ($slotTemplates as $template) {
        $start = Carbon::parse($date->toDateString() . ' ' . $template->start_time);
        $end   = Carbon::parse($date->toDateString() . ' ' . $template->end_time);

        while ($start->copy()->addMinutes($template->duration_minutes)->lte($end)) {
            $slotEnd = $start->copy()->addMinutes($template->duration_minutes);
            $slots->push([
                'start_time'       => $start->format('H:i'),
                'end_time'         => $slotEnd->format('H:i'),
                'duration_minutes' => $template->duration_minutes,
                'buffer_minutes'   => $template->buffer_minutes,
                'max_bookings'     => $template->max_bookings,
            ]);
            $start->addMinutes($template->duration_minutes + $template->buffer_minutes);
        }
    }

    return $slots;
}

 private function getBookedSlots(
    Booking $booking,
    Carbon $date,
    ?int $resourceId
): array {

    $orders = BookingOrder::query()
        ->where('booking_id', $booking->id)
        ->where('booking_date', $date->toDateString())
        ->when($resourceId, function ($q) use ($resourceId) {
            $q->where('resource_id', $resourceId);
        })
        ->whereIn('status', [BookingOrder::$pending, BookingOrder::$success])
        ->get(['start_time', 'end_time']);

    $booked = [];

    foreach ($orders as $order) {
        $key = $order->start_time . '-' . $order->end_time;
        $booked[$key] = ($booked[$key] ?? 0) + 1;
    }

    return $booked;
}


private function ordersFingerprint(Booking $booking, Carbon $date, ?int $resourceId): string
    {
        return BookingOrder::query()
            ->where('booking_id', $booking->id)
            ->where('booking_date', $date->toDateString())
            ->when($resourceId, fn ($q) => $q->where('resource_id', $resourceId))
            ->whereIn('status', [BookingOrder::$pending, BookingOrder::$success])
            ->count() . ':' . BookingOrder::query()
                ->where('booking_id', $booking->id)
                ->where('booking_date', $date->toDateString())
                ->when($resourceId, fn ($q) => $q->where('resource_id', $resourceId))
                ->whereIn('status', [BookingOrder::$pending, BookingOrder::$success])
                ->max('id');
    }
}
