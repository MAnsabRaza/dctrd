<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingOrder;
use App\Models\BookingTimeSlot;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingCalendarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_calendar');

        $user = auth()->user();

        // ─── Filters ───────────────────────────────────────────────
        $bookingIds = $request->get('booking_ids', []);
        if (!is_array($bookingIds)) {
            $bookingIds = array_filter(explode(',', $bookingIds));
        }

        $status = $request->get('status', 'all');

        $showAvailable = $request->boolean('available', true);
        $showPurchased = $request->boolean('purchased', true);
        $showValues = $request->boolean('values', true);
        $showNumberOf = $request->boolean('number_of', true);

        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        // ─── All bookings belonging to this user (for filter dropdown) ───
        $allBookings = Booking::query()
            ->where('creator_id', $user->id)
            ->orderBy('title')
            ->get(['id', 'title']);

        // If nothing selected in filter, default to ALL the user's bookings
        $filterBookingIds = !empty($bookingIds) ? $bookingIds : $allBookings->pluck('id')->toArray();

        // ─── Pull bookings (with their time slots) for the calendar ───
        $bookings = Booking::query()
            ->where('creator_id', $user->id)
            ->whereIn('id', $filterBookingIds)
            ->with(['timeSlots' => function ($query) {
                $query->where('status', true);
            }])
            ->get();

        // ─── Pull purchased orders for the visible month ──────────────
        $monthStart = Carbon::create($year, $month, 1)->startOfMonth();
        $monthEnd = Carbon::create($year, $month, 1)->endOfMonth();

        $ordersQuery = BookingOrder::query()
            ->whereIn('booking_id', $filterBookingIds)
            ->where('created_at', '>=', $monthStart->timestamp)
            ->where('created_at', '<=', $monthEnd->timestamp)
            ->with(['booking:id,title,price,currency']);

        if (!empty($status) and $status !== 'all') {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->get();

        // ─── Build calendar grid ───────────────────────────────────────
        $calendarDays = $this->buildCalendarDays(
            $bookings,
            $orders,
            $month,
            $year,
            $showAvailable,
            $showPurchased
        );

        return view('design_1.panel.bookings.calendar.index', [
            'pageTitle' => trans('panel.booking_calendar'),
            'request' => $request,
            'allBookings' => $allBookings,
            'selectedBookingIds' => $filterBookingIds,
            'status' => $status,
            'showAvailable' => $showAvailable,
            'showPurchased' => $showPurchased,
            'showValues' => $showValues,
            'showNumberOf' => $showNumberOf,
            'month' => $month,
            'year' => $year,
            'calendarDays' => $calendarDays,
            'statusOptions' => BookingOrder::$status,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BUILD CALENDAR
    |--------------------------------------------------------------------------
    */

    private function buildCalendarDays(
        $bookings,
        $orders,
        $month,
        $year,
        bool $showAvailable,
        bool $showPurchased
    ) {
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = Carbon::create($year, $month, 1)->endOfMonth();

        $gridStart = $startOfMonth->copy()->startOfWeek(Carbon::MONDAY);
        $gridEnd = $endOfMonth->copy()->endOfWeek(Carbon::MONDAY);

        // Group purchased orders by the date they were created on (Y-m-d)
        $ordersByDate = [];
        foreach ($orders as $order) {
            $dateKey = Carbon::createFromTimestamp($order->created_at)->format('Y-m-d');
            $ordersByDate[$dateKey][] = $order;
        }

        $days = [];

        $cursor = $gridStart->copy();

        while ($cursor <= $gridEnd) {
            $date = $cursor->copy();
            $dateKey = $date->format('Y-m-d');
            $dayOfWeek = (string) $date->dayOfWeek; // 0 (Sun) - 6 (Sat)

            $bars = [];

            // ── Available bars: generated from each booking's weekly time slots ──
            if ($showAvailable) {
                foreach ($bookings as $booking) {
                    foreach ($booking->timeSlots as $slot) {
                        $slotDays = !empty($slot->day_of_week)
                            ? explode(',', $slot->day_of_week)
                            : [];

                        if (in_array($dayOfWeek, $slotDays) or empty($slotDays)) {
                            $bars[] = [
                                'type' => 'available',
                                'booking_id' => $booking->id,
                                'title' => $booking->title,
                                'price' => $booking->getPriceWithActiveDiscountPrice(),
                                'currency' => $booking->currency,
                                'time' => substr($slot->start_time, 0, 5),
                                'status' => null,
                            ];
                        }
                    }
                }
            }

            // ── Purchased bars: actual BookingOrders created on this date ──
            $dayOrders = $ordersByDate[$dateKey] ?? [];

            if ($showPurchased) {
                foreach ($dayOrders as $order) {
                    $bars[] = [
                        'type' => 'purchased',
                        'booking_id' => $order->booking_id,
                        'title' => optional($order->booking)->title ?? ('#' . $order->id),
                        'price' => optional($order->sale)->total_amount ?? optional($order->booking)->price,
                        'currency' => optional($order->booking)->currency,
                        'time' => Carbon::createFromTimestamp($order->created_at)->format('H:i'),
                        'status' => $order->status,
                    ];
                }
            }

            $days[] = [
                'date' => $date->copy(),
                'isCurrentMonth' => $date->month == $month,
                'isToday' => $date->isToday(),
                'bars' => $bars,
                'ordersCount' => count($dayOrders),
                'availableCount' => count($bars) - count($dayOrders),
            ];

            $cursor->addDay();
        }

        return collect($days);
    }
}