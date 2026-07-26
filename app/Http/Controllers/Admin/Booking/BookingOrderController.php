<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Exports\BookingOrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingOrder;
use App\Models\Role;
use App\Services\Calendar\CalendarSyncService;
use App\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class BookingOrderController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_orders');

        return $this->buildOrdersListView($request, false);
    }

    public function inHouseOrders(Request $request)
    {
        $this->authorize('admin_booking_in_house_orders');

        return $this->buildOrdersListView($request, true);
    }

    /**
     * Shared logic for both the normal booking-order list and the
     * in-house booking-order list. Mirrors
     * ProductsController::index() / inHouseProducts() pattern.
     */
    private function buildOrdersListView(Request $request, bool $inHouseOnly)
    {
        $query = BookingOrder::query();

        if ($inHouseOnly) {
            $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();

            $query->whereHas('booking.creator', function ($q) use ($adminRoleIds) {
                $q->whereIn('role_id', $adminRoleIds);
            });
        }

        $totalOrders = [
            'count' => deepClone($query)->count(),
            'amount' => deepClone($query)->whereHas('sale')->with('sale')->get()->sum(function ($order) {
                return optional($order->sale)->total_amount ?? 0;
            }),
        ];

        $successOrders = [
            'count' => deepClone($query)->where('status', 'completed')->count(),
            'amount' => deepClone($query)->where('status', 'completed')->whereHas('sale')->with('sale')->get()->sum(function ($order) {
                return optional($order->sale)->total_amount ?? 0;
            }),
        ];

        $waitingOrders = [
            'count' => deepClone($query)->where('status', 'pending')->count(),
            'amount' => deepClone($query)->where('status', 'pending')->whereHas('sale')->with('sale')->get()->sum(function ($order) {
                return optional($order->sale)->total_amount ?? 0;
            }),
        ];

        $canceledOrders = [
            'count' => deepClone($query)->where('status', 'cancelled')->count(),
            'amount' => deepClone($query)->where('status', 'cancelled')->whereHas('sale')->with('sale')->get()->sum(function ($order) {
                return optional($order->sale)->total_amount ?? 0;
            }),
        ];

        $ordersQuery = $this->getOrderFilters($query, $request);

        $orders = $ordersQuery->orderBy('booking_orders.created_at', 'desc')
            ->with([
                'booking',
                'seller',
                'buyer',
                'sale',
            ])
            ->paginate(10);

        $data = [
            'pageTitle' => $inHouseOnly
                ? (trans('update.in-house-booking-orders') ?? 'In House Booking Orders')
                : (trans('admin/main.booking_orders') ?? 'Booking Orders'),
            'inHouseOrders' => $inHouseOnly,
            'orders' => $orders,
            'totalOrders' => $totalOrders,
            'successOrders' => $successOrders,
            'waitingOrders' => $waitingOrders,
            'canceledOrders' => $canceledOrders,
        ];

        $seller_ids = $request->get('seller_ids');
        $customer_ids = $request->get('customer_ids');

        if (!empty($seller_ids)) {
            $data['sellers'] = User::select('id', 'full_name')
                ->whereIn('id', $seller_ids)->get();
        }

        if (!empty($customer_ids)) {
            $data['customers'] = User::select('id', 'full_name')
                ->whereIn('id', $customer_ids)->get();
        }

        return view('admin.booking.order', $data);
    }

    private function getOrderFilters($query, $request)
    {
        $item_title = $request->get('item_title');
        $from = $request->get('from');
        $to = $request->get('to');
        $status = $request->get('status');
        $seller_ids = $request->get('seller_ids', []);
        $customer_ids = $request->get('customer_ids', []);

        if (!empty($item_title)) {
            $bookingIds = Booking::whereTranslationLike('title', "%$item_title%")
                ->pluck('id')
                ->toArray();

            if (empty($bookingIds)) {
                $bookingIds = Booking::where('title', 'like', "%$item_title%")
                    ->pluck('id')
                    ->toArray();
            }

            $query->whereIn('booking_id', $bookingIds);
        }

        $query = fromAndToDateFilter($from, $to, $query, 'booking_orders.created_at');

        if (!empty($status)) {
            $query->where('booking_orders.status', $status);
        }

        if (!empty($seller_ids) and count($seller_ids)) {
            $query->whereIn('seller_id', $seller_ids);
        }

        if (!empty($customer_ids) and count($customer_ids)) {
            $query->whereIn('buyer_id', $customer_ids);
        }

        return $query;
    }

    public function refund($id)
    {
        $this->authorize('admin_booking_orders_refund');

        $order = BookingOrder::findOrFail($id);

        if (!empty($order->sale)) {
            $order->sale->update(['refund_at' => time()]);
        }

        $order->update(['status' => BookingOrder::$canceled]);

        app(CalendarSyncService::class)->syncBooking($order->fresh(), 'cancel');

        return back();
    }

    public function invoice($id)
    {
        $this->authorize('admin_booking_orders_invoice');

        $order = BookingOrder::where('id', $id)
            ->with([
                'booking',
                'seller' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'buyer' => function ($query) {
                    $query->select('id', 'full_name');
                },
                'sale',
            ])
            ->first();

        if (empty($order)) {
            abort(404);
        }

        $data = [
            'pageTitle' => trans('admin/main.invoice'),
            'order' => $order,
        ];

        return view('admin.booking.invoice', $data);
    }

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_booking_orders');

        $query = BookingOrder::query();

        if (!empty($request->get('in_house_orders'))) {
            $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();

            $query->whereHas('booking.creator', function ($q) use ($adminRoleIds) {
                $q->whereIn('role_id', $adminRoleIds);
            });
        }

        $ordersQuery = $this->getOrderFilters($query, $request);

        $orders = $ordersQuery->orderBy('booking_orders.created_at', 'desc')
            ->with([
                'booking',
                'seller',
                'buyer',
                'sale',
            ])
            ->get();

        $export = new BookingOrdersExport($orders);

        return Excel::download($export, 'booking_orders.xlsx');
    }
}
