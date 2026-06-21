<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyBookingPurchaseController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_bookings_purchases");

        $user = auth()->user();

        $query = BookingOrder::query()
            ->where('booking_orders.buyer_id', $user->id)
            ->where('booking_orders.status', '!=', BookingOrder::$pending)
            ->whereHas('sale', function ($query) {
                $query->whereNull('refund_at');
            });

        $copyQuery = deepClone($query);
        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListsData($request, $query, $user);

        if ($request->ajax()) {
            return $getListData;
        }

        $totalOrders = deepClone($copyQuery)->count();

        $pendingOrders = deepClone($copyQuery)->where(function ($query) {
            $query->where('status', BookingOrder::$waitingConfirmation)
                ->orWhere('status', BookingOrder::$confirmed);
        })->count();

        $canceledOrders = deepClone($copyQuery)->where('status', BookingOrder::$canceled)->count();

        $totalPurchase = deepClone($copyQuery)
            ->join('sales', 'sales.id', '=', 'booking_orders.sale_id')
            ->select(DB::raw("sum(sales.total_amount) as totalAmount"))
            ->first();

        $sellerIds = deepClone($copyQuery)->pluck('seller_id')->toArray();
        $sellers = User::select('id', 'full_name')
            ->whereIn('id', array_unique($sellerIds))
            ->get();

        $data = [
            'pageTitle' => trans('update.booking_purchases_lists_page_title'),
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'canceledOrders' => $canceledOrders,
            'totalPurchase' => $totalPurchase ? $totalPurchase->totalAmount : 0,
            'sellers' => $sellers,
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.booking.my_purchases.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $seller_id = $request->get('seller_id');
        $status = $request->get('status');
        $order_id = $request->get('order_id');
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($seller_id) and $seller_id != 'all') {
            $query->where('seller_id', $seller_id);
        }

        if (isset($status) and $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($order_id)) {
            $query->where('id', $order_id);
        }

        if (!empty($sort)) {
            switch ($sort) {
                case 'price_asc':
                    $query->join('sales', 'sales.id', '=', 'booking_orders.sale_id')
                        ->select('booking_orders.*', 'sales.amount')
                        ->orderBy('sales.amount', 'asc');
                    break;
                case 'price_desc':
                    $query->join('sales', 'sales.id', '=', 'booking_orders.sale_id')
                        ->select('booking_orders.*', 'sales.amount')
                        ->orderBy('sales.amount', 'desc');
                    break;
                case 'quantity_asc':
                    $query->orderBy('quantity', 'asc');
                    break;
                case 'quantity_desc':
                    $query->orderBy('quantity', 'desc');
                    break;
                case 'create_date_asc':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'create_date_desc':
                    $query->orderBy('created_at', 'desc');
                    break;
            }
        } else {
            $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getListsData(Request $request, Builder $query, $user)
    {
        $page = $request->get('page') ?? 1;
        $count = $this->perPage;

        $total = $query->count();

        $query->limit($count);
        $query->offset(($page - 1) * $count);

        $orders = $query
            ->with([
                'booking',
                'bundle',
                'sale',
                'seller' => function ($query) {
                    $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'mobile', 'email');
                }
            ])
            ->get();

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $orders, $total, $count);
        }

        return [
            'orders' => $orders,
            'pagination' => $this->makePagination($request, $orders, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $orders, $total, $count)
    {
        $html = "";

        foreach ($orders as $orderRow) {
            $html .= (string)view()->make('design_1.panel.booking.my_purchases.table_items', ['order' => $orderRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $orders, $total, $count, true)
        ]);
    }

    /** Modal data — booking date/time, address, specifications etc. */
    public function getBookingOrder($saleId, $orderId)
    {
        $user = auth()->user();

        $order = BookingOrder::where('buyer_id', $user->id)
            ->where('id', $orderId)
            ->where('sale_id', $saleId)
            ->first();

        if (!empty($order)) {
            $order->load(['booking', 'bundle', 'seller']);
            $order->address = method_exists($user, 'getAddress') ? $user->getAddress(true) : null;

            return response()->json([
                'order' => $order,
            ]);
        }

        abort(403);
    }

    /** Buyer confirms the booking/service was completed — mirrors setGotTheParcel() */
    public function setCompleted($saleId, $orderId)
    {
        $user = auth()->user();

        $order = BookingOrder::where('buyer_id', $user->id)
            ->where('id', $orderId)
            ->where('sale_id', $saleId)
            ->first();

        if (!empty($order)) {
            $order->update([
                'status' => BookingOrder::$completed
            ]);

            $item = $order->item; // booking or bundle
            $buyer = $order->buyer;

            $notifyOptions = [
                '[c.title]' => !empty($item) ? $item->title : ('#' . $order->id),
                '[item_title]' => !empty($item) ? $item->title : ('#' . $order->id),
                '[u.name]' => !empty($buyer) ? $buyer->full_name : '',
            ];

            sendNotification('booking_order_completed', $notifyOptions, $order->seller_id);

            return response()->json([
                'code' => 200
            ]);
        }

        return response()->json([
            'code' => 422
        ]);
    }

    public function invoice($saleId, $orderId)
    {
        $user = auth()->user();

        $bookingOrder = BookingOrder::query()
            ->where('buyer_id', $user->id)
            ->where('id', $orderId)
            ->where('sale_id', $saleId)
            ->first();

        if (!empty($bookingOrder)) {
            $data = [
                'pageTitle' => trans('webinars.invoice_page_title'),
                'order' => $bookingOrder,
                'item' => $bookingOrder->item,
                'sale' => $bookingOrder->sale,
                'seller' => $bookingOrder->seller,
                'buyer' => $bookingOrder->buyer,
            ];

            return view('design_1.panel.booking.invoice.index', $data);
        }

        abort(404);
    }
}