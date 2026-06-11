@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("cart_page") }}">
@endpush

@section("content")
    @php $userCurrencyItem = getUserCurrencyItem($user ?? null); @endphp

    <section class="container cart-page-shell mt-32 mt-lg-56 mb-80 position-relative">
        <div class="cart-page-hero card-with-mask position-relative overflow-hidden">
            <div class="mask-8-white"></div>

            <div class="position-relative z-index-2 bg-white rounded-16 p-20 p-lg-32 text-center">
                <div class="d-inline-flex align-items-center checkout-options-kicker mb-12">
                    <span class="checkout-options-kicker-dot"></span>
                    <span>{{ trans('update.cart') }}</span>
                </div>
                <h1 class="font-32 font-weight-bold mb-0">{{ trans('update.cart') }}</h1>
                <p class="mt-8 font-14 font-weight-500 text-gray-500 mb-0">
                    {{ handlePrice($calculatePrices["sub_total"], true, true, false, null, true) . ' ' . trans('cart.for_items',['count' => $carts->count()]) }}
                </p>
            </div>
        </div>

        <form action="/cart/checkout" method="post" id="cartForm" class="mt-28">
            {{ csrf_field() }}

            <div class="row align-items-start cart-page-grid">
                <div class="col-12 col-lg-8">
                    @if(!empty($totalCashbackAmount))
                        @include('design_1.web.cart.overview.includes.cashback_alert')
                    @endif

                    @if(!empty($userGroup) and !empty($userGroup->discount))
                        @include('design_1.web.cart.overview.includes.user_group_discount')
                    @endif

                    <div class="cart-items-panel card-with-mask position-relative">
                        <div class="mask-8-white"></div>

                        <div class="position-relative z-index-2 bg-white rounded-16 p-16 p-lg-20">
                            @include('design_1.web.cart.overview.includes.cart_items')

                            @if($hasPhysicalProduct)
                                @include('design_1.web.cart.overview.includes.shipping_and_delivery')
                            @endif
                        </div>
                    </div>

                    @include('design_1.web.cart.overview.includes.coupon')
                </div>

                <div class="col-12 col-lg-4 mt-32 mt-lg-0">
                    <div class="cart-right-side-section">
                        <div class="js-cart-summary-container">
                            @include('design_1.web.cart.overview.includes.summary')
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </section>
@endsection

@push('scripts_bottom')
    <script>
        var cartPriceFormat = @json([
            'symbol' => currencySign($userCurrencyItem->currency ?? currency()),
            'position' => $userCurrencyItem->currency_position ?? 'left',
            'decimals' => (int) ($userCurrencyItem->currency_decimal ?? 2),
            'decimalSeparator' => ($userCurrencyItem->currency_separator ?? 'dot') == 'dot' ? '.' : ',',
            'thousandsSeparator' => ($userCurrencyItem->currency_separator ?? 'dot') == 'dot' ? ',' : '.',
        ]);
        var selectRegionDefaultVal = '';
        var selectStateLang = '{{ trans('update.choose_a_state') }}';
        var selectCityLang = '{{ trans('update.choose_a_city') }}';
        var selectDistrictLang = '{{ trans('update.all_districts') }}';
        var couponLang = '{{ trans('update.coupon') }}';
        var enterCouponLang = '{{ trans('update.please_enter_your_discount_code') }}';
        var removeCouponTitleLang = '{{ trans('update.remove_coupon_title') }}';
        var removeCouponHintLang = '{{ trans('update.remove_coupon_massage_hint') }}';
        var cancelLang = '{{ trans('public.cancel') }}';
        var removeLang = '{{ trans('public.remove') }}';
        var hasErrors = '{{ (!empty($errors) and count($errors)) ? 'true' : 'false' }}';
        var hasErrorsHintLang = '{{ trans('update.please_check_the_errors_in_the_shipping_form') }}';
    </script>

    <script src="{{ getDesign1ScriptPath("get_regions") }}"></script>
    <script src="{{ getDesign1ScriptPath("cart_page") }}"></script>
    <script src="{{ asset('js/checkout-address-autocomplete.js') }}"></script>

@endpush
