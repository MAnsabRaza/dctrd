{{-- resources/views/partials/checkout_modules/_cancellation_policy.blade.php --}}

<div class="form-group mt-24" id="checkout-module-cancellation_policy">
    <label class="form-group-label">
        {{ $module->translated_label }}
        @if($module->is_required)
            <span class="text-danger">*</span>
        @endif
    </label>
    
    @php
        $policyText = $module->config['policy_text'] ?? 'Free cancellation up to 24 hours before.';
    @endphp

    <div class="alert alert-info">
        {{ $policyText }}
    </div>

    @php $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]"; $optId = $itemId ?? '0'; @endphp
    <div class="custom-control custom-checkbox">
        <input 
            type="checkbox" 
            id="cancellation_policy_agree_{{ $optId }}"
            name="{{ $prefix }}"
            value="1"
            class="custom-control-input"
            {{ old($prefix) ? 'checked' : '' }}
            {{ $module->is_required ? 'required' : '' }}
        >
        <label class="custom-control-label" for="cancellation_policy_agree_{{ $optId }}">
            {{ trans('checkout.i_agree_cancellation_policy') }}
        </label>
    </div>

    @error('checkout_modules.cancellation_policy')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>
