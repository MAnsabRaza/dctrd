{{-- resources/views/partials/checkout_modules/_hours.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $slots = $module->config['slots'] ?? ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    $priceRule = $module->price_rule ?? [];
    $perHourAmount = $priceRule['amount'] ?? 0;
@endphp

<div
    class="checkout-module-card checkout-module-card--hours"
    id="checkout-module-hours-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="per_hour"
    data-price-amount="{{ $perHourAmount }}"
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

        @if($perHourAmount)
            <span class="checkout-module-price">{{ handlePrice($perHourAmount) }}/hr</span>
        @endif
    </div>

    <div class="checkout-time-slot-grid mt-12">
        @foreach($slots as $slot)
            <label class="checkout-time-slot-option">
                <input
                    type="radio"
                    name="{{ $prefix }}"
                    value="{{ $slot }}"
                    class="checkout-time-slot"
                    id="checkout_time_{{ $itemKey }}_{{ str_replace(':','', $slot) }}"
                    {{ old('checkout_modules.' . $itemKey . '.' . $module->name) == $slot ? 'checked' : '' }}
                >
                <span>{{ $slot }}</span>
            </label>
        @endforeach
    </div>

    @error('checkout_modules.' . $itemKey . '.hours')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).on('change', '.checkout-time-slot', function () {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
