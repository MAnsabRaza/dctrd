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

    <div class="d-flex align-items-start gap-8 p-10 rounded-12 mt-12"
         style="background: rgba(30,84,255,0.06); border: 1px solid rgba(30,84,255,0.14);">
        <x-iconsax-lin-info-circle class="icons text-primary mt-2 flex-shrink-0" width="16px" height="16px"/>
        <p class="font-12 text-gray-600 mb-0">{{ $policyText }}</p>
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
        <label class="custom-control-label font-12 text-gray-700" for="cancellation_policy_agree_{{ $itemKey }}">
            {{ trans('checkout.i_agree_cancellation_policy') }}
        </label>
    </div>

    @error('checkout_modules.' . $itemKey . '.cancellation_policy')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>