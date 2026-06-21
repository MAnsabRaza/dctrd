<?php

namespace App\Http\Controllers\Panel\Booking\Traits;

use App\Models\Booking;
use App\Models\BookingOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait MyBookingsListsTrait
{
    private function handlePageTopStats($user): array
    {
        $query = Booking::query()->where('creator_id', $user->id);

        $totalBookings = deepClone($query)->count();

        $totalSales = deepClone($query)
            ->join('booking_orders', 'bookings.id', 'booking_orders.booking_id')
            ->leftJoin('sales', function ($join) {
                $join->on('booking_orders.id', '=', 'sales.booking_order_id')
                    ->whereNull('sales.refund_at');
            })
            ->select(DB::raw('sum(sales.total_amount) as total_sales'))
            ->whereNotNull('booking_orders.sale_id')
            ->whereNotIn('booking_orders.status', [BookingOrder::$canceled, BookingOrder::$pending])
            ->first();

        return [
            'totalBookings' => $totalBookings,
            'totalSales' => !empty($totalSales) ? $totalSales->total_sales : 0,
        ];
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        return $query;
    }

    private function getPageListData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = 8; // $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $bookings = $query
            ->with([
                'bookingOrders'
            ])
            ->withCount([
                'visits'
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        foreach ($bookings as $booking) {
            $lastPurchase = $booking->sales(true, true)->first();

            $booking->last_purchase_date = !empty($lastPurchase) ? $lastPurchase->created_at : null;
        }

        if ($request->ajax()) {
            return $this->handleAjaxResponse($request, $bookings, $total, $count);
        }

        return [
            'bookings' => $bookings,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ];
    }

    private function handleAjaxResponse(Request $request, $bookings, $total, $count)
    {
        $html = "";

        foreach ($bookings as $bookingItem) {
            $html .= '<div class="col-12 col-lg-6 mb-32">';
            $html .= (string)view()->make("design_1.panel.bookings.my_bookings.booking_card.index", ['booking' => $bookingItem]);
            $html .= '</div>';
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true)
        ]);
    }
}
