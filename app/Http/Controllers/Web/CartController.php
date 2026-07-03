<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\RegionsDataByUser;
use App\Mixins\Cashback\CashbackRules;
use App\Models\Cart;
use App\Models\CartDiscount;
use App\Models\Discount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentChannel;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Services\CheckoutModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CartController extends Controller
{
    use RegionsDataByUser;

    public function index()
    {
        $user = auth()->user();
        $carts = Cart::where('creator_id', $user->id)
            ->with([
                'user',
                'webinar.teacher',
                'bundle.teacher',
                'eventTicket.event.creator',
                'meetingPackage.creator',
                'booking.creator',
                'bookingOrder.booking.creator',
                'subscribe',
                'promotion',
                'gift',
                'installmentPayment.installmentOrder.webinar.teacher',
                'installmentPayment.installmentOrder.bundle.teacher',
                'installmentPayment.installmentOrder.product.creator',
                'installmentPayment.installmentOrder.subscribe',
                'installmentPayment.installmentOrder.registrationPackage',
                'reserveMeeting' => function ($query) {
                    $query->with([
                        'meeting',
                        'meeting.creator',
                        'meetingTime'
                    ]);
                },
                'ticket',
                'productOrder' => function ($query) {
                    $query->whereHas('product');
                    $query->with(['product.creator']);
                }
            ])
            ->get();

        if (!empty($carts) and !$carts->isEmpty()) {
            $calculate = $this->calculatePrice($carts, $user);

            $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);

            $deliveryEstimateTime = 0;

            if (!empty($hasPhysicalProduct) and count($hasPhysicalProduct)) {
                foreach ($hasPhysicalProduct as $physicalProductCart) {
                    if (!empty($physicalProductCart->productOrder) and
                        !empty($physicalProductCart->productOrder->product) and
                        !empty($physicalProductCart->productOrder->product->delivery_estimated_time) and
                        $physicalProductCart->productOrder->product->delivery_estimated_time > $deliveryEstimateTime
                    ) {
                        $deliveryEstimateTime = $physicalProductCart->productOrder->product->delivery_estimated_time;
                    }
                }
            }

            if (!empty($calculate)) {

                $totalCashbackAmount = $this->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                $cartDiscount = CartDiscount::query()
                    ->where('show_only_on_empty_cart', false)
                    ->where('enable', true)
                    ->first();
                $checkoutModulesByCart = $this->getCheckoutModulesByCart($carts);


                $data = [
                    'pageTitle' => trans('public.cart_page_title'),
                    'user' => $user,
                    'carts' => $carts,
                    'checkoutModulesByCart' => $checkoutModulesByCart,
                    'calculatePrices' => $calculate,
                    'userGroup' => $user->getUserGroup(),
                    'hasPhysicalProduct' => (count($hasPhysicalProduct) > 0),
                    'deliveryEstimateTime' => $deliveryEstimateTime,
                    'totalCashbackAmount' => $totalCashbackAmount,
                    'cartDiscount' => $cartDiscount,
                ];

                $data = array_merge($data, $this->getLocationsData($user));

                return view('design_1.web.cart.overview.index', $data);
            }
        } else {
            $cartDiscount = CartDiscount::query()->where('enable', true)->first();

            if (!empty($cartDiscount)) {
                $data = [
                    'pageTitle' => trans('update.cart_is_empty'),
                    'cartDiscount' => $cartDiscount,
                ];

                return view('design_1.web.cart.empty.index', $data);
            }
        }

        return redirect('/');
    }

    public function couponValidate(Request $request)
    {
        $user = auth()->user();
        $coupon = $request->get('coupon');

        $discountCoupon = Discount::where('code', $coupon)
            ->first();

        if (!empty($discountCoupon)) {
            $checkDiscount = $discountCoupon->checkValidDiscount();
            if ($checkDiscount != 'ok') {
                return response()->json([
                    'error' => [
                        'title' => trans('public.request_failed'),
                        'msg' => $checkDiscount
                    ],
                ], 422);
            }

            $carts = Cart::where('creator_id', $user->id)
                ->get();

            if (!empty($carts) and !$carts->isEmpty()) {
                $calculate = $this->calculatePrice($carts, $user, $discountCoupon);

                if (!empty($calculate)) {
                    $calculate['discountCoupon'] = $discountCoupon;

                    $data = [
                        'calculatePrices' => $calculate
                    ];

                    $html = (string)view()->make("design_1.web.cart.overview.includes.summary", $data);

                    return response()->json([
                        'code' => 200,
                        'html' => $html,
                    ]);
                }
            }
        }


        return response()->json([
            'error' => [
                'title' => trans('public.request_failed'),
                'msg' => trans('cart.coupon_invalid')
            ],
        ], 422);
    }

    private function productDeliveryFeeBySeller($carts)
    {
        $productFee = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;

                if (!empty($product->delivery_fee)) {
                    if (!empty($productFee[$product->creator_id]) and $productFee[$product->creator_id] < $product->delivery_fee) {
                        $productFee[$product->creator_id] = $product->delivery_fee;
                    } else if (empty($productFee[$product->creator_id])) {
                        $productFee[$product->creator_id] = $product->delivery_fee;
                    }
                }
            }
        }

        return $productFee;
    }

    private function physicalProductCountBySeller($carts)
    {
        $productCount = [];

        foreach ($carts as $cart) {
            if (!empty($cart->productOrder) and !empty($cart->productOrder->product)) {
                $product = $cart->productOrder->product;

                if (!empty($product) and $product->isPhysical()) {
                    if (!empty($productCount[$product->creator_id])) {
                        $productCount[$product->creator_id] += 1;
                    } else {
                        $productCount[$product->creator_id] = 1;
                    }
                }
            }
        }

        return $productCount;
    }

    private function getCheckoutModulesByCart($carts): array
    {
        $checkoutModuleService = app(CheckoutModuleService::class);
        $modulesByCart = [];

        foreach ($carts as $cart) {
            $entityType = null;
            $entityId = null;
            $orgId = null;

            if (!empty($cart->webinar_id)) {
                $entityType = 'course';
                $entityId = $cart->webinar_id;
                $orgId = optional($cart->webinar)->teacher_id;
            } elseif (!empty($cart->product_order_id) && !empty($cart->productOrder->product_id)) {
                $entityType = 'product';
                $entityId = $cart->productOrder->product_id;
                $orgId = optional(optional($cart->productOrder)->product)->creator_id;
            } elseif (!empty($cart->reserve_meeting_id)) {
                $entityType = 'booking';
                $entityId = $cart->reserve_meeting_id;
                $orgId = optional(optional($cart->reserveMeeting)->meeting)->creator_id;
            } elseif (!empty($cart->booking_order_id) or !empty($cart->booking_id)) {
                $entityType = 'booking';
                $entityId = optional($cart->booking)->id;
                $orgId = optional($cart->booking)->creator_id;
            } elseif (!empty($cart->meeting_package_id)) {
                $entityType = 'booking';
                $entityId = $cart->meeting_package_id;
                $orgId = optional($cart->meetingPackage)->creator_id;
            }

            if (empty($entityType) || empty($entityId) || empty($orgId)) {
                continue;
            }

            try {
                $modulesByCart[$cart->id] = $checkoutModuleService->getModulesForEntity($entityType, $entityId, $orgId);
            } catch (\Throwable $e) {
                $modulesByCart[$cart->id] = collect();
            }
        }

        return $modulesByCart;
    }

    private function calculateProductDeliveryFee($carts)
    {
        $fee = 0;

        if (!empty($carts)) {
            $productsFee = $this->productDeliveryFeeBySeller($carts);

            if (!empty($productsFee) and count($productsFee)) {
                $fee = array_sum($productsFee);
            }
        }

        return $fee;
    }

    public function calculatePrice($carts, $user, $discountCoupon = null)
    {
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $commission = 0;

        $taxIsDifferent = false;

        foreach ($carts as $cart) {
            $orderPrices = $this->handleOrderPrices($cart, $user, $taxIsDifferent, $discountCoupon);
            $subTotal += $orderPrices['sub_total'];
            $totalDiscount += $orderPrices['total_discount'];
            $tax = $orderPrices['tax'];
            $taxPrice += $orderPrices['tax_price'];
            $commission += $orderPrices['commission'];
            $commissionPrice += $orderPrices['commission_price'];

            if (!$taxIsDifferent) {
                $taxIsDifferent = $orderPrices['tax_is_different'];
            }
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $subTotalWithoutDiscount = $subTotal - $totalDiscount;
        $productDeliveryFee = $this->calculateProductDeliveryFee($carts);

        if (($subTotalWithoutDiscount + $productDeliveryFee) <= 0) {
            $taxPrice = 0;
            $tax = 0;
        }

        $total = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

        if ($total < 0) {
            $total = 0;
        }

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            'total' => round($total, 2),
            'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent
        ];
    }

    public function checkout(Request $request, $carts = null)
    {
        $user = auth()->user();

        if (empty($carts)) {
            $carts = Cart::where('creator_id', $user->id)
                ->get();
        }

        $hasPhysicalProduct = $carts->where('productOrder.product.type', Product::$physical);
        $checkAddressValidation = (count($hasPhysicalProduct) > 0);

        if (empty(getStoreSettings('show_address_selection_in_cart')) or !empty(getStoreSettings('take_address_selection_optional'))) {
            $checkAddressValidation = false;
        }

        $this->validate($request, [
            'country_id' => Rule::requiredIf($checkAddressValidation),
            'province_id' => Rule::requiredIf($checkAddressValidation),
            'city_id' => Rule::requiredIf($checkAddressValidation),
            'district_id' => Rule::requiredIf($checkAddressValidation),
            'address' => Rule::requiredIf($checkAddressValidation),
            'address_line' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        $discountId = $request->input('discount_id');

        $paymentChannels = PaymentChannel::where('status', 'active')->get();

        $discountCoupon = Discount::where('id', $discountId)->first();

        if (empty($discountCoupon) or $discountCoupon->checkValidDiscount() != 'ok') {
            $discountCoupon = null;
        }

        if (!empty($carts) and !$carts->isEmpty()) {
            $checkoutModuleService = app(CheckoutModuleService::class);
            $checkoutModules = $request->input('checkout_modules', []);
            $moduleErrors = [];
            $extraPrice = 0;

            foreach ($carts as $cart) {
                $entityType = null;
                $entityId = null;
                $orgId = null;

                if (!empty($cart->webinar_id)) {
                    $entityType = 'course';
                    $entityId = $cart->webinar_id;
                    $orgId = optional($cart->webinar)->teacher_id;
                } elseif (!empty($cart->product_order_id) && !empty($cart->productOrder->product_id)) {
                    $entityType = 'product';
                    $entityId = $cart->productOrder->product_id;
                    $orgId = optional(optional($cart->productOrder)->product)->creator_id;
                } elseif (!empty($cart->reserve_meeting_id)) {
                    $entityType = 'booking';
                    $entityId = $cart->reserve_meeting_id;
                    $orgId = optional(optional($cart->reserveMeeting)->meeting)->creator_id;
                } elseif (!empty($cart->booking_order_id) or !empty($cart->booking_id)) {
                    $entityType = 'booking';
                    $entityId = optional($cart->booking)->id;
                    $orgId = optional($cart->booking)->creator_id;
                }

                if (empty($entityType) || empty($entityId) || empty($orgId)) {
                    continue;
                }

                try {
                    $modules = $checkoutModuleService->getModulesForEntity($entityType, $entityId, $orgId);
                } catch (\Throwable $e) {
                    $modules = collect();
                }

                if ($modules->isEmpty()) {
                    continue;
                }

                $itemData = $checkoutModules[$cart->id] ?? [];
                $validation = $checkoutModuleService->validateModuleData($modules, $itemData);

                if (!$validation['valid']) {
                    foreach ($validation['errors'] as $field => $message) {
                        $moduleErrors['checkout_modules.' . $cart->id . '.' . $field] = $message;
                    }
                }

                $extraPrice += $checkoutModuleService->calculateExtraPrice($modules, $itemData);
            }

            if (!empty($moduleErrors)) {
                return back()->withErrors($moduleErrors)->withInput();
            }

            $calculate = $this->calculatePrice($carts, $user, $discountCoupon);
            $calculate['extra_price'] = round($extraPrice, 2);
            $calculate['total'] = round($calculate['total'] + $calculate['extra_price'], 2);

            $order = $this->createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon, $request, $checkoutModules);

            if (count($hasPhysicalProduct) > 0) {
                $this->updateProductOrders($request, $carts, $user);
            }

            if (!empty($order) and $order->total_amount > 0) {
                $razorpay = false;
                $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));

                foreach ($paymentChannels as $paymentChannel) {
                    if ($paymentChannel->class_name == 'Razorpay' and (!$isMultiCurrency or in_array(currency(), $paymentChannel->currencies))) {
                        $razorpay = true;
                    }
                }

                $totalCashbackAmount = $this->getTotalCashbackAmount($carts, $user, $calculate["sub_total"]);

                $data = [
                    'pageTitle' => trans('public.checkout_page_title'),
                    'paymentChannels' => $paymentChannels,
                    'carts' => $carts,
                    'calculatePrices' => $calculate,
                    'userGroup' => $user->getUserGroup(),
                    'order' => $order,
                    'count' => $carts->count(),
                    'userCharge' => $user->getAccountingCharge(),
                    'razorpay' => $razorpay,
                    'totalCashbackAmount' => $totalCashbackAmount,
                    'previousUrl' => url()->previous(),
                    'offlineBanks' => \App\Models\OfflineBank::query()->orderBy('created_at', 'desc')->with(['specifications'])->get(),
                ];

                return view('design_1.web.cart.payment.index', $data);
            } else {
                return $this->handlePaymentOrderWithZeroTotalAmount($order);
            }
        }

        return redirect('/cart');
    }

    private function updateProductOrders(Request $request, $carts, $user)
    {
        $data = $request->all();

        foreach ($carts as $cart) {
            if (!empty($cart->product_order_id)) {
                ProductOrder::where('id', $cart->product_order_id)
                    ->where('buyer_id', $user->id)
                    ->update([
                        'message_to_seller' => $data['message_to_seller'],
                    ]);
            }
        }

        $user->update([
            'country_id' => $data['country_id'] ?? $user->country_id,
            'province_id' => $data['province_id'] ?? $user->province_id,
            'city_id' => $data['city_id'] ?? $user->city_id,
            'district_id' => $data['district_id'] ?? $user->district_id,
            'address' => $data['address'] ?? $user->address,
        ]);
    }

    public function createOrderAndOrderItems($carts, $calculate, $user, $discountCoupon = null, Request $request = null, array $checkoutModuleData = [])
    {
        $totalAmount = $calculate["total"];

        $orderTotalDiscount = $calculate["total_discount"];
        if ($orderTotalDiscount > $calculate["sub_total"]) {
            $orderTotalDiscount = $calculate["sub_total"];
        }

        // Remove User Pending Orders
        $this->handleRemoveUserPendingOrders($user);

        $orderData = [
            'user_id' => $user->id,
            'status' => Order::$pending,
            'amount' => $calculate["sub_total"],
            'tax' => ($totalAmount > 0) ? $calculate["tax_price"] : 0, // when total is 0 tax get 0
            'total_discount' => $orderTotalDiscount,
            'total_amount' => ($totalAmount > 0) ? $totalAmount : 0,
            'product_delivery_fee' => $calculate["product_delivery_fee"] ?? null,
            'created_at' => time(),
        ];

        // Add address fields if provided via request
        if (!empty($request)) {
            if (!empty($request->input('address_line'))) $orderData['address_line'] = $request->input('address_line');
            if (!empty($request->input('city'))) $orderData['city'] = $request->input('city');
            if (!empty($request->input('state'))) $orderData['state'] = $request->input('state');
            if (!empty($request->input('country'))) $orderData['country'] = $request->input('country');
            if (!empty($request->input('postal_code'))) $orderData['postal_code'] = $request->input('postal_code');
        }

        $order = Order::create($orderData);

        if (!empty($checkoutModuleData)) {
            try {
                app(CheckoutModuleService::class)->saveOrderMeta($order->id, [
                    'checkout_modules' => $checkoutModuleData,
                ]);
            } catch (\Throwable $e) {
                // Safe fail if module meta storage is unavailable
            }
        }

        $productsFee = $this->productDeliveryFeeBySeller($carts);
        $sellersProductsCount = $this->physicalProductCountBySeller($carts);
        $taxIsDifferent = false;

        foreach ($carts as $cart) {

            $orderPrices = $this->handleOrderPrices($cart, $user, $taxIsDifferent, $discountCoupon);
            $price = $orderPrices['sub_total'];
            $totalDiscount = $orderPrices['total_discount'];
            $tax = $orderPrices['tax'];
            $taxPrice = $orderPrices['tax_price'];
            $commission = $orderPrices['commission'];
            $commissionPrice = $orderPrices['commission_price'];


            $productDeliveryFee = 0;
            if (!empty($cart->product_order_id)) {
                $product = $cart->productOrder->product;

                if (!empty($product) and $product->isPhysical() and !empty($productsFee[$product->creator_id])) {
                    $productDeliveryFee = $productsFee[$product->creator_id];
                }

                $sellerProductCount = !empty($sellersProductsCount[$product->creator_id]) ? $sellersProductsCount[$product->creator_id] : 1;

                $productDeliveryFee = $productDeliveryFee > 0 ? $productDeliveryFee / $sellerProductCount : 0;
            }

            $subTotalWithoutDiscount = $price - $totalDiscount;
            $totalAmount = $subTotalWithoutDiscount + $taxPrice + $productDeliveryFee;

            $ticket = $cart->ticket;
            if (!empty($ticket) and !$ticket->isValid()) {
                $ticket = null;
            }

            if ($totalDiscount > $price) {
                $totalDiscount = $price;
            }

            if ($totalAmount <= 0) {
                $taxPrice = 0;
                $commissionPrice = 0;
            }

            $orderItem = OrderItem::create([
                'user_id' => $user->id,
                'order_id' => $order->id,
                'webinar_id' => $cart->webinar_id ?? null,
                'bundle_id' => $cart->bundle_id ?? null,
                'event_ticket_id' => $cart->event_ticket_id ?? null,
                'product_id' => (!empty($cart->product_order_id) and !empty($cart->productOrder->product)) ? $cart->productOrder->product->id : null,
                'product_order_id' => (!empty($cart->product_order_id)) ? $cart->product_order_id : null,
                'booking_order_id' => $cart->booking_order_id ?? null,
                'reserve_meeting_id' => $cart->reserve_meeting_id ?? null,
                'meeting_package_id' => $cart->meeting_package_id ?? null,
                'subscribe_id' => $cart->subscribe_id ?? null,
                'promotion_id' => $cart->promotion_id ?? null,
                'gift_id' => $cart->gift_id ?? null,
                'installment_payment_id' => $cart->installment_payment_id ?? null,
                'ticket_id' => !empty($ticket) ? $ticket->id : null,
                'discount_id' => $discountCoupon ? $discountCoupon->id : null,
                'quantity' => $cart->quantity ?? 1,
                'amount' => $price,
                'total_amount' => $totalAmount,
                'tax' => $tax,
                'tax_price' => $taxPrice,
                'commission' => $commission,
                'commission_price' => $commissionPrice,
                'product_delivery_fee' => $productDeliveryFee,
                'discount' => $totalDiscount,
                'created_at' => time(),
            ]);

            $itemModuleData = $checkoutModuleData[$cart->id] ?? [];

            if (!empty($itemModuleData)) {
                try {
                    app(CheckoutModuleService::class)->saveOrderItemMeta($orderItem->id, $itemModuleData);
                } catch (\Throwable $e) {
                    // Safe fail if module meta storage is unavailable
                }
            }
        }

        return $order;
    }

    private function handleRemoveUserPendingOrders($user)
    {
        $userPendingOrderIds = Order::query()->where('user_id', $user->id)
            ->where('status', Order::$pending)
            ->pluck('id')
            ->toArray();
        OrderItem::query()->whereIn('order_id', $userPendingOrderIds)
            ->where('user_id', $user->id)
            ->delete();
        Order::query()->where('user_id', $user->id)
            ->where('status', Order::$pending)
            ->delete();
    }

    private function getSeller($cart)
    {
        $user = null;

        if (!empty($cart->webinar_id) or !empty($cart->bundle_id)) {
            $user = $cart->webinar_id ? $cart->webinar->creator : $cart->bundle->creator;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $user = $cart->reserveMeeting->meeting->creator;
        } elseif (!empty($cart->booking_order_id) or !empty($cart->booking_id)) {
            $user = $cart->booking->creator;
        } elseif (!empty($cart->product_order_id) and !empty($cart->productOrder)) {
            $user = $cart->productOrder->seller;
        } elseif (!empty($cart->event_ticket_id) and !empty($cart->eventTicket)) {
            $user = $cart->eventTicket->event->creator;
        } elseif (!empty($cart->meeting_package_id) and !empty($cart->meetingPackage)) {
            $user = $cart->meetingPackage->creator;
        }

        return $user;
    }

    /**
     * @param $sources => \App\Models\UserCommission::$sources
     * @param $itemPrice
     * @param null $seller
     * */
    private function getCommissionPrice($source, $itemPrice, $seller = null)
    {
        $hasSellerSpecificCommission = false;
        $commissionPrice = 0;

        if (!empty($seller)) {
            $userCommission = $seller->commissions()->where('source', $source)->first();

            if (!empty($userCommission)) {
                $hasSellerSpecificCommission = true;
                $commissionPrice = $userCommission->calculatePrice($itemPrice);
            } else {
                $userGroup = $seller->getUserGroup();

                if (!empty($userGroup)) {
                    $groupCommission = $userGroup->commissions()->where('source', $source)->first();

                    if (!empty($groupCommission)) {
                        $hasSellerSpecificCommission = true;
                        $commissionPrice = $groupCommission->calculatePrice($itemPrice);
                    }
                }
            }
        }

        if (!$hasSellerSpecificCommission) {
            // Get System Default Commission

            $commissionSettings = getCommissionSettings();

            if (!empty($commissionSettings) and !empty($commissionSettings[$source]) and !empty($commissionSettings[$source]['type']) and !empty($commissionSettings[$source]['value'])) {
                $type = $commissionSettings[$source]['type'];
                $value = $commissionSettings[$source]['value'];

                if ($type == "percent") {
                    $commissionPrice = $itemPrice > 0 ? (($itemPrice * $value) / 100) : 0;
                } else {
                    $commissionPrice = $value;
                }
            }
        }

        return $commissionPrice;
    }

    private function getBookingCommissionPrice($booking, $itemPrice, $seller = null)
    {
        if (!empty($seller)) {
            $userCommission = $seller->commissions()->where('source', 'bookings')->first();

            if (!empty($userCommission)) {
                return $userCommission->calculatePrice($itemPrice);
            }

            $userGroup = $seller->getUserGroup();

            if (!empty($userGroup)) {
                $groupCommission = $userGroup->commissions()->where('source', 'bookings')->first();

                if (!empty($groupCommission)) {
                    return $groupCommission->calculatePrice($itemPrice);
                }
            }
        }

        $commissionSettings = getCommissionSettings();
        $categoryKey = $this->getBookingCommissionCategoryKey($booking);

        $settingMap = [
            'real_estate' => ['type' => 'commission_real_estate_type', 'value' => 'commission_real_estate_value', 'default' => 20],
            'lifestyle' => ['type' => 'commission_lifestyle_type', 'value' => 'commission_lifestyle_value', 'default' => 20],
            'healthcare' => ['type' => 'commission_healthcare_type', 'value' => 'commission_healthcare_value', 'default' => 20],
            'automotive' => ['type' => 'commission_automotive_type', 'value' => 'commission_automotive_value', 'default' => 20],
            'tutoring' => ['type' => 'commission_tutoring_type', 'value' => 'commission_tutoring_value', 'default' => 20],
            'consulting' => ['type' => 'commission_consulting_type', 'value' => 'commission_consulting_value', 'default' => 30],
            'default' => ['type' => 'booking_commission_type', 'value' => 'booking_commission_value', 'default' => 30],
        ];

        $settingKeys = $settingMap[$categoryKey] ?? $settingMap['default'];
        $type = $commissionSettings[$settingKeys['type']] ?? $commissionSettings['booking_commission_type'] ?? 'percent';
        $value = $commissionSettings[$settingKeys['value']] ?? $commissionSettings['booking_commission_value'] ?? $settingKeys['default'];

        if ($type == "percent") {
            return $itemPrice > 0 ? (($itemPrice * (float) $value) / 100) : 0;
        }

        return (float) $value;
    }

    private function getBookingCommissionCategoryKey($booking)
    {
        $category = optional($booking)->category;
        $values = [];

        while (!empty($category)) {
            $values[] = $category->slug;
            $values[] = $category->title;
            $category = $category->parent;
        }

        $categoryText = Str::of(implode(' ', array_filter($values)))->lower()->replace(['_', '&'], ['-', 'and'])->toString();

        if (Str::contains($categoryText, ['real-estate', 'real estate', 'home'])) {
            return 'real_estate';
        }

        if (Str::contains($categoryText, ['lifestyle', 'event'])) {
            return 'lifestyle';
        }

        if (Str::contains($categoryText, ['healthcare', 'health', 'wellness'])) {
            return 'healthcare';
        }

        if (Str::contains($categoryText, ['automotive', 'technical'])) {
            return 'automotive';
        }

        if (Str::contains($categoryText, ['tutoring', 'trainer'])) {
            return 'tutoring';
        }

        if (Str::contains($categoryText, ['consulting', 'legal', 'finance'])) {
            return 'consulting';
        }

        return 'default';
    }


    public function handleOrderPrices($cart, $user, $taxIsDifferent = false, $discountCoupon = null)
    {
        $seller = $this->getSeller($cart);
        $financialSettings = getFinancialSettings();

        $subTotal = 0;
        $totalDiscount = 0;
        $tax = (!empty($financialSettings['tax']) and $financialSettings['tax'] > 0) ? $financialSettings['tax'] : 0;
        $taxPrice = 0;
        $commissionPrice = 0;
        $priceWithoutDiscount = 0;

        if (!empty($cart->webinar_id) or !empty($cart->bundle_id)) {
            $item = !empty($cart->webinar_id) ? $cart->webinar : $cart->bundle;
            $price = $item->price;
            $discount = $item->getDiscount($cart->ticket, $user);

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $source = !empty($cart->webinar_id) ? 'courses' : 'bundles';
            $commissionPrice += $this->getCommissionPrice($source, $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $price = $cart->reserveMeeting->paid_amount;
            $discount = $cart->reserveMeeting->getDiscountPrice($user);

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $commissionPrice += $this->getCommissionPrice('meetings', $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->booking_order_id) or !empty($cart->booking_id)) {
            $booking = $cart->booking;

            if (!empty($booking)) {
                $price = (float) $booking->price;
                $discountPrice = !empty($booking->discount_price) ? (float) $booking->discount_price : $price;
                $discount = max(0, $price - $discountPrice);

                $priceWithoutDiscount = $price - $discount;

                $bookingTax = !is_null($booking->tax) ? (float) $booking->tax : $tax;
                $taxIsDifferent = ($tax != $bookingTax);
                $tax = $bookingTax;

                if ($bookingTax > 0 and $priceWithoutDiscount > 0) {
                    $taxPrice += $priceWithoutDiscount * $bookingTax / 100;
                }

                if (!empty($booking->commission)) {
                    $commissionPrice += ($booking->commission > 0)
                        ? (($priceWithoutDiscount * $booking->commission) / 100)
                        : 0;
                } else {
                    $commissionPrice += $this->getBookingCommissionPrice($booking, $priceWithoutDiscount, $seller);
                }

                $totalDiscount += $discount;
                $subTotal += $price;
            }
        } elseif (!empty($cart->product_order_id)) {
            $product = $cart->productOrder->product;

            if (!empty($product)) {
                $productQuantity = $cart->productOrder->quantity;
                $price = ($product->price * $productQuantity);
                $discount = $product->getDiscountPrice() * $productQuantity;

                $productTax = $product->getTax();

                $priceWithoutDiscount = $price - $discount;

                $taxIsDifferent = ($tax != $productTax);

                $tax = $productTax;
                if ($productTax > 0 and $priceWithoutDiscount > 0) {
                    $taxPrice += $priceWithoutDiscount * $productTax / 100;
                }

                // Product Commission
                if (isset($product->commission)) {
                    if ($product->commission_type == "percent") {
                        $commissionPrice += ($priceWithoutDiscount > 0 and $product->commission > 0) ? (($priceWithoutDiscount * $product->commission) / 100) : 0;
                    } else {
                        $commissionPrice += $product->commission;
                    }
                } else {
                    $source = ($product->type == Product::$physical) ? 'physical_products' : 'virtual_products';
                    $commissionPrice += $this->getCommissionPrice($source, $priceWithoutDiscount, $seller);
                }

                $totalDiscount += $discount;
                $subTotal += $price;
            }
        } elseif (!empty($cart->installment_payment_id)) {
            $price = $cart->installmentPayment->amount;
            $discount = 0;

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            // Commission
            $installmentOrder = $cart->installmentPayment->installmentOrder;

            if (!empty($installmentOrder)) {
                $source = null;

                if (!empty($installmentOrder->webinar_id)) {
                    $source = "courses";
                } elseif (!empty($installmentOrder->bundle_id)) {
                    $source = "bundles";
                } elseif (!empty($installmentOrder->product_id) and !empty($installmentOrder->product)) {
                    if ($installmentOrder->product->type == Product::$physical) {
                        $source = "physical_products";
                    } else {
                        $source = "virtual_products";
                    }
                }

                if (!empty($source)) {
                    $commissionPrice += $this->getCommissionPrice($source, $priceWithoutDiscount, $seller);
                }
            }

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->event_ticket_id)) {
            $quantity = $cart->quantity ?? 1;
            $eventTicket = $cart->eventTicket;
            $price = $eventTicket->price * $quantity;
            $discount = $eventTicket->getDiscountPrice() * $quantity;

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $commissionPrice += $this->getCommissionPrice("events", $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        } elseif (!empty($cart->meeting_package_id)) {
            $meetingPackage = $cart->meetingPackage;

            $price = $meetingPackage->price;
            $discount = $meetingPackage->getDiscountPrice();

            $priceWithoutDiscount = $price - $discount;

            if ($tax > 0 and $priceWithoutDiscount > 0) {
                $taxPrice += $priceWithoutDiscount * $tax / 100;
            }

            $commissionPrice += $this->getCommissionPrice("meeting_packages", $priceWithoutDiscount, $seller);

            $totalDiscount += $discount;
            $subTotal += $price;
        }

        if (!empty($discountCoupon)) {
            $totalDiscount += $this->getCouponDiscountByCartItem($discountCoupon, $cart, $user);
        }

        $userGroup = $user->getUserGroup();
        if (!empty($userGroup) and !empty($userGroup->discount) and $subTotal > 0) {
            $totalDiscount += ($subTotal * $userGroup->discount) / 100;
        }

        if ($totalDiscount > $subTotal) {
            $totalDiscount = $subTotal;
        }

        $commission = ($commissionPrice > 0 and $priceWithoutDiscount > 0) ? (($commissionPrice / $priceWithoutDiscount) * 100) : 0;

        return [
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'tax' => $tax,
            'tax_price' => round($taxPrice, 2),
            'commission' => $commission,
            'commission_price' => round($commissionPrice, 2),
            //'product_delivery_fee' => round($productDeliveryFee, 2),
            'tax_is_different' => $taxIsDifferent
        ];
    }

    private function handlePaymentOrderWithZeroTotalAmount($order)
    {
        $order->update([
            'payment_method' => Order::$paymentChannel
        ]);

        $paymentController = new PaymentController();

        $paymentController->setPaymentAccounting($order);

        $order->update([
            'status' => Order::$paid
        ]);

        return redirect('/payments/status?t=' . $order->id);
    }


    private function getTotalCashbackAmount($carts, $user, $subTotal)
    {
        $amount = 0;

        if (getFeaturesSettings('cashback_active') and (empty($user) or !$user->disable_cashback)) {
            $cashbackRulesMixin = new CashbackRules($user);
            $applyPerItemRules = [];

            foreach ($carts as $cart) {
                $rules = $cashbackRulesMixin->getRulesByItem($cart);

                if (!empty($rules) and count($rules)) {
                    foreach ($rules as $rule) {
                        if (empty($rule->min_amount) or $rule->min_amount <= $subTotal) {
                            if ($rule->amount_type == "fixed_amount") {
                                if ($rule->apply_cashback_per_item and $rule->apply_cashback_per_item > 0) {
                                    $amount += $rule->amount;
                                } else {
                                    $applyPerItemRules[$rule->id] = $rule;
                                }
                            } else if ($rule->amount_type == "percent") {
                                $itemOrder = $this->handleOrderPrices($cart, $user);
                                $itemPrice = $itemOrder['sub_total'];

                                if (!empty($itemOrder['total_discount'])) {
                                    $itemPrice = $itemPrice - $itemOrder['total_discount'];
                                }

                                $ruleAmount = $rule->getAmount($itemPrice);

                                if (!empty($rule->max_amount) and $rule->max_amount < $ruleAmount) {
                                    $amount += $rule->max_amount;
                                } else {
                                    $amount += $ruleAmount;
                                }
                            }
                        }
                    }
                }
            }


            if (!empty($applyPerItemRules)) {
                foreach ($applyPerItemRules as $applyPerItemRule) {
                    $amount += $applyPerItemRule->amount;
                }
            }
        }

        return $amount;
    }

    private function handleDiscountPrice($discount, $carts, $subTotal)
    {
        $user = auth()->user();
        $totalDiscount = 0;

        foreach ($carts as $cart) {
            $totalDiscount += $this->getCouponDiscountByCartItem($discount, $cart, $user);
        }

        if ($discount->discount_type != Discount::$discountTypeFixedAmount and !empty($discount->max_amount) and $totalDiscount > $discount->max_amount) {
            $totalDiscount = $discount->max_amount;
        }

        return $totalDiscount;
    }

    private function getCouponDiscountByCartItem($couponDiscount, $cart, $user)
    {
        $applyDiscount = false;
        $percent = $couponDiscount->percent ?? 1;
        //$otherDiscounts = 0;
        $totalCouponDiscount = 0;
        $totalItemAmount = 0;

        if ($couponDiscount->source == Discount::$discountSourceCourse) {
            $discountWebinarsIds = $couponDiscount->discountCourses()->pluck('course_id')->toArray();
            $webinar = $cart->webinar;
            if (!empty($webinar) and (in_array($webinar->id, $discountWebinarsIds) or count($discountWebinarsIds) < 1)) {
                $totalItemAmount += $webinar->price;
                //$otherDiscounts += $webinar->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceBundle) {
            $discountBundlesIds = $couponDiscount->discountBundles()->pluck('bundle_id')->toArray();
            $bundle = $cart->bundle;
            if (!empty($bundle) and (in_array($bundle->id, $discountBundlesIds) or count($discountBundlesIds) < 1)) {
                $totalItemAmount += $bundle->price;
                //$otherDiscounts += $bundle->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceEvent) {
            $discountEventsIds = $couponDiscount->discountEvents()->pluck('event_id')->toArray();
            $eventTicket = $cart->eventTicket;
            $quantity = $cart->quantity ?? 1;

            if (!empty($eventTicket) and (in_array($eventTicket->event_id, $discountEventsIds) or count($discountEventsIds) < 1)) {
                $totalItemAmount += $eventTicket->price * $quantity;
                //$otherDiscounts += $event->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceMeetingPackage) {
            $discountMeetingPackagesIds = $couponDiscount->discountMeetingPackages()->pluck('meeting_package_id')->toArray();
            $meetingPackage = $cart->meetingPackage;

            if (!empty($meetingPackage) and (in_array($meetingPackage->id, $discountMeetingPackagesIds) or count($discountMeetingPackagesIds) < 1)) {
                $totalItemAmount += $meetingPackage->price;
                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceProduct) {
            if (!empty($cart->productOrder)) {
                $product = $cart->productOrder->product;

                if (!empty($product) and ($couponDiscount->product_type == 'all' or $couponDiscount->product_type == $product->type)) {
                    $productQuantity = $cart->productOrder->quantity;
                    $totalItemAmount += ($product->price * $productQuantity);
                    //$otherDiscounts += $product->getDiscountPrice() * $productQuantity;

                    $applyDiscount = true;
                }
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceMeeting) {
            $reserveMeeting = $cart->reserveMeeting;
            if (!empty($reserveMeeting)) {
                $totalItemAmount += $reserveMeeting->paid_amount;
                //$otherDiscounts += $reserveMeeting->getDiscountPrice($user);

                $applyDiscount = true;
            }
        } elseif ($couponDiscount->source == Discount::$discountSourceCategory) {
            $webinar = $cart->webinar;
            $categoriesIds = ($couponDiscount->discountCategories) ? $couponDiscount->discountCategories()->pluck('category_id')->toArray() : [];
            if (!empty($webinar) and in_array($webinar->category_id, $categoriesIds)) {
                $totalItemAmount += $webinar->price;
                //$otherDiscounts += $webinar->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }
        } else {
            // All Source
            $webinar = $cart->webinar;
            $bundle = $cart->bundle;
            $reserveMeeting = $cart->reserveMeeting;

            if (!empty($webinar)) {
                $totalItemAmount += $webinar->price;
                //$otherDiscounts += $webinar->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }

            if (!empty($reserveMeeting)) {
                $totalItemAmount += $reserveMeeting->paid_amount;
                //$otherDiscounts += $reserveMeeting->getDiscountPrice($user);

                $applyDiscount = true;
            }

            if (!empty($bundle)) {
                $totalItemAmount += $bundle->price;
                //$otherDiscounts += $bundle->getDiscount($cart->ticket, $user);

                $applyDiscount = true;
            }

            if (!empty($cart->productOrder)) {
                $product = $cart->productOrder->product;

                if (!empty($product)) {
                    $totalItemAmount += ($product->price * $cart->productOrder->quantity);
                    //$otherDiscounts += $product->getDiscountPrice();

                    $applyDiscount = true;
                }
            }

            $eventTicket = $cart->eventTicket;
            if (!empty($eventTicket)) {
                $quantity = $cart->quantity ?? 1;

                $totalItemAmount += $eventTicket->price * $quantity;
                $applyDiscount = true;
            }

            $meetingPackage = $cart->meetingPackage;
            if (!empty($meetingPackage)) {
                $totalItemAmount += $meetingPackage->price;
                $applyDiscount = true;
            }
        }


        if ($applyDiscount) {
            if ($couponDiscount->discount_type == Discount::$discountTypeFixedAmount) {
                $totalCouponDiscount = ($totalItemAmount > $couponDiscount->amount) ? $couponDiscount->amount : $totalItemAmount;
            } else {
                $totalCouponDiscount = ($totalItemAmount > 0) ? $totalItemAmount * $percent / 100 : 0;
            }

            if ($couponDiscount->discount_type != Discount::$discountTypeFixedAmount and !empty($couponDiscount->max_amount) and $totalCouponDiscount > $couponDiscount->max_amount) {
                $totalCouponDiscount = $couponDiscount->max_amount;
            }
        }

        return $totalCouponDiscount;
    }

    private function taxIsDifferent($carts)
    {
        $cartHasWebinar = array_filter($carts->pluck('webinar_id')->toArray());
        $cartHasBundle = array_filter($carts->pluck('bundle_id')->toArray());
        $cartHasMeeting = array_filter($carts->pluck('reserve_meeting_id')->toArray());
        $cartHasInstallmentPayment = array_filter($carts->pluck('installment_payment_id')->toArray());

        return (count($cartHasWebinar) or count($cartHasBundle) or count($cartHasMeeting) or count($cartHasInstallmentPayment));
    }
}
