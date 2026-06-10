{{-- resources/views/partials/checkout_modules/_reviewer_message.blade.php --}}

<div class="form-group mt-24" id="checkout-module-reviewer_message">
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
        $maxLength = $module->config['max_length'] ?? 500;
        $placeholder = $module->config['placeholder'] ?? trans('checkout.message_to_reviewer');
    @endphp

    <textarea 
        name="checkout_modules[reviewer_message]"
        class="form-control checkout-textarea"
        rows="4"
        placeholder="{{ $placeholder }}"
        maxlength="{{ $maxLength }}"
        {{ $module->is_required ? 'required' : '' }}
    >{{ old('checkout_modules.reviewer_message') }}</textarea>

    <div class="small text-muted mt-1">
        <span id="reviewer_message_count">0</span> / {{ $maxLength }} {{ trans('checkout.characters') }}
    </div>

    @error('checkout_modules.reviewer_message')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
    <script>
        $(document).on('input', 'textarea[name="checkout_modules[reviewer_message]"]', function() {
            let count = $(this).val().length;
            $('#reviewer_message_count').text(count);
        });

        // Initialize on load
        let initialCount = $('textarea[name="checkout_modules[reviewer_message]"]').val().length;
        $('#reviewer_message_count').text(initialCount);
    </script>
@endpush
