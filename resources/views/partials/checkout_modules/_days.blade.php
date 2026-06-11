{{-- resources/views/partials/checkout_modules/_days.blade.php --}}
@php
    $itemKey = $itemId ?? '0';
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    $priceRule = $module->price_rule ?? [];
    $perDayAmount = $priceRule['amount'] ?? 0;
    $oldCheckIn = old('checkout_modules.' . $itemKey . '.' . $module->name . '.check_in');
    $oldCheckOut = old('checkout_modules.' . $itemKey . '.' . $module->name . '.check_out');
@endphp

<div
    class="checkout-module-card checkout-module-card--days"
    id="checkout-module-days-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="per_day"
    data-price-amount="{{ $perDayAmount }}"
>
    {{-- Header --}}
    <div class="d-flex align-items-start justify-content-between mb-12">
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

    {{-- Inline Date/Time Card — matches client design (Date & Time | Date & Time | Staff is separate) --}}
    <div class="checkout-inline-fields-card border border-primary rounded-12 p-10">
        <div class="row gx-0">

            {{-- Check-In --}}
            <div class="col-6 pr-8" style="border-right: 1px solid var(--gray-200);">
                <label class="font-11 font-weight-bold text-dark mb-4 d-block">
                    <x-iconsax-lin-calendar-2 class="icons text-gray-500" width="13px" height="13px"/>
                    {{ trans('checkout.check_in') }}
                </label>
                <input
                    type="date"
                    name="{{ $prefix }}[check_in]"
                    class="checkout-date-input checkout-inline-date-input w-100 border-0 bg-transparent font-12 text-dark p-0"
                    id="checkout_days_check_in_{{ $itemKey }}"
                    value="{{ $oldCheckIn }}"
                    min="{{ now()->format('Y-m-d') }}"
                    {{ $module->is_required ? 'required' : '' }}
                    style="outline: none; cursor: pointer;"
                >
            </div>

            {{-- Check-Out --}}
            <div class="col-6 pl-8">
                <label class="font-11 font-weight-bold text-dark mb-4 d-block">
                    <x-iconsax-lin-calendar-2 class="icons text-gray-500" width="13px" height="13px"/>
                    {{ trans('checkout.check_out') }}
                </label>
                <input
                    type="date"
                    name="{{ $prefix }}[check_out]"
                    class="checkout-date-input checkout-inline-date-input w-100 border-0 bg-transparent font-12 text-dark p-0"
                    id="checkout_days_check_out_{{ $itemKey }}"
                    value="{{ $oldCheckOut }}"
                    min="{{ now()->format('Y-m-d') }}"
                    {{ $module->is_required ? 'required' : '' }}
                    style="outline: none; cursor: pointer;"
                >
            </div>

        </div>
    </div>

    {{-- Nights Badge --}}
    <div class="checkout-module-meta mt-10">
        <span class="checkout-module-badge" id="checkout_days_nights_{{ $itemKey }}">
            0 {{ trans('checkout.nights') }}
        </span>
    </div>

    {{-- Errors --}}
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
    function updateNightsCount_{{ $itemKey }}() {
        var checkIn  = $('#checkout_days_check_in_{{ $itemKey }}').val();
        var checkOut = $('#checkout_days_check_out_{{ $itemKey }}').val();
        var $badge   = $('#checkout_days_nights_{{ $itemKey }}');

        if (!checkIn || !checkOut) {
            $badge.text('0 {{ trans("checkout.nights") }}');
            return;
        }

        var inDate  = new Date(checkIn);
        var outDate = new Date(checkOut);

        if (outDate <= inDate) {
            // auto-push check-out one day ahead
            var nextDay = new Date(inDate);
            nextDay.setDate(nextDay.getDate() + 1);
            var yyyy = nextDay.getFullYear();
            var mm   = String(nextDay.getMonth() + 1).padStart(2, '0');
            var dd   = String(nextDay.getDate()).padStart(2, '0');
            $('#checkout_days_check_out_{{ $itemKey }}').val(yyyy + '-' + mm + '-' + dd);
            outDate = nextDay;
        }

        var nights = Math.max(0, Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24)));
        $badge.text(nights + ' {{ trans("checkout.nights") }}');
        $(document).trigger('checkout:priceUpdate');
    }

    // Set min on check_out when check_in changes
    $(document).on('change', '#checkout_days_check_in_{{ $itemKey }}', function () {
        var val = $(this).val();
        if (val) {
            $('#checkout_days_check_out_{{ $itemKey }}').attr('min', val);
        }
        updateNightsCount_{{ $itemKey }}();
    });

    $(document).on('change', '#checkout_days_check_out_{{ $itemKey }}', function () {
        updateNightsCount_{{ $itemKey }}();
    });

    $(document).ready(function () {
        updateNightsCount_{{ $itemKey }}();
    });
})(jQuery);
</script>
@endpush