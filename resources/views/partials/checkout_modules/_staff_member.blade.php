{{-- resources/views/partials/checkout_modules/_staff_member.blade.php --}}

<div class="form-group mt-24" id="checkout-module-staff_member">
    <label class="form-group-label">
        {{ $module->translated_label }}
        @if($module->is_required)
            <span class="text-danger">*</span>
        @endif
    </label>
    
    @if($module->translated_help_text)
        <p class="text-muted small mb-2">{{ $module->translated_help_text }}</p>
    @endif

    @php $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]"; @endphp
    <select 
        name="{{ $prefix }}"
        class="form-control checkout-staff-select"
        {{ $module->is_required ? 'required' : '' }}
    >
        <option value="">--- {{ trans('checkout.select_staff') }} ---</option>
        {{-- Staff options will be loaded via AJAX --}}
    </select>

    @error('checkout_modules.staff_member')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).on('change', '.checkout-staff-select', function() {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
