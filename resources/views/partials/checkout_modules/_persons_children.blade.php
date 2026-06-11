{{-- resources/views/partials/checkout_modules/_persons_children.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $config = $module->config ?? [];
    $adultsConfig = $config['adults'] ?? ['min' => 1, 'max' => 20];
    $childrenConfig = $config['children'] ?? ['min' => 0, 'max' => 10];
    $roomsConfig = $config['rooms'] ?? ['min' => 1, 'max' => 10];
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    $priceRule = $module->price_rule ?? [];
    $perPersonAmount = $priceRule['amount'] ?? 0;
@endphp

<div
    class="checkout-module-card checkout-module-card--people"
    id="checkout-module-persons-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="per_person"
    data-price-amount="{{ $perPersonAmount }}"
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

        @if($perPersonAmount)
            <span class="checkout-module-price">{{ handlePrice($perPersonAmount) }}/person</span>
        @endif
    </div>

    <div class="checkout-module-stack mt-12">
        <div class="checkout-stepper-row">
            <label>{{ trans('checkout.adults') }}</label>
            <div class="checkout-stepper">
                <button type="button" class="checkout-stepper-btn stepper-btn-minus" data-field="adults">-</button>
                <input
                    type="number"
                    name="{{ $prefix }}[adults]"
                    class="stepper-input checkout-stepper-input"
                    id="checkout_person_adults_{{ $itemKey }}"
                    value="{{ old('checkout_modules.' . $itemKey . '.' . $module->name . '.adults', 1) }}"
                    min="{{ $adultsConfig['min'] }}"
                    max="{{ $adultsConfig['max'] }}"
                    required
                >
                <button type="button" class="checkout-stepper-btn stepper-btn-plus" data-field="adults">+</button>
            </div>
        </div>

        <div class="checkout-stepper-row">
            <label>{{ trans('checkout.children') }}</label>
            <div class="checkout-stepper">
                <button type="button" class="checkout-stepper-btn stepper-btn-minus" data-field="children">-</button>
                <input
                    type="number"
                    name="{{ $prefix }}[children]"
                    class="stepper-input checkout-stepper-input"
                    id="checkout_person_children_{{ $itemKey }}"
                    value="{{ old('checkout_modules.' . $itemKey . '.' . $module->name . '.children', 0) }}"
                    min="{{ $childrenConfig['min'] }}"
                    max="{{ $childrenConfig['max'] }}"
                >
                <button type="button" class="checkout-stepper-btn stepper-btn-plus" data-field="children">+</button>
            </div>
        </div>

        <div class="checkout-stepper-row">
            <label>{{ trans('checkout.rooms') }}</label>
            <div class="checkout-stepper">
                <button type="button" class="checkout-stepper-btn stepper-btn-minus" data-field="rooms">-</button>
                <input
                    type="number"
                    name="{{ $prefix }}[rooms]"
                    class="stepper-input checkout-stepper-input"
                    id="checkout_person_rooms_{{ $itemKey }}"
                    value="{{ old('checkout_modules.' . $itemKey . '.' . $module->name . '.rooms', 1) }}"
                    min="{{ $roomsConfig['min'] }}"
                    max="{{ $roomsConfig['max'] }}"
                    required
                >
                <button type="button" class="checkout-stepper-btn stepper-btn-plus" data-field="rooms">+</button>
            </div>
        </div>
    </div>

    @error('checkout_modules.' . $itemKey . '.persons_children')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).on('click', '.stepper-btn-minus, .stepper-btn-plus', function (e) {
            e.preventDefault();

            var $input = $(this).closest('.checkout-stepper').find('.stepper-input');
            var value = parseInt($input.val(), 10) || 0;
            var min = parseInt($input.attr('min'), 10) || 0;
            var max = parseInt($input.attr('max'), 10) || 999;

            if ($(this).hasClass('stepper-btn-minus')) {
                value = Math.max(min, value - 1);
            } else {
                value = Math.min(max, value + 1);
            }

            $input.val(value).trigger('change');
            $(document).trigger('checkout:priceUpdate');
        });

        $(document).on('change', '.checkout-stepper-input', function () {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
