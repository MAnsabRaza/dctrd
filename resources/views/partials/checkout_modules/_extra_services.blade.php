{{-- resources/views/partials/checkout_modules/_extra_services.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $options = $module->config['options'] ?? [];
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
@endphp

<div
    class="checkout-module-card checkout-module-card--extras"
    id="checkout-module-extra-services-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="additive"
>
    <div class="font-13 font-weight-bold text-dark">
        {{ $module->translated_label }}
        @if($module->is_required)
            <span class="text-danger">*</span>
        @endif
    </div>

    @if($module->translated_help_text)
        <p class="checkout-module-helper mb-0 mt-4">{{ $module->translated_help_text }}</p>
    @endif

    <div class="checkout-extra-grid mt-12">
        @foreach($options as $index => $option)
            <label class="checkout-extra-option">
                <input
                    type="checkbox"
                    name="{{ $prefix }}[]"
                    value="{{ $option['label'] }}"
                    class="checkout-extra-service"
                    data-price="{{ $option['price'] ?? 0 }}"
                    id="checkout_extra_{{ $itemKey }}_{{ $index }}"
                    {{ in_array($option['label'], old('checkout_modules.' . $itemKey . '.' . $module->name, [])) ? 'checked' : '' }}
                >
                <span class="checkout-extra-option__label">{{ $option['label'] }}</span>
                <span class="checkout-extra-option__price">+{{ handlePrice($option['price'] ?? 0) }}</span>
            </label>
        @endforeach
    </div>

    @error('checkout_modules.' . $itemKey . '.extra_services')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).on('change', '.checkout-extra-service', function () {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
