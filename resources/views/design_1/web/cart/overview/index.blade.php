@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <style>
        .cart-page-title { font-size: 26px; font-weight: 800; color: #0f172a; }
        .cart-page-subtitle { font-size: 13px; color: #64748b; margin-top: 4px; }

        .cart-section-label {
            font-size: 12px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .07em;
            padding-left: 8px; border-left: 3px solid #22c55e;
            margin-bottom: 14px;
        }

        /* Booking row card */
        .cart-booking-row {
            background: #fff;
            border: 1px solid #dbe4f0;
            border-radius: 18px;
            padding: 14px;
            display: flex;
            align-items: flex-start;
            gap: 14px;
            margin-bottom: 14px;
            position: relative;
            box-shadow: 0 1px 0 rgba(15, 23, 42, .02);
        }
        .cart-booking-row:hover { box-shadow: 0 10px 30px rgba(15,23,42,.06); }
        .cart-booking-thumb {
            width: 70px; height: 70px;
            border-radius: 16px; overflow: hidden; flex-shrink: 0;
            background: #eefaf1;
            display: flex; align-items: center; justify-content: center;
        }
        .cart-booking-thumb img { width: 100%; height: 100%; object-fit: cover; }

        /* Generic item card */
        .cart-item-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 14px 16px; display: flex; align-items: flex-start;
            gap: 14px; margin-bottom: 14px; position: relative;
        }

        /* Remove button */
        .cart-remove-btn {
            width: 30px; height: 30px; padding: 0; border-radius: 50%;
            border: 1px solid #e2e8f0; background: #fff;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: background .15s, border-color .15s; flex-shrink: 0;
        }
        .cart-remove-btn:hover { background: #fee2e2 !important; border-color: #fca5a5 !important; }
        .cart-remove-btn:hover .icons { color: #ef4444 !important; }

        /* Summary card */
        .cart-summary-card {
            background: #fff; border: 1px solid #dbe4f0; border-radius: 22px;
            padding: 20px 22px; position: sticky; top: 20px;
            box-shadow: 0 1px 0 rgba(15, 23, 42, .02);
        }

        /* Coupon card */
        .cart-coupon-card {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 16px; padding: 16px 18px; margin-bottom: 14px;
        }

        .cart-checkout-btn {
            height: 48px; border-radius: 12px; font-size: 15px;
            font-weight: 700; width: 100%;
            box-shadow: 0 12px 24px rgba(17, 123, 255, .18);
        }

        /* Cancellation policy error highlight (guard state) */
        .booking-cancellation-card.cp-error {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, .12);
        }

        {{--
            NOTE: Location card styles (.cart-location-card*, .cart-addr-*)
            and booking module card styles (.booking-info-*,
            .booking-cancellation-*, .bmod-*) used to be duplicated here.
            They now live in their own single-source partials:
              - location_section.blade.php          (pushes its own styles)
              - _checkout_modules_styles.blade.php   (pushed once from
                checkout_item_modules.blade.php)
            so there is exactly one copy of each style block on the page
            no matter how many times the partials are included.
        --}}

        @media (max-width: 991px) {
            .booking-info-grid { grid-template-columns: 1fr; }
        }
    </style>
@endpush

@section("content")
<div class="container py-40">

    {{-- Page heading --}}
    <div class="text-center mb-32 mt-32">
        <h1 class="cart-page-title">{{ trans('public.cart_page_title') ?? 'Cart' }}</h1>
        <p class="cart-page-subtitle">
            {{ handlePrice($calculatePrices['total']) }}
            {{ trans('cart.for') ?? 'for' }}
            {{ $carts->count() }}
            {{ trans('cart.items') ?? 'items' }}
        </p>
    </div>

    {{-- Currency symbol hidden (JS ke liye) --}}
    <span id="cartCurrencySymbol"
          data-symbol="{{ getCurrencySymbol() ?? '$' }}"
          data-decimals="{{ getCurrencyDecimals() ?? 2 }}"
          style="display:none;"></span>

    <form action="/cart/checkout" method="POST" id="cartCheckoutForm">
        @csrf
        <input type="hidden" name="discount_id" id="discountIdInput">

        <div class="row">

            {{-- ════════ LEFT COLUMN ════════ --}}
            <div class="col-12 col-lg-8">

                {{-- Cashback alert --}}
                @include('design_1.web.cart.overview.includes.cashback_alert')

                {{-- Bookings section label --}}
                @php
                    $bookingCarts = $carts->filter(fn ($cart) => !empty($cart->booking_order_id) || !empty($cart->booking_id));
                @endphp
                @if($bookingCarts->isNotEmpty())
                    <div class="cart-section-label">{{ trans('update.bookings') ?? 'Bookings' }}</div>
                @endif

                {{-- All Cart Items (each booking item internally includes
                     checkout_item_modules.blade.php for its modules) --}}
                @include('design_1.web.cart.overview.includes.cart_items')

                {{--
                    Delivery Address — single source of truth.
                    Previously this whole block (address_line/city/state/
                    country/postal_code + CSS + JS) was hardcoded inline
                    here AND duplicated again in location_section.blade.php.
                    Now there is exactly one copy, included here.
                --}}
                @auth
                    @include('design_1.web.cart.overview.includes.location_section')
                @endauth

                {{-- Coupon --}}
                @include('design_1.web.cart.overview.includes.coupon')

                {{-- User group discount --}}
                @include('design_1.web.cart.overview.includes.user_group_discount')

                {{-- Shipping & delivery --}}
                @if(!empty($hasPhysicalProduct) && $hasPhysicalProduct)
                    @include('design_1.web.cart.overview.includes.shipping_and_delivery')
                @endif

            </div>

            {{-- ════════ RIGHT COLUMN ════════ --}}
            <div class="col-12 col-lg-4 mt-24 mt-lg-0">
                @include('design_1.web.cart.overview.includes.summary', [
                    'calculatePrices' => $calculatePrices,
                ])
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

    /* ════════════════════════════════════════
       CURRENCY HELPER
    ════════════════════════════════════════ */
    var $currEl = $('#cartCurrencySymbol');
    var curSym  = $currEl.data('symbol')   || '$';
    var curDec  = parseInt($currEl.data('decimals') || 2);

    function formatMoney(amount) {
        return curSym + parseFloat(amount).toFixed(curDec);
    }

    /* ════════════════════════════════════════
       MODULE PRICE CALCULATOR
       Listens to the single 'checkout:priceUpdate' event fired by
       checkout_item_modules.blade.php's shared script (days, hours,
       persons_children, extra_services all trigger it). This replaces
       the old recalcExtras() which ONLY summed extra service checkboxes
       and silently ignored per-day / per-hour / per-person pricing.
    ════════════════════════════════════════ */
    var baseTotal = parseFloat($('.js-cart-total').data('amount')) || 0;

    function calcModuleExtra($card) {
        var priceType   = $card.data('price-type');
        var priceAmount = parseFloat($card.data('price-amount')) || 0;

        switch (priceType) {
            case 'per_day': {
                var nightsText = $card.find('[id^="bmod_nights_"]').text();
                var nights = parseInt(nightsText, 10) || 0;
                return nights * priceAmount;
            }
            case 'per_hour': {
                var hasSlot = $card.find('.bmod-time-select').val();
                return hasSlot ? priceAmount : 0;
            }
            case 'per_person': {
                var adults = parseInt($card.find('[id^="pax_adults_"]').val(), 10) || 0;
                return adults * priceAmount;
            }
            case 'additive': {
                var sum = 0;
                $card.find('.bmod-extra-chk:checked').each(function () {
                    sum += parseFloat($(this).data('price')) || 0;
                });
                return sum;
            }
            default:
                return 0;
        }
    }

    function recalcModuleExtras() {
        var extrasTotal = 0;

        $('[data-module-name]').each(function () {
            extrasTotal += calcModuleExtra($(this));
        });

        $('.js-cart-extras').text(formatMoney(extrasTotal));

        var newTotal = baseTotal + extrasTotal;
        $('.js-cart-total').text(formatMoney(newTotal));
    }

    $(document).on('checkout:priceUpdate', recalcModuleExtras);

    // Run once on load (covers old()/pre-filled values)
    recalcModuleExtras();

    /* ════════════════════════════════════════
       REMOVE CART ITEM
    ════════════════════════════════════════ */
    $(document).on('click', '.cart-remove-btn', function () {
        var $btn   = $(this);
        var cartId = $btn.data('cart-id');
        if (!cartId) return;

        $btn.addClass('loadingbar').prop('disabled', true);

        $.ajax({
            url: '/cart/' + cartId + '/delete',
            method: 'GET'
        }).done(function (res) {
            $('#cart-item-' + cartId).fadeOut(260, function () {
                $(this).remove();

                // Baqi items na rahe toh reload
                if ($('.cart-booking-row, .cart-item-card').length === 0) {
                    location.reload();
                }

                // baseTotal update karo (removed item ki price minus)
                baseTotal = parseFloat($('.js-cart-total').data('amount')) || 0;
                recalcModuleExtras();
            });
            if (res && res.msg) showToast(res.status || 'success', res.title || '', res.msg);
        }).fail(function (xhr) {
            $btn.removeClass('loadingbar').prop('disabled', false);
            var err = xhr.responseJSON;
            showToast('error',
                err && err.title ? err.title : 'Error',
                err && err.msg   ? err.msg   : 'Could not remove item');
        });
    });

    /* ════════════════════════════════════════
       COUPON: validate on Enter
    ════════════════════════════════════════ */
    $(document).on('keydown', '#couponCodeInput', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); $('#couponValidateBtn').trigger('click'); }
    });

    /* ════════════════════════════════════════
       CHECKOUT BUTTON
       Ab form submit hone se pehle cancellation-policy
       checkboxes (jo required hain) validate hoti hain.
       Agar koi required policy unchecked hai to submit
       ruk jayega, us card ko highlight/scroll karke
       user ko dikha diya jayega.
    ════════════════════════════════════════ */
    $(document).on('click', '.js-cart-checkout', function (e) {
        e.preventDefault();

        if ($(this).data('access-blocked')) {
            return;
        }

        // Guard: cancellation_policy module (agar page par present hai)
        if (typeof window.validateBookingCheckoutModules === 'function') {
            if (!window.validateBookingCheckoutModules()) {
                return; // block submit — checkout nahi hoga
            }
        }

        var form = document.getElementById('cartCheckoutForm');
        if (form) { form.submit(); }
    });

})(jQuery);
</script>
@endpush
