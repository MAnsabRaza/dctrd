<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingOrderItem;
use App\Models\BookingAvailability;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class NightlyAvailability
{
    /**
     * Check if an accommodation is available for the entire date range.
     *
     * @param  Booking $booking
     * @param  Carbon  $checkIn
     * @param  Carbon  $checkOut
     * @param  int|null $resourceId  specific room/unit
     * @return array   ['available' => bool, 'blocked_dates' => [], 'reason' => '']
     */
    public function check(
        Booking $booking,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $resourceId = null
    ): array {
        if ($checkOut->lte($checkIn)) {
            return [
                'available' => false,
                'blocked_dates' => [],
                'reason' => 'Check-out must be after check-in.',
            ];
        }

        $nights = $checkIn->diffInDays($checkOut);

        if ($booking->duration_minutes) {
            // Minimum stay check (if configured)
        }

        // Check lead time
        $leadHours = (int) $booking->lead_time_hours;
        if ($checkIn->diffInHours(now(), false) > -$leadHours) {
            return [
                'available' => false,
                'blocked_dates' => [],
                'reason' => "Bookings must be made at least {$leadHours} hours in advance.",
            ];
        }

        // Get all dates in range (exclude checkout date — that day belongs to next guest)
        $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
        $blockedDates = [];

        foreach ($period as $date) {
            $result = $this->isDateAvailable($booking, $date, $resourceId);
            if (!$result['available']) {
                $blockedDates[] = $date->toDateString();
            }
        }

        if (!empty($blockedDates)) {
            return [
                'available' => false,
                'blocked_dates' => $blockedDates,
                'reason' => 'Some dates in your range are not available.',
            ];
        }

        // Check capacity (parallel bookings)
        $overlapping = $this->countOverlapping($booking, $checkIn, $checkOut, $resourceId);
        $maxCapacity = $resourceId ? 1 : ($booking->capacity ?? PHP_INT_MAX);

        if ($overlapping >= $maxCapacity) {
            return [
                'available' => false,
                'blocked_dates' => [],
                'reason' => 'No availability for the selected dates.',
            ];
        }

        return [
            'available' => true,
            'blocked_dates' => [],
            'reason' => '',
            'nights' => $nights,
        ];
    }

    /**
     * Get a calendar matrix for a month (for frontend calendar display).
     *
     * @param  Booking $booking
     * @param  int     $year
     * @param  int     $month
     * @param  int|null $resourceId
     * @return array   keyed by date string → ['available', 'price', 'slots_left']
     */
    public function getMonthCalendar(
        Booking $booking,
        int $year,
        int $month,
        ?int $resourceId = null
    ): array {
        $start = Carbon::create($year, $month, 1);
        $end = $start->copy()->endOfMonth();

        $calendar = [];
        $period = CarbonPeriod::create($start, $end);

        // Bulk-fetch availability overrides for the month
        $overrides = BookingAvailability::where('booking_id', $booking->id)
            ->when($resourceId, fn($q) => $q->where('resource_id', $resourceId))
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->get()
            ->keyBy('date');

        // Bulk-fetch bookings for the month to count overlaps efficiently
        $bookingOrders = \App\Models\BookingOrderItem::query()

            ->where('booking_id', $booking->id)

            ->when($resourceId, function ($q) use ($resourceId) {

                $q->where('resource_id', $resourceId);
            })

            ->whereIn('status', ['pending', 'confirmed'])

            ->whereBetween('booking_date', [
                $start->toDateString(),
                $end->toDateString()
            ])

            ->get([
                'booking_date',
                'start_time',
                'end_time',
            ]);

        foreach ($period as $date) {
            $dateStr = $date->toDateString();
            $override = $overrides->get($dateStr);

           $bookedCount = $bookingOrders->filter(function ($order) use ($dateStr) {

    return $order->booking_date == $dateStr;

})->count();

            $capacity = $resourceId ? 1 : ($booking->capacity ?? PHP_INT_MAX);
            $slotsLeft = $capacity - $bookedCount;

            $isAvailable = true;
            $price = null;
            $reason = null;

            if ($override) {
                $isAvailable = (bool) $override->is_available;
                $price = $override->price_override;
                $reason = $override->close_reason;
            }

            if ($slotsLeft <= 0) {
                $isAvailable = false;
                $reason = 'Fully booked';
            }

            if ($date->lt(today())) {
                $isAvailable = false;
                $reason = 'Past date';
            }

            $calendar[$dateStr] = [
                'available' => $isAvailable,
                'slots_left' => max(0, $slotsLeft),
                'price' => $price ?? (float) $booking->price,
                'reason' => $reason,
            ];
        }

        return $calendar;
    }

    // ─── Private Helpers ──────────────────────────────────────────────────

    private function isDateAvailable(Booking $booking, Carbon $date, ?int $resourceId): array
    {
        // Check explicit override
        $override = BookingAvailability::where('booking_id', $booking->id)
            ->when($resourceId, fn($q) => $q->where('resource_id', $resourceId))
            ->where('date', $date->toDateString())
            ->first();

        if ($override && !$override->is_available) {
            return ['available' => false, 'reason' => $override->close_reason ?? 'Closed'];
        }

        if ($date->isPast()) {
            return ['available' => false, 'reason' => 'Past date'];
        }

        return ['available' => true];
    }

    private function countOverlapping(
        Booking $booking,
        Carbon $checkIn,
        Carbon $checkOut,
        ?int $resourceId
    ): int {
        return BookingOrderItem::where('booking_id', $booking->id)
            ->when($resourceId, fn($q) => $q->where('resource_id', $resourceId))
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_date', '>=', $checkIn->toDateString())
            ->where('booking_date', '<', $checkOut->toDateString())
            ->count();
    }
}
