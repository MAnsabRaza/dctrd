<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingOrder;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingSaleController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("panel_bookings_sales");

        $user = auth()->user();

        if (!$user->isOrganization() and !$user->isTeacher()) {
            abort(403);
        }

        $query = BookingOrder::where('booking_orders.seller_id', $user->id)
            ->where('booking_orders.status', '!=', 'pending')
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
        $pendingOrders = deepClone($copyQuery)->where('booking_orders.status', BookingOrder::$waitingDelivery)->count();
        $canceledOrders = deepClone($copyQuery)->where('booking_orders.status', BookingOrder::$canceled)->count();

        $totalSales = deepClone($copyQuery)
            ->join('sales', 'sales.booking_order_id', 'booking_orders.id')
            ->select(DB::raw('(sum(sales.total_amount) - (sum(sales.tax) + sum(sales.commission))) as totalAmount'))
            ->first();

        $customerIds = deepClone($copyQuery)->pluck('buyer_id')->toArray();
        $customers = User::select('id', 'full_name')
            ->whereIn('id', array_unique($customerIds))
            ->get();

        $data = [
            'pageTitle' => trans('update.booking_sales_lists_page_title'),
            'totalOrders' => $totalOrders,
            'pendingOrders' => $pendingOrders,
            'canceledOrders' => $canceledOrders,
            'totalSales' => $totalSales ? $totalSales->totalAmount : 0,
            'customers' => $customers,
        ];
        $data = array_merge($data, $getListData);

        return view('design_1.panel.bookings.sales.index', $data);
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $from = $request->get('from');
        $to = $request->get('to');
        $customer_id = $request->get('customer_id');
        $type = $request->get('type');
        $status = $request->get('status');
        $order_id = $request->get('order_id');
        $sort = $request->get('sort');

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($customer_id) and $customer_id != 'all') {
            $query->where('buyer_id', $customer_id);
        }

        if (isset($type) and $type !== 'all') {
            if ($type == 'bundle') {
                $query->whereNotNull('bundle_id');
            } elseif ($type == 'booking') {
                $query->whereNotNull('booking_id');
            }
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
                'buyer' => function ($query) {
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
            $html .= (string)view()->make('design_1.panel.bookings.sales.table_items', ['order' => $orderRow]);
        }

        return response()->json([
            'data' => $html,
            'pagination' => $this->makePagination($request, $orders, $total, $count, true)
        ]);
    }

    public function invoice($saleId, $orderId)
    {
        $user = auth()->user();

        $bookingOrder = BookingOrder::where('seller_id', $user->id)
            ->where('id', $orderId)
            ->where('sale_id', $saleId)
            ->whereHas('sale', function ($query) {
                $query->whereNull('refund_at');
            })
            ->first();

        if (!empty($bookingOrder) and !empty($bookingOrder->item)) {
            $data = [
                'pageTitle' => trans('webinars.invoice_page_title'),
                'order' => $bookingOrder,
                'booking' => $bookingOrder->item,
                'sale' => $bookingOrder->sale,
                'seller' => $bookingOrder->seller,
                'buyer' => $bookingOrder->buyer,
            ];

            return view('design_1.panel.bookings.invoice.index', $data);
        }

        abort(404);
    }

    public function getBookingOrder($saleId, $orderId)
    {
        $user = auth()->user();

        $order = BookingOrder::query()->where('seller_id', $user->id)
            ->where('id', $orderId)
            ->where('sale_id', $saleId)
            ->first();

        if (!empty($order)) {
            $buyer = $order->buyer;
            $order->address = $buyer->getAddress(true);

            $html = (string)view()->make("design_1.panel.bookings.modals.enter_tracking_code", [
                'order' => $order,
                'saleId' => $saleId,
            ]);

            return response()->json([
                'code' => 200,
                'html' => $html,
            ]);
        }

        return response()->json([], 422);
    }

    public function setTrackingCode(Request $request, $saleId, $orderId)
    {
        $user = auth()->user();
        $data = $request->all();

        $validator = Validator::make($data, [
            'tracking_code' => 'required'
        ]);

        if ($validator->fails()) {
            return response([
                'code' => 422,
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = BookingOrder::where('seller_id', $user->id)
            ->where('id', $orderId)
            ->where('sale_id', $saleId)
            ->first();

        if (!empty($order)) {
            $order->update([
                'tracking_code' => $data['tracking_code'],
                'status' => BookingOrder::$shipped
            ]);

            $item = $order->item;
            $seller = $order->seller;

            $notifyOptions = [
                '[p.title]' => !empty($item) ? $item->title : '',
                '[u.name]' => !empty($seller) ? $seller->full_name : '',
            ];
            sendNotification('booking_tracking_code', $notifyOptions, $order->buyer_id);
        }

        return response()->json([
            'code' => 200,
            'title' => trans('public.request_success'),
            'msg' => trans('update.tracking_code_success_save'),
        ]);
    }
}