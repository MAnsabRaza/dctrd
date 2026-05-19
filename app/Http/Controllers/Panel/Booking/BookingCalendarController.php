<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingOrder;
use App\Services\SlotEngine;
use App\Services\NightlyAvailability;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingCalendarController extends Controller
{
    protected $slotEngine;
    protected $nightlyAvailability;

    public function __construct(
        SlotEngine $slotEngine,
        NightlyAvailability $nightlyAvailability
    ) {
        $this->slotEngine = $slotEngine;

        $this->nightlyAvailability = $nightlyAvailability;
    }

    public function index(Request $request)
    {
        $this->authorize('panel_bookings_calendar');

        $user = auth()->user();

        $bookingId = $request->get('booking_id');

        $bookings = Booking::query()
            ->where('creator_id', $user->id)
            ->orderBy('title')
            ->get();

        if (empty($bookingId) and $bookings->count()) {

            $bookingId = $bookings->first()->id;
        }

        $booking = Booking::query()
            ->where('creator_id', $user->id)
            ->findOrFail($bookingId);

        $month = $request->get('month', now()->month);

        $year = $request->get('year', now()->year);

        $calendarDays = $this->buildCalendarDays(
            $booking,
            $month,
            $year
        );

        return view('design_1.panel.bookings.calendar.index', [

            'pageTitle' => 'Booking Calendar',

            'booking' => $booking,

            'bookings' => $bookings,

            'month' => $month,

            'year' => $year,

            'calendarDays' => $calendarDays,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD CALENDAR
    |--------------------------------------------------------------------------
    */

    private function buildCalendarDays(
        Booking $booking,
        $month,
        $year
    ) {

        $startOfMonth = Carbon::create(
            $year,
            $month,
            1
        )->startOfMonth();

        $endOfMonth = Carbon::create(
            $year,
            $month,
            1
        )->endOfMonth();

        $gridStart = $startOfMonth->copy()->startOfWeek();

        $gridEnd = $endOfMonth->copy()->endOfWeek();

       $monthAvailability = $this->nightlyAvailability
    ->getMonthCalendar(
        $booking,
        $year,
        $month
    );

        $days = [];

        while ($gridStart <= $gridEnd) {

            $date = $gridStart->copy();

            $dateKey = $date->format('Y-m-d');

            $availabilityData = $monthAvailability[$dateKey] ?? null;

            $slots = $this->slotEngine
                ->getAvailableSlots(
                    $booking,
                    $date
                );

            $ordersCount = BookingOrder::query()
                ->where('booking_id', $booking->id)
                ->whereDate('check_in', '<=', $date)
                ->whereDate('check_out', '>=', $date)
                ->count();

            $days[] = [

                'date' => $date->copy(),

                'isCurrentMonth' => $date->month == $month,

                'isToday' => $date->isToday(),

                'isAvailable' => $availabilityData['available'] ?? true,

                'slotsLeft' => $availabilityData['slots_left'] ?? count($slots),

                'price' => $availabilityData['price'] ?? $booking->price,

                'ordersCount' => $ordersCount,

                'slots' => $slots,
            ];

            $gridStart->addDay();
        }

        return collect($days);
    }
}