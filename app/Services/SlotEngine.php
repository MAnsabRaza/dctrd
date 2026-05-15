<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingTimeSlot;
use App\Models\BookingOrder;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlotEngine
{
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
        if (!$this->isDateOpen($booking, $date)) {
            return collect();
        }

        $slots = $this->getTimeSlotsForDate($booking, $date, $resourceId);

        $bookedSlots = $this->getBookedSlots($booking, $date, $resourceId);

        return $slots->filter(function ($slot) use ($bookedSlots, $date, $booking) {
            // Check lead time
            $slotStart = Carbon::parse($date->toDateString() . ' ' . $slot['start_time']);
            $leadHours = (int) $booking->lead_time_hours;
            if ($slotStart->diffInHours(now(), false) > -$leadHours) {
                return false;
            }

            // Check cutoff
            $cutoffHours = (int) $booking->cutoff_time_hours;
            if ($cutoffHours > 0) {
                $cutoffTime = $slotStart->copy()->subHours($cutoffHours);
                if (now()->greaterThan($cutoffTime)) {
                    return false;
                }
            }

            // Check if slot is already booked
            $key = $slot['start_time'] . '-' . $slot['end_time'];
            return !isset($bookedSlots[$key]);
        })->values();
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
                    'date'  => $current->toDateString(),
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

    private function isDateOpen(Booking $booking, Carbon $date): bool
    {
        // Check explicit availability table
        $avail = $booking->availability()
            ->where('date', $date->toDateString())
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

        $slotTemplates = BookingTimeSlot::where('booking_id', $booking->id)
            ->where('status', true)
            ->when($resourceId, fn($q) => $q->where('resource_id', $resourceId))
            ->get()
            ->filter(function ($template) use ($dayOfWeek) {
                if (!$template->day_of_week) return true; // applies all days
                $days = array_map('intval', explode(',', $template->day_of_week));
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
                // Move past slot + buffer
                $start->addMinutes($template->duration_minutes + $template->buffer_minutes);
            }
        }

        return $slots;
    }

    private function getBookedSlots(Booking $booking, Carbon $date, ?int $resourceId): array
    {
        $orders = BookingOrder::where('booking_id', $booking->id)
            ->where('check_in_date', $date->toDateString())
            ->when($resourceId, fn($q) => $q->where('resource_id', $resourceId))
            ->whereIn('status', ['pending', 'confirmed'])
            ->get(['start_time', 'end_time']);

        $booked = [];
        foreach ($orders as $order) {
            $key = $order->start_time . '-' . $order->end_time;
            $booked[$key] = true;
        }

        return $booked;
    }
}