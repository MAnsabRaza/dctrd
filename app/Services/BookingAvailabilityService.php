<?php

namespace App\Services\Booking;

use App\Models\Booking;
use App\Models\BookingAvailability;
use App\Models\BookingOrder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * BookingAvailabilityService
 *
 * Har booking type ke liye alag availability logic handle karta hai.
 *
 * Usage:
 *   $service = new BookingAvailabilityService();
 *   $available = $service->check($booking, $params);
 *   $slots     = $service->getAvailableSlots($booking, $date);
 */
class BookingAvailabilityService
{
    /**
     * Check karo ke ek booking given params ke sath available hai ya nahi.
     *
     * @param Booking $booking
     * @param array   $params  {date?, time?, check_in?, check_out?, guests?, staff_id?}
     * @return bool
     */
    public function check(Booking $booking, array $params): bool
    {
        $config = BookingTemplateConfig::for($booking->booking_type);

        return match ($config->availabilityMode()) {
            BookingTemplateConfig::AVAIL_TIME_SLOT   => $this->checkTimeSlot($booking, $params),
            BookingTemplateConfig::AVAIL_DATE_RANGE  => $this->checkDateRange($booking, $params),
            BookingTemplateConfig::AVAIL_TICKET_COUNT=> $this->checkTickets($booking, $params),
            BookingTemplateConfig::AVAIL_APPOINTMENT => $this->checkAppointment($booking, $params),
            default                                  => true,
        };
    }

    /**
     * Frontend search ke liye — bookings query filter karo.
     * Returns a query builder with availability constraints applied.
     */
    public function applyAvailabilityFilter($query, string $bookingType, array $params)
    {
        $config = BookingTemplateConfig::for($bookingType);

        return match ($config->availabilityMode()) {
            BookingTemplateConfig::AVAIL_TIME_SLOT    => $this->filterByTimeSlot($query, $params),
            BookingTemplateConfig::AVAIL_DATE_RANGE   => $this->filterByDateRange($query, $params),
            BookingTemplateConfig::AVAIL_TICKET_COUNT => $this->filterByTicketCount($query),
            BookingTemplateConfig::AVAIL_APPOINTMENT  => $this->filterByAppointment($query, $params),
            default                                   => $query,
        };
    }

    // ─── Mode: Time Slot ─────────────────────────────────────────────
    // Beauty/Spa, Doctors, Professional Services, Education

    /**
     * Check karo ke given date+time pe time slot available hai.
     * Staff availability bhi check hoti hai agar staff_id diya.
     */
    private function checkTimeSlot(Booking $booking, array $params): bool
    {
        $date    = $params['date']     ?? null;
        $time    = $params['time']     ?? null;
        $staffId = $params['staff_id'] ?? ($booking->meta['staff_id'] ?? null);

        if (!$date) return true; // no date filter = available

        // BookingAvailability check
        $availRecord = BookingAvailability::where('booking_id', $booking->id)
            ->where('date', $date)
            ->first();

        // Agar availability record hai aur closed hai
        if ($availRecord && !$availRecord->is_open) {
            return false;
        }

        // Time slot conflict check
        if ($time && $booking->duration_minutes) {
            $requestedStart = Carbon::parse("$date $time");
            $requestedEnd   = $requestedStart->copy()->addMinutes($booking->duration_minutes);

            // Check existing confirmed orders for same date+time slot
            $conflict = BookingOrder::where('booking_id', $booking->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->whereDate('start_at', $date)
                ->where(function ($q) use ($requestedStart, $requestedEnd) {
                    // Overlap check: existing.start < requested.end AND existing.end > requested.start
                    $q->where('start_at', '<', $requestedEnd)
                      ->where('end_at',   '>',  $requestedStart);
                })
                ->when($staffId, fn($q) => $q->where('meta->staff_id', $staffId))
                ->exists();

            if ($conflict) return false;
        }

        // Capacity check (group services)
        if ($booking->capacity) {
            $booked = BookingOrder::where('booking_id', $booking->id)
                ->whereIn('status', ['confirmed', 'pending'])
                ->whereDate('start_at', $date)
                ->when($time, fn($q) => $q->whereTime('start_at', $time))
                ->sum('quantity');

            if ($booked >= $booking->capacity) return false;
        }

        return true;
    }

    /**
     * Query: date+time filter lagao — sirf bookings dikhao jinka slot available hai.
     */
    private function filterByTimeSlot($query, array $params)
    {
        $date    = $params['date']    ?? null;
        $time    = $params['time']    ?? null;
        $staffId = $params['staff_id']?? null;

        if (!$date) return $query;

        // Exclude bookings jinka availability record closed hai
        $query->whereDoesntHave('availabilities', function ($q) use ($date) {
            $q->where('date', $date)->where('is_open', false);
        });

        // Exclude bookings jinka capacity full ho chuka hai on this date
        $query->where(function ($q) use ($date, $time) {
            $q->whereNull('capacity')
              ->orWhere('capacity', '=', 0)
              ->orWhereRaw('capacity > (
                  SELECT COALESCE(SUM(bo.quantity), 0)
                  FROM booking_orders bo
                  WHERE bo.booking_id = bookings.id
                    AND bo.status IN (\'confirmed\', \'pending\')
                    AND DATE(bo.start_at) = ?
                    ' . ($time ? 'AND TIME(bo.start_at) = ?' : '') . '
              )', $time ? [$date, $time] : [$date]);
        });

        // Staff filter
        if ($staffId) {
            $query->whereJsonContains('meta->staff_id', $staffId);
        }

        return $query;
    }

    // ─── Mode: Date Range ────────────────────────────────────────────
    // Accommodation, Automotive Rental

    /**
     * Check karo ke check_in se check_out tak booking available hai.
     * Guests count capacity ke against check hota hai.
     */
    private function checkDateRange(Booking $booking, array $params): bool
    {
        $checkIn  = $params['check_in']  ?? ($params['meta']['check_in_date']  ?? null);
        $checkOut = $params['check_out'] ?? ($params['meta']['check_out_date'] ?? null);
        $guests   = $params['guests']    ?? ($params['min_persons'] ?? 1);

        if (!$checkIn || !$checkOut) return true;

        $checkInDate  = Carbon::parse($checkIn);
        $checkOutDate = Carbon::parse($checkOut);

        if ($checkInDate->gte($checkOutDate)) return false;

        // Guest capacity check
        if ($booking->max_persons && $guests > $booking->max_persons) {
            return false;
        }

        // Check ke koi existing confirmed order is date range ke sath overlap karta hai
        $conflict = BookingOrder::where('booking_id', $booking->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('start_at', '<', $checkOut)
            ->where('end_at',   '>',  $checkIn)
            ->exists();

        return !$conflict;
    }

    /**
     * Query: date range filter — sirf woh bookings jo check_in/check_out ke liye available hain.
     */
    private function filterByDateRange($query, array $params)
    {
        $checkIn  = $params['check_in']  ?? null;
        $checkOut = $params['check_out'] ?? null;
        $guests   = intval($params['guests'] ?? 1);

        if (!$checkIn || !$checkOut) return $query;

        // Exclude bookings jinka koi confirmed order in dates ke sath overlap karta hai
        $query->whereDoesntHave('orders', function ($q) use ($checkIn, $checkOut) {
            $q->whereIn('status', ['confirmed', 'pending'])
              ->where('start_at', '<', $checkOut)
              ->where('end_at',   '>',  $checkIn);
        });

        // Guest capacity filter
        if ($guests > 0) {
            $query->where(function ($q) use ($guests) {
                $q->whereNull('max_persons')
                  ->orWhere('max_persons', '>=', $guests);
            });
        }

        return $query;
    }

    // ─── Mode: Ticket Count ──────────────────────────────────────────
    // Events

    /**
     * Check karo ke tickets available hain.
     */
    private function checkTickets(Booking $booking, array $params): bool
    {
        $quantity = $params['quantity'] ?? 1;

        // Agar inventory set nahi toh unlimited
        if (is_null($booking->inventory)) return true;

        $sold = BookingOrder::where('booking_id', $booking->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->sum('quantity');

        return ($booking->inventory - $sold) >= $quantity;
    }

    /**
     * Query: sirf woh events dikhao jinke tickets available hain.
     */
    private function filterByTicketCount($query)
    {
        $query->where(function ($q) {
            $q->whereNull('inventory')
              ->orWhereRaw('inventory > (
                  SELECT COALESCE(SUM(bo.quantity), 0)
                  FROM booking_orders bo
                  WHERE bo.booking_id = bookings.id
                    AND bo.status IN (\'confirmed\', \'pending\')
              )');
        });

        return $query;
    }

    // ─── Mode: Appointment ───────────────────────────────────────────
    // Automotive service / mechanic

    /**
     * Appointment slot available hai ya nahi.
     */
    private function checkAppointment(Booking $booking, array $params): bool
    {
        // Same logic as time slot for mechanic appointments
        return $this->checkTimeSlot($booking, $params);
    }

    private function filterByAppointment($query, array $params)
    {
        return $this->filterByTimeSlot($query, $params);
    }

    // ─── Public helpers ───────────────────────────────────────────────

    /**
     * Get available time slots for a booking on a given date.
     * Returns array of available slot strings: ['09:00', '10:00', ...]
     */
    public function getAvailableSlots(Booking $booking, string $date): array
    {
        // Get all defined time slots for this booking
        $allSlots = $booking->timeSlots()
            ->where('is_active', true)
            ->get();

        if ($allSlots->isEmpty()) {
            return $this->generateDefaultSlots($booking, $date);
        }

        $available = [];
        foreach ($allSlots as $slot) {
            if ($this->checkTimeSlot($booking, ['date' => $date, 'time' => $slot->start_time])) {
                $available[] = $slot->start_time;
            }
        }

        return $available;
    }

    /**
     * Generate default time slots based on booking duration.
     * Example: 9am-6pm with 60min slots = ['09:00', '10:00', ..., '17:00']
     */
    private function generateDefaultSlots(Booking $booking, string $date): array
    {
        $duration = $booking->duration_minutes ?? 60;
        $buffer   = ($booking->buffer_before ?? 0) + ($booking->buffer_after ?? 0);
        $slotStep = $duration + $buffer;

        // Default: 9am to 6pm
        $start = Carbon::parse("$date 09:00");
        $end   = Carbon::parse("$date 18:00");
        $slots = [];

        while ($start->copy()->addMinutes($duration)->lte($end)) {
            $timeStr = $start->format('H:i');
            if ($this->checkTimeSlot($booking, ['date' => $date, 'time' => $timeStr])) {
                $slots[] = $timeStr;
            }
            $start->addMinutes($slotStep);
        }

        return $slots;
    }

    /**
     * Ek booking ke liye blocked dates return karo (accommodation/rental ke liye).
     */
    public function getBlockedDates(Booking $booking): array
    {
        $orders = BookingOrder::where('booking_id', $booking->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->whereNotNull('start_at')
            ->whereNotNull('end_at')
            ->get(['start_at', 'end_at']);

        $blocked = [];
        foreach ($orders as $order) {
            $current = Carbon::parse($order->start_at);
            $end     = Carbon::parse($order->end_at);

            while ($current->lt($end)) {
                $blocked[] = $current->format('Y-m-d');
                $current->addDay();
            }
        }

        return array_unique($blocked);
    }
}