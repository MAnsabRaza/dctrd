@extends(getTemplate() . '.layouts.app')


@section('content')
    <section class="cart-banner position-relative text-center">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.shopping_cart') }}</h1>
        <span class="payment-hint font-20 text-white d-block">
            {{ handlePrice($subTotal, true, true, false, null, true) . ' ' . trans('cart.for_items', ['count' => $carts->count()]) }}</span>
    </section>

    <div class="container">

        @if (!empty($totalCashbackAmount))
            <div class="d-flex align-items-center mt-45 p-15 success-transparent-alert">
                <div class="success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=""></i>
                </div>

                <div class="ml-10">
                    <div class="font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class="font-12 ">
                        {{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback', ['amount' => handlePrice($totalCashbackAmount)]) }}
                    </div>
                </div>
            </div>
        @endif

        @if (!empty($cartDiscount))
            @include('web.default.cart.includes.cart_discount', [
                'cartDiscountClassName' => 'is-cart-page',
            ])
        @endif

        <section class="mt-45">
            <h2 class="section-title">{{ trans('cart.cart_items') }}</h2>

            <div class="rounded-sm shadow mt-20 py-25 px-10 px-md-30">
                @if ($carts->count() > 0)
                    <div class="row d-none d-md-flex">
                        <div class="col-12 col-lg-8"><span class="text-gray font-weight-500">{{ trans('cart.item') }}</span>
                        </div>
                        <div class="col-6 col-lg-2 text-center"><span
                                class="text-gray font-weight-500">{{ trans('public.price') }}</span></div>
                        <div class="col-6 col-lg-2 text-center"><span
                                class="text-gray font-weight-500">{{ trans('public.remove') }}</span></div>
                    </div>
                @endif
                @php
                    $cart_items = [];
                    $vendors_items = [];
                    $item_model_classes = [];
                @endphp

                @foreach ($carts as $cart)
                    @php
                        if ($cart->webinar_id) {
                            $cart_items[] = $cart->webinar_id;
                            $vendors_items[] = $cart->webinar->teacher_id ?? null;
                            $item_model_classes[] = 'App\Models\Webinar';
                        }

                        if ($cart->bundle_id) {
                            $cart_items[] = $cart->bundle_id;
                            $vendors_items[] = $cart->bundle->teacher_id ?? null;
                            // $item_model_classes[] = 'App\Models\Bundle';
                        }

                        if ($cart->product_order_id) {
                            $productId = $cart->productOrder?->product_id;
                            $vendorId = $cart->productOrder?->product?->creator_id;

                            if ($productId) {
                                $cart_items[] = $productId;
                                $vendors_items[] = $vendorId;
                                $item_model_classes[] = 'App\Models\Product';
                            }
                        }
                    @endphp

                    <div class="row mt-5 cart-row">
                        <div class="col-12 col-lg-8 mb-15 mb-md-0">
                            <div class="webinar-card webinar-list-cart row">
                                <div class="col-4">
                                    <div class="image-box">
                                        @php
                                            $cartItemInfo = $cart->getItemInfo();
                                            $cartTaxType = !empty($cartItemInfo['isProduct']) ? 'store' : 'general';
                                        @endphp
                                        <img src="{{ $cartItemInfo['imgPath'] }}" class="img-cover" alt="user avatar">
                                    </div>
                                </div>

                                <div class="col-8">
                                    <div class="webinar-card-body p-0 w-100 h-100 d-flex flex-column">
                                        <div class="d-flex flex-column">
                                            <a href="{{ $cartItemInfo['itemUrl'] ?? '#!' }}" target="_blank">
                                                <h3 class="font-16 font-weight-bold text-dark-blue">
                                                    {{ $cartItemInfo['title'] }}</h3>
                                            </a>

                                            @if (!empty($cart->gift_id) and !empty($cart->gift))
                                                <span class="d-block mt-5 text-gray font-12">{!! trans('update.a_gift_for_name_on_date', [
                                                    'name' => $cart->gift->name,
                                                    'date' => !empty($cart->gift->date) ? dateTimeFormat($cart->gift->date, 'j M Y H:i') : trans('update.instantly'),
                                                ]) !!}</span>
                                            @endif
                                        </div>

                                        @if (!empty($cart->reserve_meeting_id))
                                            <div class="mt-10">
                                                <span
                                                    class="text-gray font-12 border rounded-pill py-5 px-10">{{ $cart->reserveMeeting->day . ' ' . $cart->reserveMeeting->meetingTime->time }}
                                                    ({{ $cart->reserveMeeting->meeting->getTimezone() }})
                                                </span>
                                            </div>

                                            @if ($cart->reserveMeeting->meeting->getTimezone() != getTimezone())
                                                <div class="mt-10">
                                                    <span
                                                        class="text-danger font-12 border border-danger rounded-pill py-5 px-10">{{ $cart->reserveMeeting->day . ' ' . dateTimeFormat($cart->reserveMeeting->start_at, 'h:iA', false) . '-' . dateTimeFormat($cart->reserveMeeting->end_at, 'h:iA', false) }}
                                                        ({{ getTimezone() }})</span>
                                                </div>
                                            @endif
                                        @endif

                                        @if (!empty($cartItemInfo['profileUrl']) and !empty($cartItemInfo['teacherName']))
                                            <span class="text-gray font-14 mt-auto">
                                                {{ trans('public.by') }}
                                                <a href="{{ $cartItemInfo['profileUrl'] }}" target="_blank"
                                                    class="text-gray text-decoration-underline">{{ $cartItemInfo['teacherName'] }}</a>
                                            </span>
                                        @endif

                                        @if (!empty($cartItemInfo['extraHint']))
                                            <span class="text-gray font-14 mt-auto">{{ $cartItemInfo['extraHint'] }}</span>
                                        @endif

                                        @if (!is_null($cartItemInfo['rate']))
                                            @include('web.default.includes.webinar.rate', [
                                                'rate' => $cartItemInfo['rate'],
                                            ])
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-lg-2 d-flex flex-md-column align-items-center justify-content-center">
                            <span class="text-gray d-inline-block d-md-none">{{ trans('public.price') }} :</span>

                            @if (!empty($cartItemInfo['discountPrice']))
                                <span
                                    class="text-gray text-decoration-line-through mx-10 mx-md-0">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true, $cartTaxType) }}</span>
                                <span
                                    class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true, $cartTaxType) }}</span>
                            @else
                                <span
                                    class="font-20 text-primary mt-0 mt-md-5 font-weight-bold">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true, $cartTaxType) }}</span>
                            @endif

                            @if (!empty($cartItemInfo['quantity']))
                                <span
                                    class="font-12 text-warning font-weight-500 mt-0 mt-md-5">({{ $cartItemInfo['quantity'] }}
                                    {{ trans('update.product') }})</span>
                            @endif

                            @if (!empty($cartItemInfo['extraPriceHint']))
                                <span
                                    class="font-12 text-gray font-weight-500 mt-0 mt-md-5">{{ $cartItemInfo['extraPriceHint'] }}</span>
                            @endif
                        </div>

                        <div class="col-6 col-lg-2 d-flex flex-md-column align-items-center justify-content-center">
                            <span class="text-gray d-inline-block d-md-none mr-10 mr-md-0">{{ trans('public.remove') }}
                                :</span>

                            <a href="/cart/{{ $cart->id }}/delete"
                                class="delete-action btn-cart-list-delete d-flex align-items-center justify-content-center">
                                <i data-feather="x" width="20" height="20" class=""></i>
                            </a>
                        </div>
                    </div>

                    @php
                        $cart_items = array_filter($cart_items);
                        $vendors_items = array_filter(array_unique($vendors_items));
                        $item_model_classes = array_filter(array_unique($item_model_classes));
                    @endphp
                @endforeach

                <button type="button" onclick="window.history.back()"
                    class="btn btn-sm btn-primary mt-25">{{ trans('cart.continue_shopping') }}</button>
            </div>
        </section>
        {{-- ++++++++++++++++++++++ checkout ++++++++++++++++++++++ --}}
        <form action="/cart/checkout" method="post" id="cartForm">
            {{ csrf_field() }}
            <input type="hidden" name="discount_id" value="">

            @if ($hasPhysicalProduct)
                @include('web.default.cart.includes.shipping_and_delivery')
            @endif

            <div class="row mt-30">
                <div class="col-12 col-lg-6">
                    <section class="mt-45">
                        <h3 class="section-title">{{ trans('cart.coupon_code') }}</h3>
                        <div class="rounded-sm shadow mt-20 py-25 px-20">
                            <p class="text-gray font-14">{{ trans('cart.coupon_code_hint') }}</p>

                            @if (!empty($userGroup) and !empty($userGroup->discount))
                                <p class="text-gray mt-25">
                                    {{ trans('cart.in_user_group', ['group_name' => $userGroup->name, 'percent' => $userGroup->discount]) }}
                                </p>
                            @endif

                            <form action="/carts/coupon/validate" method="Post">
                                {{ csrf_field() }}
                                <div class="form-group">
                                    <input type="text" name="coupon" id="coupon_input" class="form-control mt-25"
                                        placeholder="{{ trans('cart.enter_your_code_here') }}">
                                    <span class="invalid-feedback">{{ trans('cart.coupon_invalid') }}</span>
                                    <span class="valid-feedback">{{ trans('cart.coupon_valid') }}</span>
                                </div>

                                <button type="submit" id="checkCoupon"
                                    class="btn btn-sm btn-primary mt-50">{{ trans('cart.validate') }}</button>
                            </form>
                        </div>
                    </section>
                </div>

                <div class="col-12 col-lg-6">
                    <section class="mt-45">
                        <h3 class="section-title">{{ trans('cart.cart_totals') }}</h3>
                        <div class="rounded-sm shadow mt-20 pb-20 px-20">
                            {{-- ++++++++++++++++ subTotal +++++++++++++++ --}}
                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.sub_total') }}</h4>
                                <span class="font-14 text-gray font-weight-bold">{{ handlePrice($subTotal) }}</span>
                            </div>
                            {{-- ++++++++++++++++ discount +++++++++++++++ --}}
                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('public.discount') }}</h4>
                                <span class="font-14 text-gray font-weight-bold">
                                    <span id="totalDiscount">{{ handlePrice($totalDiscount) }}</span>
                                </span>
                            </div>

                            <div class="cart-checkout-item">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.tax') }}
                                    @if (!$taxIsDifferent)
                                        <span class="font-14 text-gray ">({{ $tax }}%)</span>
                                    @endif
                                </h4>
                                <span class="font-14 text-gray font-weight-bold"><span
                                        id="taxPrice">{{ handlePrice($taxPrice) }}</span></span>
                            </div>

                            @if (!empty($productDeliveryFee))
                                <div class="cart-checkout-item">
                                    <h4 class="text-secondary font-14 font-weight-500">
                                        {{ trans('update.delivery_fee') }}
                                    </h4>
                                    <span class="font-14 text-gray font-weight-bold"><span
                                            id="taxPrice">{{ handlePrice($productDeliveryFee) }}</span></span>
                                </div>
                            @endif

                            <div class="cart-checkout-item border-0">
                                <h4 class="text-secondary font-14 font-weight-500">{{ trans('cart.total') }}</h4>
                                <span class="font-14 text-gray font-weight-bold"><span
                                        id="totalAmount">{{ handlePrice($total) }}</span></span>
                            </div>

                            <button type="submit"
                                class="btn btn-sm btn-primary mt-15">{{ trans('cart.checkout') }}</button>
                        </div>
                    </section>
                </div>
            </div>
        </form>

        @php
            use App\Services\CrossSellingService;
            use App\Services\UpSellingService;

            $crossSellingService = new CrossSellingService();
            $upSellingService = new UpSellingService();

            $cart_model = null;
            $cart_model_type = null;
            $cart_model_id = null;
            $owner_id = null;

            $cart_model_data = $carts->first();

            if ($cart_model_data) {
                $isCourse = $cart_model_data->webinar_id !== null;

                $cart_model = $isCourse ? 'App\Models\Webinar' : 'App\Models\Product';
                $cart_model_type = $isCourse ? 'course' : 'product';
                $cart_model_id = $isCourse ? $cart_model_data->webinar_id : $cart_model_data->product_order_id;

                $owner_id = $isCourse
                    ? optional($cart_model_data->webinar)->teacher_id
                    : optional(optional($cart_model_data->productOrder)->product)->creator_id;
            }

            if ($cart_model && $cart_model_type && $cart_model_id && $owner_id) {
                $settings = \App\Models\UserUpSelling::where('user_id', $owner_id)->first();

                $cross_selling = $crossSellingService->getCrossSellingItemsWithSettings(
                    $owner_id,
                    $cart_model,
                    $cart_model_id,
                    'cart',
                );

                $up_selling = $upSellingService->getUpsellItems($owner_id, $cart_model_id, $cart_model_type,'cart');
            } else {
                $cross_selling = null;
                $up_selling = null;
            }
        @endphp


        @if ($cross_selling != null && count($cross_selling['products']) > 0 && $cart_model_type != null)
            {{-- Display Popup --}}
            @if (($cross_selling['display_on'] ?? null) === 1)
                @include('web.default.cross_up_selling.popup', [
                    'products' => $cross_selling['products'],
                    'title' => $cross_selling['description'] ?? '',
                    'slider' => $cross_selling['slider'] ?? false,
                    'display' => $cross_selling['display_on'],
                    'type' => $cart_model_type,
                ])
            @endif

            {{-- Display Blade Component --}}
            @if (($cross_selling['display_on'] ?? null) === 2)
                <x-cross-selling :products="$cross_selling['products']" :title="$cross_selling['description'] ?? ''" :slider="$cross_selling['slider'] ?? false" :display="$cross_selling['display_on']"
                    :type="$cart_model_type" />
            @endif
            
            @if (($cross_selling['display_on'] ?? null) === 3)
                <x-cross-selling :products="$cross_selling['products']" :title="$cross_selling['description'] ?? ''" :slider="$cross_selling['slider'] ?? false" :display="$cross_selling['display_on']"
                    :type="$cart_model_type" />
            @endif

            @if (($cross_selling['display_on'] ?? null) == 4 && !empty($cross_selling['products']))
                <x-cross-selling :products="$cross_selling['products']" :title="$cross_selling['description'] ?? ''" :slider="$cross_selling['slider'] ?? false" :display="$cross_selling['display_on']" :type="$cart_model_type" />
            @endif
        @endif

        @if ($settings != null && $up_selling != null && $cart_model_type != null)
            <x-up-selling :products="$up_selling" :title="trans('update.Recommend Items')" :slider="true" :display="2" :type="$cart_model_type" />
        @endif

    </div>
@endsection

@push('scripts_bottom')
    <script>
        var couponInvalidLng = '{{ trans('cart.coupon_invalid') }}';
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
    </script>

    <script src="/assets/default/js/parts/get-regions.min.js"></script>
    <script src="/assets/default/js/parts/cart.min.js"></script>
@endpush
