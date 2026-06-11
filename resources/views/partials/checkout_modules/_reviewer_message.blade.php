{{-- resources/views/partials/checkout_modules/_reviewer_message.blade.php --}}
@php
    $itemKey = $itemId ?? '0';
    $maxLength = $module->config['max_length'] ?? 500;
    $placeholder = $module->config['placeholder'] ?? trans('checkout.message_to_reviewer');
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
@endphp

<div
    class="checkout-module-card checkout-module-card--reviewer"
    id="checkout-module-reviewer-message-{{ $itemKey }}"
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
        rows="3"
        id="reviewer_message_{{ $itemKey }}"
        placeholder="{{ $placeholder }}"
        maxlength="{{ $maxLength }}"
        {{ $module->is_required ? 'required' : '' }}
    >{{ old('checkout_modules.' . $itemKey . '.' . $module->name) }}</textarea>

    <div class="checkout-module-meta mt-8">
        <span class="checkout-module-badge">
            <span id="reviewer_message_count_{{ $itemKey }}">0</span> / {{ $maxLength }} {{ trans('checkout.characters') }}
        </span>
    </div>

    @error('checkout_modules.' . $itemKey . '.reviewer_message')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
<script>
(function ($) {
    function updateReviewerMessageCount_{{ $itemKey }}() {
        var val = $('textarea[name="{{ $prefix }}"]').val() || '';
        $('#reviewer_message_count_{{ $itemKey }}').text(val.length);
    }
    $(document).on('input', 'textarea[name="{{ $prefix }}"]', updateReviewerMessageCount_{{ $itemKey }});
    $(document).ready(updateReviewerMessageCount_{{ $itemKey }});
})(jQuery);
</script>
@endpush