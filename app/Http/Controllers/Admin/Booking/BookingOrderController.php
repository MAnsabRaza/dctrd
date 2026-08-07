<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Exports\BookingOrdersExport;
use App\Http\Controllers\Controller;
use App\Models\Accounting;
use App\Models\Booking;
use App\Models\BookingOrder;
use App\Models\Role;
use App\Services\Calendar\CalendarSyncService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

        $successStatuses = [BookingOrder::$success, 'completed'];
        $canceledStatuses = [BookingOrder::$canceled, 'cancelled'];

        $successOrders = [
            'count' => deepClone($query)->whereIn('status', $successStatuses)->count(),
            'amount' => deepClone($query)->whereIn('status', $successStatuses)->whereHas('sale')->with('sale')->get()->sum(function ($order) {
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
            'count' => deepClone($query)->whereIn('status', $canceledStatuses)->count(),
            'amount' => deepClone($query)->whereIn('status', $canceledStatuses)->whereHas('sale')->with('sale')->get()->sum(function ($order) {
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
        $bookingId = $request->get('booking_id');
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

        if (!empty($bookingId)) {
            $query->where('booking_id', $bookingId);
        }

        $query = fromAndToDateFilter($from, $to, $query, 'booking_orders.created_at');

        if (!empty($status)) {
            $status = $this->normalizeStatus($status);
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

        $order = BookingOrder::with('sale')->findOrFail($id);

        if (!empty($order->sale)) {
            $sale = $order->sale;

            if (empty($sale->refund_at) and !empty($sale->total_amount)) {
                Accounting::refundAccounting($sale, $order->id);
            }

            $sale->update(['refund_at' => time()]);
        }

        $order->update(['status' => BookingOrder::$canceled]);

        $this->syncCalendarQuietly($order->fresh(), 'cancel');

        return back()->with('success', 'Booking order refunded successfully.');
    }

    public function cancel($id)
    {
        $this->authorize('admin_booking_orders_edit');

        $order = BookingOrder::findOrFail($id);

        if ($order->status !== BookingOrder::$canceled) {
            $order->update(['status' => BookingOrder::$canceled]);
            $this->syncCalendarQuietly($order->fresh(), 'cancel');
        }

        return back()->with('success', 'Booking order canceled successfully.');
    }

    public function updateStatus($id, $status)
    {
        $this->authorize('admin_booking_orders_edit');

        $status = $this->normalizeStatus($status);

        if (!in_array($status, BookingOrder::$status, true)) {
            abort(404);
        }

        $order = BookingOrder::findOrFail($id);
        $oldStatus = $order->status;

        $order->update(['status' => $status]);

        if ($status === BookingOrder::$canceled and $oldStatus !== BookingOrder::$canceled) {
            $this->syncCalendarQuietly($order->fresh(), 'cancel');
        } elseif ($oldStatus === BookingOrder::$canceled and $status !== BookingOrder::$canceled) {
            $this->syncCalendarQuietly($order->fresh(), 'create');
        }

        if ($status === BookingOrder::$success) {
            $this->sendBookingNotificationsQuietly($order, 'confirmed');
        }

        return back()->with('success', 'Booking order status updated successfully.');
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

    private function normalizeStatus(string $status): string
    {
        return match ($status) {
            'confirmed' => BookingOrder::$waitingDelivery,
            'completed' => BookingOrder::$success,
            'cancelled' => BookingOrder::$canceled,
            default => $status,
        };
    }

    private function syncCalendarQuietly(?BookingOrder $order, string $action): void
    {
        if (empty($order)) {
            return;
        }

        try {
            app(CalendarSyncService::class)->syncBooking($order, $action);
        } catch (\Throwable $exception) {
            Log::warning('Booking order calendar sync failed after status change.', [
                'booking_order_id' => $order->id,
                'action' => $action,
                'error' => $exception->getMessage(),
            ]);
        }
    }

    private function sendBookingNotificationsQuietly(BookingOrder $order, string $event): void
    {
        try {
            $order->sendBookingNotifications($event);
        } catch (\Throwable $exception) {
            Log::warning('Booking order notification failed after status change.', [
                'booking_order_id' => $order->id,
                'event' => $event,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
