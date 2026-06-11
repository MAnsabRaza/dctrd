{{-- resources/views/partials/checkout_modules/_staff_member.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
@endphp

<div
    class="checkout-module-card checkout-module-card--staff"
    id="checkout-module-staff-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="none"
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

    <div class="form-group mb-0 mt-12">
        <label class="form-group-label">{{ trans('checkout.select_staff') }}</label>
        <select
            name="{{ $prefix }}"
            class="form-control checkout-staff-select"
            id="checkout_staff_{{ $itemKey }}"
            {{ $module->is_required ? 'required' : '' }}
        >
            <option value="">--- {{ trans('checkout.select_staff') }} ---</option>
        </select>
    </div>

    @error('checkout_modules.' . $itemKey . '.staff_member')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).on('change', '.checkout-staff-select', function () {
            $(document).trigger('checkout:priceUpdate');
        });
    </script>
@endpush
