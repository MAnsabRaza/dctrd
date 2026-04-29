@extends(getTemplate().'.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="cart-banner position-relative text-center">
        <h1 class="font-30 text-white font-weight-bold">{{ trans('cart.checkout') }}</h1>
        <span class="payment-hint font-20 text-white d-block">{{ handlePrice($total) . ' ' .  trans('cart.for_items',['count' => $count]) }}</span>
    </section>

    <section class="container mt-45">

        @if(!empty($totalCashbackAmount))
            <div class="d-flex align-items-center mb-25 p-15 success-transparent-alert">
                <div class="success-transparent-alert__icon d-flex align-items-center justify-content-center">
                    <i data-feather="credit-card" width="18" height="18" class=""></i>
                </div>

                <div class="ml-10">
                    <div class="font-14 font-weight-bold ">{{ trans('update.get_cashback') }}</div>
                    <div class="font-12 ">{{ trans('update.by_purchasing_this_cart_you_will_get_amount_as_cashback',['amount' => handlePrice($totalCashbackAmount)]) }}</div>
                </div>
            </div>
        @endif

        @php
            $isMultiCurrency = !empty(getFinancialCurrencySettings('multi_currency'));
            $userCurrency = currency();
            $invalidChannels = [];
        @endphp

        <h2 class="section-title">{{ trans('financial.select_a_payment_gateway') }}</h2>

        <form action="/payments/payment-request" method="post" class=" mt-25">
            {{ csrf_field() }}
            <input type="hidden" name="order_id" value="{{ $order->id }}">

            <div class="row">
                @if(!empty($paymentChannels))
                    @foreach($paymentChannels as $paymentChannel)
                        @if(!$isMultiCurrency or (!empty($paymentChannel->currencies) and in_array($userCurrency, $paymentChannel->currencies)))
                            <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                                <input type="radio" name="gateway" id="{{ $paymentChannel->title }}" data-class="{{ $paymentChannel->class_name }}" value="{{ $paymentChannel->id }}">
                                <label for="{{ $paymentChannel->title }}" class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                    <img src="{{ $paymentChannel->image }}" width="120" height="60" alt="">

                                    <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                        {{ trans('financial.pay_via') }}
                                        <span class="font-weight-bold font-14">{{ $paymentChannel->title }}</span>
                                    </p>
                                </label>
                            </div>
                        @else
                            @php
                                $invalidChannels[] = $paymentChannel;
                            @endphp
                        @endif
                    @endforeach
                @endif

                <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                    <input type="radio" @if(empty($userCharge) or ($total > $userCharge)) disabled @endif name="gateway" id="offline" value="credit">
                    <label for="offline" class="rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                        <img src="/assets/default/img/activity/pay.svg" width="120" height="60" alt="">

                        <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                            {{ trans('financial.account') }}
                            <span class="font-weight-bold">{{ trans('financial.charge') }}</span>
                        </p>

                        <span class="mt-5">{{ handlePrice($userCharge) }}</span>
                    </label>
                </div>
            </div>

            @if(!empty($invalidChannels) and empty(getFinancialSettings("hide_disabled_payment_gateways")))
                <div class="d-flex align-items-center mt-30 rounded-lg border p-15">
                    <div class="size-40 d-flex-center rounded-circle bg-gray200">
                        <i data-feather="info" class="text-gray" width="20" height="20"></i>
                    </div>
                    <div class="ml-5">
                        <h4 class="font-14 font-weight-bold text-gray">{{ trans('update.disabled_payment_gateways') }}</h4>
                        <p class="font-12 text-gray">{{ trans('update.disabled_payment_gateways_hint') }}</p>
                    </div>
                </div>

                <div class="row mt-20">
                    @foreach($invalidChannels as $invalidChannel)
                        <div class="col-6 col-lg-4 mb-40 charge-account-radio">
                            <div class="disabled-payment-channel bg-white border rounded-sm p-20 p-lg-45 d-flex flex-column align-items-center justify-content-center">
                                <img src="{{ $invalidChannel->image }}" width="120" height="60" alt="">

                                <p class="mt-30 mt-lg-50 font-weight-500 text-dark-blue">
                                    {{ trans('financial.pay_via') }}
                                    <span class="font-weight-bold font-14">{{ $invalidChannel->title }}</span>
                                </p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif


            <div class="d-flex align-items-center justify-content-between mt-45">
                <span class="font-16 font-weight-500 text-gray">{{ trans('financial.total_amount') }} {{ handlePrice($total) }}</span>
                <button type="button" id="paymentSubmit" disabled class="btn btn-sm btn-primary">{{ trans('public.start_payment') }}</button>
            </div>
        </form>

        @if(!empty($razorpay) and $razorpay)
            <form action="/payments/verify/Razorpay" method="get">
                <input type="hidden" name="order_id" value="{{ $order->id }}">

                <script src="https://checkout.razorpay.com/v1/checkout.js"
                        data-key="{{ getRazorpayApiKey()['api_key'] }}"
                        data-amount="{{ (int)($order->total_amount * 100) }}"
                        data-buttontext="product_price"
                        data-description="Rozerpay"
                        data-currency="{{ currency() }}"
                        data-image="{{ $generalSettings['logo'] }}"
                        data-prefill.name="{{ $order->user->full_name }}"
                        data-prefill.email="{{ $order->user->email }}"
                        data-theme.color="#43d477">
                </script>
            </form>
        @endif

        {{-- Cross & UpSelling --}}
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
                    'checkout',
                );

                $up_selling = $upSellingService->getUpsellItems($owner_id, $cart_model_id, $cart_model_type,'checkout');
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
    </section>

@endsection

@push('scripts_bottom')
    <script src="/assets/default/js/parts/payment.min.js"></script>
@endpush
