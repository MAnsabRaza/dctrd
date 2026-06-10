{{-- resources/views/partials/checkout_modules/_extra_services.blade.php --}}

<div class="form-group mt-24" id="checkout-module-extra_services">
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
        $options = $module->config['options'] ?? [];
    @endphp

    <div class="extra-services-list">
        @foreach($options as $index => $option)
            <div class="extra-service-item">
                <label class="custom-checkbox">
                    <input 
                        type="checkbox" 
                        name="checkout_modules[extra_services][]"
                        value="{{ $option['label'] }}"
                        class="checkout-extra-service"
                        data-price="{{ $option['price'] ?? 0 }}"
                        {{ in_array($option['label'], old('checkout_modules.extra_services', [])) ? 'checked' : '' }}
                    >
                    <span>{{ $option['label'] }}</span>
                    <span class="price">+${{ number_format($option['price'] ?? 0, 2) }}</span>
                </label>
            </div>
        @endforeach
    </div>

    @error('checkout_modules.extra_services')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <style>
        .extra-services-list {
            display: grid;
            gap: 10px;
            margin-top: 12px;
        }
        .extra-service-item {
            display: block;
        }
        .custom-checkbox {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .custom-checkbox:hover {
            background: #f9f9f9;
            border-color: #007bff;
        }
        .custom-checkbox input[type="checkbox"] {
            margin-right: 10px;
            cursor: pointer;
        }
        .custom-checkbox input[type="checkbox"]:checked + span,
        .custom-checkbox input[type="checkbox"]:checked + span + .price {
            color: #007bff;
            font-weight: 600;
        }
        .custom-checkbox .price {
            margin-left: auto;
            color: #28a745;
            font-weight: 500;
        }
    </style>
    <script>
        $(document).on('change', '.checkout-extra-service', function() {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
