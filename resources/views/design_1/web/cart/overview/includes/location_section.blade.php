{{--
    resources/views/design_1/web/cart/overview/partials/_location_section.blade.php

    Renders a collapsible location card ABOVE the coupon section in cart.
    Fields: address_line, city, state, country, postal_code

    Include in cart overview index.blade.php ABOVE the coupon row:
        @include('design_1.web.cart.overview.partials._location_section')
--}}

@push('styles_top')
<style>
.cart-location-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 16px 20px;
    margin-bottom: 16px;
}
.cart-location-card__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    cursor: pointer;
    user-select: none;
}
.cart-location-card__title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}
.cart-location-card__toggle-icon {
    transition: transform .2s;
}
.cart-location-card__toggle-icon.open {
    transform: rotate(180deg);
}
.cart-location-card__body {
    margin-top: 14px;
}

/* Address suggestion dropdown */
.cart-addr-suggestions {
    position: absolute;
    z-index: 1050;
    width: 100%;
    max-height: 200px;
    overflow-y: auto;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    box-shadow: 0 8px 24px rgba(15,23,42,.1);
    top: calc(100% + 2px);
    left: 0;
}
.cart-addr-suggestion-item {
    padding: 8px 12px;
    font-size: 12px;
    cursor: pointer;
    color: #334155;
    border-bottom: 1px solid #f1f5f9;
    transition: background .12s;
}
.cart-addr-suggestion-item:last-child { border-bottom: none; }
.cart-addr-suggestion-item:hover { background: #f0fdf4; color: #15803d; }
</style>
@endpush

<div class="cart-location-card" id="cartLocationSection">

    {{-- Header / toggle --}}
    <div class="cart-location-card__header" id="cartLocationToggle">
        <div class="cart-location-card__title">
            <x-iconsax-lin-location class="icons text-primary" width="18px" height="18px"/>
            <span>{{ trans('update.delivery_address') ?? 'Delivery / Location' }}</span>
            <span class="font-12 font-weight-400 text-gray-400">({{ trans('public.optional') ?? 'optional' }})</span>
        </div>
        <x-iconsax-lin-arrow-down-2 class="icons text-gray-400 cart-location-card__toggle-icon"
                                     id="cartLocationChevron"
                                     width="16px" height="16px"/>
    </div>

    {{-- Body --}}
    <div class="cart-location-card__body" id="cartLocationBody" style="display:none;">

        <div class="row">

            {{-- Address Line (with autocomplete) --}}
            <div class="col-12 position-relative mb-14">
                <div class="form-group mb-0 position-relative">
                    <label class="form-group-label font-12">
                        {{ trans('update.address') ?? 'Address' }}
                    </label>
                    <input type="text"
                           name="address_line"
                           id="cartAddressLine"
                           class="form-control"
                           autocomplete="off"
                           placeholder="{{ trans('update.address') ?? 'Start typing address...' }}"
                           value="{{ old('address_line', auth()->user()->address ?? '') }}">
                    <div class="cart-addr-suggestions d-none" id="cartAddrSuggestions"></div>
                </div>
            </div>

            {{-- City --}}
            <div class="col-12 col-md-6 mb-14">
                <div class="form-group mb-0">
                    <label class="form-group-label font-12">{{ trans('update.city') ?? 'City' }}</label>
                    <input type="text"
                           name="city"
                           id="cartCity"
                           class="form-control"
                           placeholder="{{ trans('update.city') ?? 'City' }}"
                           value="{{ old('city', auth()->user()->city ?? '') }}">
                </div>
            </div>

            {{-- State / Province --}}
            <div class="col-12 col-md-6 mb-14">
                <div class="form-group mb-0">
                    <label class="form-group-label font-12">{{ trans('update.state') ?? 'State / Province' }}</label>
                    <input type="text"
                           name="state"
                           id="cartState"
                           class="form-control"
                           placeholder="{{ trans('update.state') ?? 'State' }}"
                           value="{{ old('state', auth()->user()->state ?? '') }}">
                </div>
            </div>

            {{-- Country --}}
            <div class="col-12 col-md-6 mb-14">
                <div class="form-group mb-0">
                    <label class="form-group-label font-12">{{ trans('update.country') ?? 'Country' }}</label>
                    <input type="text"
                           name="country"
                           id="cartCountry"
                           class="form-control"
                           placeholder="{{ trans('update.country') ?? 'Country' }}"
                           value="{{ old('country', auth()->user()->country ?? '') }}">
                </div>
            </div>

            {{-- Postal Code --}}
            <div class="col-12 col-md-6 mb-0">
                <div class="form-group mb-0">
                    <label class="form-group-label font-12">{{ trans('update.postal_code') ?? 'Postal Code' }}</label>
                    <input type="text"
                           name="postal_code"
                           id="cartPostalCode"
                           class="form-control"
                           placeholder="{{ trans('update.postal_code') ?? 'Postal Code' }}"
                           value="{{ old('postal_code', auth()->user()->postal_code ?? '') }}">
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts_bottom')
<script>
(function () {
    /* ── Toggle open/close ── */
    var toggle  = document.getElementById('cartLocationToggle');
    var body    = document.getElementById('cartLocationBody');
    var chevron = document.getElementById('cartLocationChevron');

    if (toggle && body) {
        toggle.addEventListener('click', function () {
            var isOpen = body.style.display !== 'none';
            body.style.display = isOpen ? 'none' : 'block';
            if (chevron) chevron.classList.toggle('open', !isOpen);
        });

        // Auto-open if any field already has a value (e.g. old() / user data)
        var inputs = body.querySelectorAll('input');
        for (var i = 0; i < inputs.length; i++) {
            if (inputs[i].value.trim()) {
                body.style.display = 'block';
                if (chevron) chevron.classList.add('open');
                break;
            }
        }
    }

    /* ── Address autocomplete ── */
    var addrInput   = document.getElementById('cartAddressLine');
    var suggestions = document.getElementById('cartAddrSuggestions');

    if (!addrInput || !suggestions) return;

    var debounceTimer;

    function debounce(fn, wait) {
        return function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fn, wait);
        };
    }

    function renderSuggestions(items) {
        suggestions.innerHTML = '';
        if (!items || !items.length) {
            suggestions.classList.add('d-none');
            return;
        }
        items.forEach(function (item) {
            var el = document.createElement('div');
            el.className = 'cart-addr-suggestion-item';
            el.textContent = item.display_name;
            el.addEventListener('click', function () {
                addrInput.value = item.display_name || addrInput.value;

                var city    = document.getElementById('cartCity');
                var state   = document.getElementById('cartState');
                var country = document.getElementById('cartCountry');
                var postal  = document.getElementById('cartPostalCode');

                if (city    && item.city)        city.value    = item.city;
                if (state   && item.state)       state.value   = item.state;
                if (country && item.country)     country.value = item.country;
                if (postal  && item.postal_code) postal.value  = item.postal_code;

                suggestions.classList.add('d-none');
            });
            suggestions.appendChild(el);
        });
        suggestions.classList.remove('d-none');
    }

    var doSuggest = debounce(function () {
        var q = addrInput.value.trim();
        if (q.length < 3) { renderSuggestions([]); return; }
        fetch('/location/suggestions?q=' + encodeURIComponent(q))
            .then(function (r) { return r.json(); })
            .then(renderSuggestions)
            .catch(function () { renderSuggestions([]); });
    }, 400);

    addrInput.addEventListener('input', doSuggest);
    addrInput.addEventListener('keyup', doSuggest);

    document.addEventListener('click', function (e) {
        if (e.target !== addrInput && !suggestions.contains(e.target)) {
            suggestions.classList.add('d-none');
        }
    });
})();
</script>
@endpush