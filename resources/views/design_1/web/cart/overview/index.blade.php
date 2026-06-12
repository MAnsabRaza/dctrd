@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <style>
        .cart-page-title { font-size: 26px; font-weight: 800; color: #0f172a; }
        .cart-page-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }

        /* Section label */
        .cart-section-label {
            font-size: 12px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .07em;
            padding-left: 8px; border-left: 3px solid #22c55e;
            margin-bottom: 14px;
        }

        /* Booking row card */
        .cart-booking-row {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 18px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 14px;
            position: relative;
            transition: box-shadow .2s;
        }
        .cart-booking-row:hover { box-shadow: 0 8px 28px rgba(15,23,42,.08); }
        .cart-booking-thumb {
            width: 72px; height: 72px;
            border-radius: 14px;
            overflow: hidden;
            flex-shrink: 0;
            background: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
        }
        .cart-booking-thumb img { width: 100%; height: 100%; object-fit: cover; }

        /* Generic item card (courses, products etc) */
        .cart-item-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 14px 16px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 14px;
            position: relative;
        }

        /* Remove button */
        .cart-remove-btn {
            width: 30px; height: 30px; padding: 0;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            transition: background .15s, border-color .15s;
            flex-shrink: 0;
        }
        .cart-remove-btn:hover { background: #fee2e2 !important; border-color: #fca5a5 !important; }
        .cart-remove-btn:hover .icons { color: #ef4444 !important; }

        /* Summary card */
        .cart-summary-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 20px 22px;
            position: sticky;
            top: 20px;
        }

        /* Coupon card */
        .cart-coupon-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 18px;
            margin-bottom: 14px;
        }

        /* Location card */
        .cart-location-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 18px;
            margin-bottom: 14px;
        }

        /* Address autocomplete */
        .cart-addr-suggestions {
            position: absolute; z-index: 1050;
            width: 100%; max-height: 200px; overflow-y: auto;
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 8px 24px rgba(15,23,42,.1);
            top: calc(100% + 2px); left: 0;
        }
        .cart-addr-item {
            padding: 8px 12px; font-size: 12px; cursor: pointer;
            color: #334155; border-bottom: 1px solid #f1f5f9;
            transition: background .1s;
        }
        .cart-addr-item:last-child { border-bottom: none; }
        .cart-addr-item:hover { background: #f0fdf4; color: #15803d; }

        /* Checkout btn */
        .cart-checkout-btn {
            height: 48px; border-radius: 12px;
            font-size: 15px; font-weight: 700;
            width: 100%;
        }

        /* ── User Info Card ── */
        .cart-user-info-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 18px;
            margin-bottom: 14px;
        }
        .cart-user-info-card__header {
            display: flex; align-items: center;
            justify-content: space-between;
            cursor: pointer; user-select: none;
        }
        .cart-user-info-card__title {
            display: flex; align-items: center;
            gap: 8px; font-size: 14px;
            font-weight: 700; color: #0f172a;
        }
        .cart-user-info-card__toggle { transition: transform .2s; }
        .cart-user-info-card__toggle.open { transform: rotate(180deg); }
        .cart-user-info-card__body { margin-top: 14px; }

        /* ── Payment Method Card ── */
        .cart-payment-card {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 16px 18px;
            margin-bottom: 14px;
        }
        .cart-payment-option {
            display: flex; align-items: center;
            gap: 10px; padding: 10px 12px;
            border: 1.5px solid #e2e8f0;
            border-radius: 10px; cursor: pointer;
            transition: border-color .15s, background .15s;
            margin-bottom: 8px;
        }
        .cart-payment-option:last-child { margin-bottom: 0; }
        .cart-payment-option.selected,
        .cart-payment-option:has(input:checked) {
            border-color: #2563eb;
            background: rgba(37,99,235,.04);
        }
        .cart-payment-option input[type="radio"] {
            accent-color: #2563eb; flex-shrink: 0;
        }
        .cart-payment-option__logo {
            height: 22px; object-fit: contain; flex-shrink: 0;
        }
        .cart-payment-option__label {
            font-size: 13px; font-weight: 600; color: #0f172a;
        }
        .cart-payment-option__hint {
            font-size: 11px; color: #64748b; margin-top: 1px;
        }
    </style>
@endpush

@section("content")
<div class="container py-40">

    {{-- Page heading --}}
    <div class="text-center mb-32">
        <h1 class="cart-page-title">{{ trans('public.cart_page_title') ?? 'Cart' }}</h1>
        <p class="cart-page-subtitle">
            {{ handlePrice($calculatePrices['total']) }}
            {{ trans('cart.for') ?? 'for' }}
            {{ $carts->count() }}
            {{ trans('cart.items') ?? 'items' }}
        </p>
    </div>

    <form action="/cart/checkout" method="POST" id="cartCheckoutForm">
        @csrf
        <input type="hidden" name="discount_id" id="discountIdInput">

        <div class="row">

            {{-- ════════════════════════════════
                 LEFT COLUMN — Cart Items
            ════════════════════════════════ --}}
            <div class="col-12 col-lg-8">

                {{-- Cashback alert --}}
                @include('design_1.web.cart.overview.includes.cashback_alert')

                {{-- Bookings section label --}}
                @php $bookingCarts = $carts->whereNotNull('booking_id'); @endphp
                @if($bookingCarts->isNotEmpty())
                    <div class="cart-section-label">{{ trans('update.bookings') ?? 'Bookings' }}</div>
                @endif

                {{-- ── All Cart Items ── --}}
                @include('design_1.web.cart.overview.includes.cart_items')

                {{-- ════════════════════════════════
                     USER INFORMATION CARD
                     Auto-fills from auth user profile
                ════════════════════════════════ --}}
                @auth
                <div class="cart-user-info-card" id="cartUserInfoSection">
                    <div class="cart-user-info-card__header" id="cartUserInfoToggle">
                        <div class="cart-user-info-card__title">
                            <x-iconsax-lin-profile class="icons text-primary" width="18px" height="18px"/>
                            <span>{{ trans('public.your_information') ?? 'Your Information' }}</span>
                            <span class="font-12 font-weight-400 text-gray-400">({{ trans('public.optional') ?? 'optional' }})</span>
                        </div>
                        <x-iconsax-lin-arrow-down-2 class="icons text-gray-400 cart-user-info-card__toggle"
                                                     id="cartUserInfoChevron"
                                                     width="16px" height="16px"/>
                    </div>

                    <div class="cart-user-info-card__body" id="cartUserInfoBody" style="display:none;">
                        @php $user = auth()->user(); @endphp
                        <div class="row">
                            <div class="col-12 col-md-6 mb-14">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.full_name') ?? 'Full Name' }}</label>
                                    <input type="text" name="buyer_name" class="form-control"
                                           value="{{ old('buyer_name', $user->full_name ?? '') }}"
                                           placeholder="{{ trans('update.full_name') ?? 'Full Name' }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-14">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('auth.email') ?? 'Email' }}</label>
                                    <input type="email" name="buyer_email" class="form-control"
                                           value="{{ old('buyer_email', $user->email ?? '') }}"
                                           placeholder="{{ trans('auth.email') ?? 'Email' }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-0">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.mobile') ?? 'Phone' }}</label>
                                    <input type="text" name="buyer_phone" class="form-control"
                                           value="{{ old('buyer_phone', $user->mobile ?? '') }}"
                                           placeholder="{{ trans('update.mobile') ?? 'Phone number' }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-6 mb-0">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.country') ?? 'Country' }}</label>
                                    <input type="text" name="buyer_country" class="form-control"
                                           value="{{ old('buyer_country', $user->country ?? '') }}"
                                           placeholder="{{ trans('update.country') ?? 'Country' }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth

                {{-- Location section (above coupon) --}}
                @include('design_1.web.cart.overview.includes.location_section')

                {{-- ════════════════════════════════
                     PAYMENT METHOD CARD
                ════════════════════════════════ --}}
                @if(!empty($paymentChannels) && count($paymentChannels))
                <div class="cart-payment-card">
                    <div class="font-14 font-weight-bold text-dark mb-12 d-flex align-items-center gap-8">
                        <x-iconsax-lin-card class="icons text-primary" width="18px" height="18px"/>
                        {{ trans('cart.payment_method') ?? 'Payment Method' }}
                    </div>

                    @foreach($paymentChannels as $channel)
                        <label class="cart-payment-option d-flex align-items-center">
                            <input type="radio" name="payment_method"
                                   value="{{ $channel->class_name ?? $channel->title }}"
                                   {{ $loop->first ? 'checked' : '' }}>
                            @if(!empty($channel->image))
                                <img src="{{ $channel->image }}" alt="{{ $channel->title }}"
                                     class="cart-payment-option__logo ml-4">
                            @else
                                <x-iconsax-lin-card class="icons text-gray-400 ml-4" width="22px" height="22px"/>
                            @endif
                            <div class="ml-8">
                                <div class="cart-payment-option__label">{{ $channel->title }}</div>
                                @if(!empty($channel->description))
                                    <div class="cart-payment-option__hint">{{ $channel->description }}</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                @endif

                {{-- Coupon --}}
                @include('design_1.web.cart.overview.includes.coupon')

                {{-- User group discount --}}
                @include('design_1.web.cart.overview.includes.user_group_discount')

                {{-- Shipping & delivery (physical products) --}}
                @if(!empty($hasPhysicalProduct) && $hasPhysicalProduct)
                    @include('design_1.web.cart.overview.includes.shipping_and_delivery')
                @endif

            </div>

            {{-- ════════════════════════════════
                 RIGHT COLUMN — Order Summary
            ════════════════════════════════ --}}
            <div class="col-12 col-lg-4 mt-24 mt-lg-0">
                <div class="cart-summary-card" id="orderSummaryCard">
                    <div class="font-16 font-weight-bold text-dark mb-16">
                        {{ trans('cart.order_summary') ?? 'Order Summary' }}
                    </div>

                    @include('design_1.web.cart.overview.includes.summary', [
                        'calculatePrices' => $calculatePrices,
                    ])

                    <button type="submit" class="btn btn-primary cart-checkout-btn mt-16">
                        {{ trans('cart.checkout') ?? 'Checkout' }}
                    </button>

                    <div class="d-flex align-items-center justify-content-center gap-8 mt-10">
                        <x-iconsax-lin-shield-tick class="icons text-success" width="14px" height="14px"/>
                        <span class="font-11 text-gray-400">{{ trans('cart.secure_payments') ?? 'Secure Payments Provided' }}</span>
                    </div>

                    {{-- Payment logos strip --}}
                    <div class="d-flex align-items-center justify-content-center flex-wrap gap-6 mt-12">
                        @php
                            $paymentLogos = [
                                ['src' => '/assets/default/img/payments/visa.svg',       'alt' => 'Visa'],
                                ['src' => '/assets/default/img/payments/mastercard.svg', 'alt' => 'Mastercard'],
                                ['src' => '/assets/default/img/payments/paypal.svg',     'alt' => 'PayPal'],
                                ['src' => '/assets/default/img/payments/stripe.svg',     'alt' => 'Stripe'],
                            ];
                        @endphp
                        @foreach($paymentLogos as $logo)
                            <img src="{{ $logo['src'] }}" alt="{{ $logo['alt'] }}"
                                 style="height:18px;object-fit:contain;opacity:.7;"
                                 onerror="this.style.display='none'">
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection

@push('scripts_bottom')
<script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
<script>
(function ($) {
    'use strict';

    /* ── Remove cart item ── */
    $(document).on('click', '.cart-remove-btn', function () {
        var $btn   = $(this);
        var cartId = $btn.data('cart-id');
        if (!cartId) return;

        $btn.addClass('loadingbar').prop('disabled', true);

        $.ajax({
            url: '/cart/' + cartId + '/remove',
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' }
        }).done(function (res) {
            $('#cart-item-' + cartId).fadeOut(260, function () {
                $(this).remove();
                if ($('.cart-booking-row, .cart-item-card').length === 0) {
                    location.reload();
                }
            });
            if (res && res.msg) showToast(res.status || 'success', res.title || '', res.msg);
        }).fail(function (xhr) {
            $btn.removeClass('loadingbar').prop('disabled', false);
            var err = xhr.responseJSON;
            showToast('error', err && err.title ? err.title : 'Error', err && err.msg ? err.msg : 'Could not remove item');
        });
    });

    /* ── Coupon: validate on Enter ── */
    $(document).on('keydown', '#couponCodeInput', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#couponValidateBtn').trigger('click'); }
    });

    /* ── User Info Card: toggle open/close, auto-open if data exists ── */
    (function () {
        var toggle  = document.getElementById('cartUserInfoToggle');
        var body    = document.getElementById('cartUserInfoBody');
        var chevron = document.getElementById('cartUserInfoChevron');

        if (!toggle || !body) return;

        toggle.addEventListener('click', function () {
            var isOpen = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            if (chevron) chevron.classList.toggle('open', !isOpen);
        });

        // Auto-open if any field has a pre-filled value
        var inputs = body.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value && inputs[i].value.trim()) {
                body.style.display = 'block';
                if (chevron) chevron.classList.add('open');
                break;
            }
        }
    })();

    /* ── Payment option visual selection ── */
    $(document).on('change', '.cart-payment-option input[type="radio"]', function () {
        $('.cart-payment-option').removeClass('selected');
        $(this).closest('.cart-payment-option').addClass('selected');
    });
    // Mark first as selected on load
    $('.cart-payment-option input[type="radio"]:checked').closest('.cart-payment-option').addClass('selected');

})(jQuery);
</script>
@endpush