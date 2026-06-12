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
        .cart-location-header {
            display: flex; align-items: center; justify-content: space-between;
            cursor: pointer; user-select: none;
        }
        .cart-location-chevron { transition: transform .2s; }
        .cart-location-chevron.open { transform: rotate(180deg); }

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

                {{-- Bookings section --}}
                @php $bookingCarts = $carts->whereNotNull('booking_id'); @endphp
                @if($bookingCarts->isNotEmpty())
                    <div class="cart-section-label">{{ trans('update.bookings') ?? 'Bookings' }}</div>
                @endif

                {{-- ── All Cart Items ── --}}
                @include('design_1.web.cart.overview.includes.cart_items')

                {{-- Location section (above coupon) --}}
                @include('design_1.web.cart.overview.includes.location_section')

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

})(jQuery);
</script>
@endpush