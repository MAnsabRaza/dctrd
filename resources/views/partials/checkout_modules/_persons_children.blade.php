{{-- resources/views/partials/checkout_modules/_persons_children.blade.php --}}

<div class="form-group mt-24" id="checkout-module-persons_children">
    <label class="form-group-label">
        {{ $module->translated_label }}
        @if($module->is_required)
            <span class="text-danger">*</span>
        @endif
    </label>
    
    @if($module->translated_help_text)
        <p class="text-muted small mb-2">{{ $module->translated_help_text }}</p>
    @endif

    @php
        $config = $module->config ?? [];
        $adultsConfig = $config['adults'] ?? ['min' => 1, 'max' => 20];
        $childrenConfig = $config['children'] ?? ['min' => 0, 'max' => 10];
        $roomsConfig = $config['rooms'] ?? ['min' => 1, 'max' => 10];
        $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    @endphp

    <div class="stepper-group">
        {{-- Adults --}}
        <div class="stepper-item">
            <label>{{ trans('checkout.adults') }}</label>
            <div class="stepper-control">
                <button type="button" class="btn btn-sm btn-light stepper-btn-minus" data-field="adults">−</button>
                <input 
                    type="number" 
                    name="{{ $prefix }}[adults]"
                    class="stepper-input checkout-stepper-input"
                    value="{{ old($prefix . '.adults', 1) }}"
                    min="{{ $adultsConfig['min'] }}"
                    max="{{ $adultsConfig['max'] }}"
                    required
                >
                <button type="button" class="btn btn-sm btn-light stepper-btn-plus" data-field="adults">+</button>
            </div>
        </div>

        {{-- Children --}}
        <div class="stepper-item">
            <label>{{ trans('checkout.children') }}</label>
            <div class="stepper-control">
                <button type="button" class="btn btn-sm btn-light stepper-btn-minus" data-field="children">−</button>
                <input 
                    type="number" 
                    name="{{ $prefix }}[children]"
                    class="stepper-input checkout-stepper-input"
                    value="{{ old($prefix . '.children', 0) }}"
                    min="{{ $childrenConfig['min'] }}"
                    max="{{ $childrenConfig['max'] }}"
                >
                <button type="button" class="btn btn-sm btn-light stepper-btn-plus" data-field="children">+</button>
            </div>
        </div>

        {{-- Rooms --}}
        <div class="stepper-item">
            <label>{{ trans('checkout.rooms') }}</label>
            <div class="stepper-control">
                <button type="button" class="btn btn-sm btn-light stepper-btn-minus" data-field="rooms">−</button>
                <input 
                    type="number" 
                    name="{{ $prefix }}[rooms]"
                    class="stepper-input checkout-stepper-input"
                    value="{{ old($prefix . '.rooms', 1) }}"
                    min="{{ $roomsConfig['min'] }}"
                    max="{{ $roomsConfig['max'] }}"
                    required
                >
                <button type="button" class="btn btn-sm btn-light stepper-btn-plus" data-field="rooms">+</button>
            </div>
        </div>
    </div>

    @error('checkout_modules.persons_children')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <style>
        .stepper-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-top: 12px;
        }
        .stepper-item label {
            display: block;
            margin-bottom: 8px;
            font-size: 13px;
            font-weight: 500;
        }
        .stepper-control {
            display: flex;
            border: 1px solid #ddd;
            border-radius: 4px;
            overflow: hidden;
        }
        .stepper-control button {
            flex: 0 0 36px;
            padding: 0;
            border: 0;
            background: #f5f5f5;
            cursor: pointer;
            font-weight: bold;
            border-right: 1px solid #ddd;
        }
        .stepper-control button:last-child {
            border-right: 0;
            border-left: 1px solid #ddd;
            order: 3;
        }
        .stepper-control button:hover {
            background: #e9e9e9;
        }
        .stepper-input {
            flex: 1;
            border: 0;
            text-align: center;
            padding: 6px;
            font-weight: 600;
        }
    </style>
    <script>
        $(document).on('click', '.stepper-btn-minus, .stepper-btn-plus', function(e) {
            e.preventDefault();
            let field = $(this).data('field');
            let $input = $(this).closest('.stepper-control').find('.stepper-input');
            let val = parseInt($input.val()) || 0;
            let min = parseInt($input.attr('min')) || 0;
            let max = parseInt($input.attr('max')) || 999;

            if ($(this).hasClass('stepper-btn-minus')) {
                val = Math.max(min, val - 1);
            } else {
                val = Math.min(max, val + 1);
            }

            $input.val(val).trigger('change');
            $(document).trigger('checkout:priceUpdate');
        });

        $(document).on('change', '.stepper-input', function() {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
