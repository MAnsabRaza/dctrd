{{-- resources/views/partials/checkout_modules/_cancellation_policy.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    $policyText = $module->config['policy_text'] ?? trans('checkout.free_cancellation_hint');
@endphp

<div
    class="checkout-module-card checkout-module-card--policy"
    id="checkout-module-cancellation-policy-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="none"
>
    <div class="font-13 font-weight-bold text-dark">
        {{ $module->translated_label }}
        @if($module->is_required)
            <span class="text-danger">*</span>
        @endif
    </div>

    <div class="alert alert-info mt-12 mb-0">
        {{ $policyText }}
    </div>

    <div class="custom-control custom-checkbox mt-12">
        <input
            type="checkbox"
            id="cancellation_policy_agree_{{ $itemKey }}"
            name="{{ $prefix }}"
            value="1"
            class="custom-control-input"
            {{ old('checkout_modules.' . $itemKey . '.' . $module->name) ? 'checked' : '' }}
            {{ $module->is_required ? 'required' : '' }}
        >
        <label class="custom-control-label" for="cancellation_policy_agree_{{ $itemKey }}">
            {{ trans('checkout.i_agree_cancellation_policy') }}
        </label>
    </div>

    @error('checkout_modules.' . $itemKey . '.cancellation_policy')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
