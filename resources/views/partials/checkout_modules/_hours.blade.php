{{-- resources/views/partials/checkout_modules/_hours.blade.php --}}

<div class="form-group mt-24" id="checkout-module-hours">
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
        $slots = $module->config['slots'] ?? ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    @endphp

    @php $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]"; @endphp
    <div class="time-slot-grid">
        @foreach($slots as $slot)
            @php $optId = $itemId ?? '0'; @endphp
            <label class="time-slot-label">
                <input 
                    type="radio" 
                    name="{{ $prefix }}"
                    value="{{ $slot }}"
                    class="checkout-time-slot"
                    id="checkout_time_{{ $optId }}_{{ str_replace(':','', $slot) }}"
                    {{ old($prefix) == $slot ? 'checked' : '' }}
                >
                <span>{{ $slot }}</span>
            </label>
        @endforeach
    </div>

    @error('checkout_modules.hours')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <style>
        .time-slot-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
            gap: 10px;
            margin-top: 12px;
        }
        .time-slot-label {
            position: relative;
            display: block;
            cursor: pointer;
        }
        .time-slot-label input[type="radio"] {
            position: absolute;
            opacity: 0;
        }
        .time-slot-label span {
            display: block;
            padding: 8px 12px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 6px;
            transition: all 0.2s;
            user-select: none;
        }
        .time-slot-label input[type="radio"]:checked + span {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }
        .time-slot-label:hover span {
            border-color: #007bff;
        }
    </style>
    <script>
        $(document).on('change', '.checkout-time-slot', function() {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
