<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use Illuminate\Http\Request;

class MyBookingOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_my_orders');

        $user = auth()->user();

        $query = BookingOrder::with([
            'items.booking',
            'items.bundle',
            'items.resource',
        ])
            ->where('user_id', $user->id);

        // FILTERS

        if (!empty($request->get('status'))) {
            $query->where('status', $request->get('status'));
        }

        if (!empty($request->get('payment_status'))) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if (!empty($request->get('from'))) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }

        if (!empty($request->get('to'))) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        if (!empty($request->get('order_id'))) {
            $query->where('id', $request->get('order_id'));
        }

        // SORTING

        $sort = $request->get('sort');

        switch ($sort) {

            case 'total_asc':
                $query->orderBy('total', 'asc');
                break;

            case 'total_desc':
                $query->orderBy('total', 'desc');
                break;

            case 'create_date_asc':
                $query->oldest();
                break;

            default:
                $query->latest();
                break;
        }

        $orders = $query->paginate(10);

        // TOP STATS

        $totalOrders = BookingOrder::where('user_id', $user->id)->count();

        $pendingOrders = BookingOrder::where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $canceledOrders = BookingOrder::where('user_id', $user->id)
            ->where('status', 'cancelled')
            ->count();

        $totalSales = BookingOrder::where('user_id', $user->id)
            ->sum('total');

        return view('design_1.panel.bookings.order.my_order', [
            'pageTitle' => 'My Booking Orders',
            'orders' => $orders,

            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'canceledOrders' => $canceledOrders,
            'totalSales' => $totalSales,
        ]);
    }
}
