<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MyBookingOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_my_orders');

        $user = auth()->user();

        $query = BookingOrder::query()
            ->where('user_id', $user->id)
            ->with([
                'items.booking',
                'items.bundle',
                'items.resource',
            ]);

        // COPY QUERY
        $copyQuery = deepClone($query);

        // HANDLE FILTERS
        $query = $this->handleFilters($request, $query);

        // LIST DATA
        $getListData = $this->getListsData($request, $query);

        // AJAX
        if ($request->ajax()) {
            return $getListData;
        }

        // TOP STATS

        $totalOrders = BookingOrder::where('user_id', $user->id)
            ->count();

        $pendingOrders = BookingOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $canceledOrders = BookingOrder::where('user_id', $user->id)
            ->where('status', 'cancelled')
            ->count();

        $totalSales = BookingOrder::where('user_id', $user->id)
            ->sum('total');

        $data = [
            'pageTitle' => 'My Booking Orders',

            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'canceledOrders' => $canceledOrders,
            'totalSales' => $totalSales,
        ];

        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.order.my_order', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | HANDLE FILTERS
    |--------------------------------------------------------------------------
    */

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from');
        $to = $request->get('to');

        $status = $request->get('status');
        $payment_status = $request->get('payment_status');

        $order_id = $request->get('order_id');

        $sort = $request->get('sort');

        // DATE FILTER

        $query = fromAndToDateFilter(
            $from,
            $to,
            $query,
            'created_at'
        );

        // ORDER ID

        if (!empty($order_id)) {

            $query->where('id', $order_id);
        }

        // STATUS

        if (!empty($status)) {

            $query->where('status', $status);
        }

        // PAYMENT STATUS

        if (!empty($payment_status)) {

            $query->where('payment_status', $payment_status);
        }

        // SORTING

        if (!empty($sort)) {

            switch ($sort) {

                case 'total_asc':

                    $query->orderBy('total', 'asc');

                    break;

                case 'total_desc':

                    $query->orderBy('total', 'desc');

                    break;

                case 'create_date_asc':

                    $query->orderBy('created_at', 'asc');

                    break;

                case 'create_date_desc':

                    $query->orderBy('created_at', 'desc');

                    break;
            }

        } else {

            $query->latest();
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | LIST DATA
    |--------------------------------------------------------------------------
    */

    private function getListsData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;

        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);

        $query->offset(($page - 1) * $count);

        $orders = $query->get();

        // AJAX

        if ($request->ajax()) {

            return $this->getAjaxResponse(
                $request,
                $orders,
                $total,
                $count
            );
        }

        return [

            'orders' => $orders,

            'pagination' => $this->makePagination(
                $request,
                $orders,
                $total,
                $count,
                true
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX RESPONSE
    |--------------------------------------------------------------------------
    */

    private function getAjaxResponse(
        Request $request,
        $orders,
        $total,
        $count
    ) {

        $html = "";

        foreach ($orders as $orderRow) {

            $html .= (string)view()->make(
                'design_1.panel.bookings.order.table_item',
                ['order' => $orderRow]
            );
        }

        return response()->json([

            'data' => $html,

            'pagination' => $this->makePagination(
                $request,
                $orders,
                $total,
                $count,
                true
            )
        ]);
    }
}