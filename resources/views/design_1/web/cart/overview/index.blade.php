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

        /* Generic item card */
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
        .cart-location-card__header {
            display: flex; align-items: center;
            justify-content: space-between;
            cursor: pointer; user-select: none;
        }
        .cart-location-card__title {
            display: flex; align-items: center;
            gap: 8px; font-size: 14px; font-weight: 700; color: #0f172a;
        }
        .cart-location-card__toggle { transition: transform .2s; }
        .cart-location-card__toggle.open { transform: rotate(180deg); }
        .cart-location-card__body { margin-top: 14px; }

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
        }
        .cart-addr-item:hover { background: #f0fdf4; color: #15803d; }

        .cart-checkout-btn {
            height: 48px; border-radius: 12px;
            font-size: 15px; font-weight: 700;
            width: 100%;
        }

        /* ── Booking modules — plain card (no green border) ── */
        .bmod-wrap {
            margin-top: 12px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            overflow: hidden;
        }
        .bmod-row {
            display: flex; flex-wrap: wrap;
        }
        .bmod-col {
            flex: 1 1 160px; min-width: 0;
            padding: 10px 14px;
            border-right: 1px solid #e2e8f0;
        }
        .bmod-col:last-child { border-right: none; }
        .bmod-label {
            font-size: 11px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .05em;
            margin-bottom: 5px;
            display: flex; align-items: center; gap: 4px;
        }
        .bmod-value {
            font-size: 13px; font-weight: 600; color: #0f172a;
            display: flex; align-items: center; gap: 5px;
        }
        .bmod-date-input {
            font-size: 13px; font-weight: 600; color: #0f172a;
            border: 1px solid #e2e8f0; border-radius: 6px;
            padding: 3px 6px; background: #fff;
            cursor: pointer; width: 100%; max-width: 148px;
        }
        .bmod-date-input:focus { outline: none; border-color: #94a3b8; }
        .bmod-nights-badge {
            display: inline-flex; align-items: center;
            margin-top: 5px; padding: 2px 8px;
            border-radius: 999px;
            background: rgba(37,99,235,.08);
            font-size: 10px; font-weight: 600; color: #2563eb;
        }
        .bmod-staff-name {
            font-size: 13px; font-weight: 600; color: #0f172a;
            display: flex; align-items: center; gap-6px;
        }

        @media (max-width: 640px) {
            .bmod-col { border-right: none; border-bottom: 1px solid #e2e8f0; flex: 1 1 100%; }
            .bmod-col:last-child { border-bottom: none; }
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

            {{-- ════════ LEFT COLUMN ════════ --}}
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
                     DELIVERY ADDRESS — auto-filled from user DB
                ════════════════════════════════ --}}
                @auth
                @php $authUser = auth()->user(); @endphp
                <div class="cart-location-card" id="cartLocationSection">
                    <div class="cart-location-card__header" id="cartLocationToggle">
                        <div class="cart-location-card__title">
                            <x-iconsax-lin-location class="icons text-primary" width="18px" height="18px"/>
                            <span>{{ trans('update.delivery_address') ?? 'Delivery Address' }}</span>
                            <span class="font-12 font-weight-400 text-gray-400">({{ trans('public.optional') ?? 'optional' }})</span>
                        </div>
                        <x-iconsax-lin-arrow-down-2 class="icons text-gray-400 cart-location-card__toggle"
                                                     id="cartLocationChevron"
                                                     width="16px" height="16px"/>
                    </div>

                    <div class="cart-location-card__body" id="cartLocationBody" style="display:none;">
                        <div class="row">
                            {{-- Address Line --}}
                            <div class="col-12 mb-14 position-relative">
                                <div class="form-group mb-0 position-relative">
                                    <label class="form-group-label font-12">{{ trans('update.address') ?? 'Address' }}</label>
                                    <input type="text" name="address_line" id="cartAddressLine"
                                           class="form-control" autocomplete="off"
                                           placeholder="{{ trans('update.address') ?? 'Start typing address...' }}"
                                           value="{{ old('address_line', $authUser->address_line ?? $authUser->address ?? '') }}">
                                    <div class="cart-addr-suggestions d-none" id="cartAddrSuggestions"></div>
                                </div>
                            </div>

                            {{-- City --}}
                            <div class="col-12 col-md-6 mb-14">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.city') ?? 'City' }}</label>
                                    <input type="text" name="city" id="cartCity" class="form-control"
                                           placeholder="{{ trans('update.city') ?? 'City' }}"
                                           value="{{ old('city', $authUser->city ?? '') }}">
                                </div>
                            </div>

                            {{-- State --}}
                            <div class="col-12 col-md-6 mb-14">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.state') ?? 'State / Province' }}</label>
                                    <input type="text" name="state" id="cartState" class="form-control"
                                           placeholder="{{ trans('update.state') ?? 'State' }}"
                                           value="{{ old('state', $authUser->state ?? '') }}">
                                </div>
                            </div>

                            {{-- Country --}}
                            <div class="col-12 col-md-6 mb-14">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.country') ?? 'Country' }}</label>
                                    <input type="text" name="country" id="cartCountry" class="form-control"
                                           placeholder="{{ trans('update.country') ?? 'Country' }}"
                                           value="{{ old('country', $authUser->country 
                                </div>
                            </div>

                            {{-- Postal Code --}}
                            <div class="col-12 col-md-6 mb-0">
                                <div class="form-group mb-0">
                                    <label class="form-group-label font-12">{{ trans('update.postal_code') ?? 'Postal Code' }}</label>
                                    <input type="text" name="postal_code" id="cartPostalCode" class="form-control"
                                           placeholder="{{ trans('update.postal_code') ?? 'Postal Code' }}"
                                           value="{{ old('postal_code', $authUser->postal_code ?? '') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth

                {{-- Coupon --}}
                @include('design_1.web.cart.overview.includes.coupon')

                {{-- User group discount --}}
                @include('design_1.web.cart.overview.includes.user_group_discount')

                {{-- Shipping & delivery (physical products) --}}
                @if(!empty($hasPhysicalProduct) && $hasPhysicalProduct)
                    @include('design_1.web.cart.overview.includes.shipping_and_delivery')
                @endif

            </div>

            {{-- ════════ RIGHT COLUMN ════════ --}}
            <div class="col-12 col-lg-4 mt-24 mt-lg-0">
                <div class="cart-summary-card" id="orderSummaryCard">
                    <div class="font-16 font-weight-bold text-dark mb-16">
                        {{ trans('cart.order_summary') ?? 'Order Summary' }}
                    </div>

                    @include('design_1.web.cart.overview.includes.summary', [
                        'calculatePrices' => $calculatePrices,
                    ])

                    {{-- Single checkout button --}}
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

    /* ── Delivery Address toggle — auto-open if user has data ── */
    (function () {
        var toggle  = document.getElementById('cartLocationToggle');
        var body    = document.getElementById('cartLocationBody');
        var chevron = document.getElementById('cartLocationChevron');
        if (!toggle || !body) return;

        toggle.addEventListener('click', function () {
            var isOpen = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            if (chevron) chevron.classList.toggle('open', !isOpen);
        });

        // Auto-open if any field pre-filled
        var inputs = body.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value && inputs[i].value.trim()) {
                body.style.display = 'block';
                if (chevron) chevron.classList.add('open');
                break;
            }
        }

        /* Address autocomplete */
        var addrInput   = document.getElementById('cartAddressLine');
        var suggestions = document.getElementById('cartAddrSuggestions');
        if (!addrInput || !suggestions) return;

        var debTimer;
        addrInput.addEventListener('input', function () {
            clearTimeout(debTimer);
            debTimer = setTimeout(function () {
                var q = addrInput.value.trim();
                if (q.length < 3) { suggestions.classList.add('d-none'); suggestions.innerHTML = ''; return; }
                fetch('/location/suggestions?q=' + encodeURIComponent(q))
                    .then(function (r) { return r.json(); })
                    .then(function (items) {
                        suggestions.innerHTML = '';
                        if (!items || !items.length) { suggestions.classList.add('d-none'); return; }
                        items.forEach(function (item) {
                            var el = document.createElement('div');
                            el.className = 'cart-addr-item';
                            el.textContent = item.display_name;
                            el.addEventListener('click', function () {
                                addrInput.value = item.display_name || addrInput.value;
                                var city = document.getElementById('cartCity');
                                var state = document.getElementById('cartState');
                                var country = document.getElementById('cartCountry');
                                var postal = document.getElementById('cartPostalCode');
                                if (city && item.city) city.value = item.city;
                                if (state && item.state) state.value = item.state;
                                if (country && item.country) country.value = item.country;
                                if (postal && item.postal_code) postal.value = item.postal_code;
                                suggestions.classList.add('d-none');
                            });
                            suggestions.appendChild(el);
                        });
                        suggestions.classList.remove('d-none');
                    })
                    .catch(function () { suggestions.classList.add('d-none'); });
            }, 400);
        });

        document.addEventListener('click', function (e) {
            if (e.target !== addrInput && !suggestions.contains(e.target)) {
                suggestions.classList.add('d-none');
            }
        });
    })();

    /* ── Nights counter for booking date modules ── */
    $(document).on('change', '.bmod-cin, .bmod-cout', function () {
        var $wrap = $(this).closest('[data-item-key]');
        var key   = $wrap.data('item-key');
        var inV   = $wrap.find('.bmod-cin').val();
        var outV  = $wrap.find('.bmod-cout').val();
        var $badge = $wrap.find('.bmod-nights-badge');
        if (!inV || !outV) { $badge.text('0 nights'); return; }
        var inD = new Date(inV), outD = new Date(outV);
        if (outD <= inD) {
            var n = new Date(inD); n.setDate(n.getDate() + 1);
            $wrap.find('.bmod-cout').val(n.getFullYear() + '-' +
                String(n.getMonth()+1).padStart(2,'0') + '-' +
                String(n.getDate()).padStart(2,'0'));
            outD = n;
        }
        var nights = Math.max(0, Math.ceil((outD - inD) / 86400000));
        $badge.text(nights + ' nights');
    });

})(jQuery);
</script>
@endpush