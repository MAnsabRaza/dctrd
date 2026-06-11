{{-- resources/views/partials/checkout_modules/_checkout_message.blade.php --}}

@php
    $itemKey = $itemId ?? '0';
    $maxLength = $module->config['max_length'] ?? 500;
    $placeholder = $module->config['placeholder'] ?? trans('checkout.special_instructions');
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
@endphp

<div
    class="checkout-module-card checkout-module-card--message"
    id="checkout-module-checkout-message-{{ $itemKey }}"
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

    <textarea
        name="{{ $prefix }}"
        class="form-control checkout-textarea mt-12"
        rows="4"
        id="checkout_message_{{ $itemKey }}"
        placeholder="{{ $placeholder }}"
        maxlength="{{ $maxLength }}"
        {{ $module->is_required ? 'required' : '' }}
    >{{ old('checkout_modules.' . $itemKey . '.' . $module->name) }}</textarea>

    <div class="checkout-module-meta mt-8">
        <span class="checkout-module-badge">
            <span id="checkout_message_count_{{ $itemKey }}">0</span> / {{ $maxLength }} {{ trans('checkout.characters') }}
        </span>
    </div>

    @error('checkout_modules.' . $itemKey . '.checkout_message')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        (function ($) {
            function updateCheckoutMessageCount{{ $itemKey }}() {
                var $input = $('textarea[name="{{ $prefix }}"]');
                $('#checkout_message_count_{{ $itemKey }}').text($input.val() ? $input.val().length : 0);
            }

            $(document).on('input', 'textarea[name="{{ $prefix }}"]', updateCheckoutMessageCount{{ $itemKey }});
            $(document).ready(updateCheckoutMessageCount{{ $itemKey }});
        })(jQuery);
    </script>
@endpush
