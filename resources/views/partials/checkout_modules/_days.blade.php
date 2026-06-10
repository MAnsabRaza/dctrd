{{-- resources/views/partials/checkout_modules/_days.blade.php --}}

<div class="form-group mt-24" id="checkout-module-days">
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
        $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    @endphp

    <div class="row">
        <div class="col-md-6">
            <input 
                type="date" 
                name="{{ $prefix }}[check_in]"
                class="form-control checkout-date-input"
                id="checkout_days_check_in_{{ $itemId ?? '0' }}"
                value="{{ old($prefix . '.check_in') }}"
                data-min-date="{{ now()->format('Y-m-d') }}"
                required
            >
            <small class="text-muted">{{ trans('checkout.check_in') }}</small>
        </div>
        <div class="col-md-6">
            <input 
                type="date" 
                name="{{ $prefix }}[check_out]"
                class="form-control checkout-date-input"
                id="checkout_days_check_out_{{ $itemId ?? '0' }}"
                value="{{ old($prefix . '.check_out') }}"
                required
            >
            <small class="text-muted">{{ trans('checkout.check_out') }}</small>
        </div>
    </div>

    <div class="mt-2" id="checkout-days-info">
        <span id="nights-count" class="badge badge-info">0 {{ trans('checkout.nights') }}</span>
    </div>

    @error('checkout_modules.days.check_in')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
    @error('checkout_modules.days.check_out')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).ready(function() {
            updateNights();
            
                    $('#checkout_days_check_in_{{ $itemId ?? '0' }}, #checkout_days_check_out_{{ $itemId ?? '0' }}').on('change', updateNights);

            function updateNights() {
                let checkIn = $('#checkout_days_check_in_{{ $itemId ?? '0' }}').val();
                let checkOut = $('#checkout_days_check_out_{{ $itemId ?? '0' }}').val();

                if (checkIn && checkOut) {
                    let inDate = new Date(checkIn);
                    let outDate = new Date(checkOut);
                    let nights = Math.ceil((outDate - inDate) / (1000 * 60 * 60 * 24));
                    $('#nights-count').text(Math.max(1, nights) + ' {{ trans("checkout.nights") }}');
                    
                    // Trigger price update
                    $(document).trigger('checkout:priceUpdate');
                }
            }
        });
    </script>
@endpush
