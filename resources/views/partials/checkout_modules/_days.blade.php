{{-- resources/views/partials/checkout_modules/_days.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    $priceRule = $module->price_rule ?? [];
    $perDayAmount = $priceRule['amount'] ?? 0;
@endphp

<div
    class="checkout-module-card checkout-module-card--days"
    id="checkout-module-days-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="per_day"
    data-price-amount="{{ $perDayAmount }}"
>
    <div class="d-flex align-items-start justify-content-between">
        <div>
            <div class="font-13 font-weight-bold text-dark">
                {{ $module->translated_label }}
                @if($module->is_required)
                    <span class="text-danger">*</span>
                @endif
            </div>
            @if($module->translated_help_text)
                <p class="checkout-module-helper mb-0 mt-4">{{ $module->translated_help_text }}</p>
            @endif
        </div>

        @if($perDayAmount)
            <span class="checkout-module-price">{{ handlePrice($perDayAmount) }}/day</span>
        @endif
    </div>

    <div class="checkout-module-grid checkout-module-grid--two mt-12">
        <div class="form-group mb-0">
            <label class="form-group-label">{{ trans('checkout.check_in') }}</label>
            <input
                type="date"
                name="{{ $prefix }}[check_in]"
                class="form-control checkout-date-input"
                id="checkout_days_check_in_{{ $itemKey }}"
                value="{{ old('checkout_modules.' . $itemKey . '.' . $module->name . '.check_in') }}"
                data-min-date="{{ now()->format('Y-m-d') }}"
                required
            >
        </div>

        <div class="form-group mb-0">
            <label class="form-group-label">{{ trans('checkout.check_out') }}</label>
            <input
                type="date"
                name="{{ $prefix }}[check_out]"
                class="form-control checkout-date-input"
                id="checkout_days_check_out_{{ $itemKey }}"
                value="{{ old('checkout_modules.' . $itemKey . '.' . $module->name . '.check_out') }}"
                required
            >
        </div>
    </div>

    <div class="checkout-module-meta mt-10">
        <span class="checkout-module-badge" id="checkout_days_nights_{{ $itemKey }}">0 {{ trans('checkout.nights') }}</span>
    </div>

    @error('checkout_modules.' . $itemKey . '.days.check_in')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('checkout_modules.' . $itemKey . '.days.check_out')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        (function ($) {
            function updateNightsCount{{ $itemKey }}() {
                var checkIn = $('#checkout_days_check_in_{{ $itemKey }}').val();
                var checkOut = $('#checkout_days_check_out_{{ $itemKey }}').val();
                var $count = $('#checkout_days_nights_{{ $itemKey }}');

                if (!checkIn || !checkOut) {
                    $count.text('0 {{ trans("checkout.nights") }}');
                    return;
                }

                var inDate = new Date(checkIn);
                var outDate = new Date(checkOut);
                var nights = Math.max(0, Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24)));

                $count.text(nights + ' {{ trans("checkout.nights") }}');
                $(document).trigger('checkout:priceUpdate');
            }

            $(document).on('change', '#checkout_days_check_in_{{ $itemKey }}, #checkout_days_check_out_{{ $itemKey }}', updateNightsCount{{ $itemKey }});
            $(document).ready(updateNightsCount{{ $itemKey }});
        })(jQuery);
    </script>
@endpush
