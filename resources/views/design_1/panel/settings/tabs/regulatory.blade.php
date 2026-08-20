@php
    $stacks = $stacks ?? [];
    $countries = $countries ?? collect();
    $userCountry = $userCountry ?? null;
@endphp

<div class="regulatory-forms">
    @forelse($stacks as $stack)
        <div class="bg-white p-16 rounded-16 border-gray-200 mt-20">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h3 class="font-16 font-weight-bold mb-0">
                    {{ $stack['role']->label }}
                    <span class="badge-status ml-2 {{ $stack['roleRequestStatus'] === 'active' ? 'text-success bg-success-30' : 'text-warning bg-warning-30' }}">
                        {{ ucfirst($stack['roleRequestStatus']) }}
                    </span>
                </h3>
            </div>

            {{-- Primary form — <div> hi rahega, nested <form> HTML-invalid hai --}}
            @if($stack['primaryForm'])
                @php
                    $primaryFormKey = 'primary_' . $stack['primaryForm']->id;
                @endphp

                <div class="js-regulatory-form" data-form-id="{{ $stack['primaryForm']->id }}"
                     data-submission-id="{{ $stack['primarySubmission']->id ?? '' }}">
                    <input type="hidden" name="regulatory_forms[{{ $primaryFormKey }}][form_id]" value="{{ $stack['primaryForm']->id }}">
                    <input type="hidden" name="regulatory_forms[{{ $primaryFormKey }}][submission_id]" value="{{ $stack['primarySubmission']->id ?? '' }}">

                    <h4 class="font-14 font-weight-bold mb-12">{{ $stack['primaryForm']->title }}</h4>

                    @include('design_1.panel.settings.includes.regulatory_field_group', [
                        'fields'       => $stack['primaryForm']->fields,
                        'submission'   => $stack['primarySubmission'] ?? null,
                        'formKey'      => $primaryFormKey,
                        'countries'    => $countries,
                        'userCountry'  => $userCountry,
                    ])

                    <div class="d-flex gap-3 mt-16">
                        <button type="button" class="btn btn-outline-secondary js-save-draft">Save Draft</button>
                        <button type="button" class="btn btn-primary js-submit-review">Submit for Review</button>
                    </div>
                </div>
            @endif

            {{-- Secondary/Tertiary/etc --}}
            @foreach(($stack['extraTemplates'] ?? []) as $extraTemplate)
                <div class="mt-24 pt-24 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary js-add-slot-btn"
                            data-form-id="{{ $extraTemplate->id }}"
                            data-label="{{ $extraTemplate->title }}">
                        + I want to add {{ $extraTemplate->title }}
                    </button>

                    <div class="js-slots-container mt-12">
                        @foreach(($stack['extraSubmissions'] ?? collect())->get($extraTemplate->id, []) as $slotSubmission)
                            @php
                                $slotFormKey = 'submission_' . $slotSubmission->id;
                            @endphp

                            <div class="border-gray-200 rounded-12 p-12 mb-12 js-regulatory-slot">
                                <div class="js-regulatory-form" data-form-id="{{ $extraTemplate->id }}"
                                     data-submission-id="{{ $slotSubmission->id }}">
                                    <input type="hidden" name="regulatory_forms[{{ $slotFormKey }}][form_id]" value="{{ $extraTemplate->id }}">
                                    <input type="hidden" name="regulatory_forms[{{ $slotFormKey }}][submission_id]" value="{{ $slotSubmission->id }}">

                                    @include('design_1.panel.settings.includes.regulatory_field_group', [
                                        'fields'       => $extraTemplate->fields,
                                        'submission'   => $slotSubmission,
                                        'formKey'      => $slotFormKey,
                                        'countries'    => $countries,
                                        'userCountry'  => $userCountry,
                                    ])

                                    <div class="d-flex gap-3 mt-12">
                                        <button type="button" class="btn btn-sm btn-outline-secondary js-save-draft">Save Draft</button>
                                        <button type="button" class="btn btn-sm btn-primary js-submit-review">Submit for Review</button>
                                        <button type="button" class="btn btn-sm btn-danger js-delete-slot"
                                                data-submission-id="{{ $slotSubmission->id }}">Delete</button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-gray-500 mt-20">There are no regulatory forms available at the moment.</div>
    @endforelse
</div>

{{-- Confirmation modal --}}
<div class="modal fade" id="addSlotConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-body text-center p-24 bg-white rounded-16">
            <p class="mb-8 font-weight-bold">Are you sure the choice that you made?</p>
            <p id="addSlotConfirmText" class="mb-16 text-gray-500"></p>
            <p class="text-small mb-16">After you make your choice, you can not undo it!!</p>
            <div class="d-flex justify-content-center gap-3">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No, I chose it by Mistake</button>
                <button type="button" class="btn btn-primary" id="addSlotConfirmYes">Yes, I want to apply for this</button>
            </div>
        </div>
    </div>
</div>

<style>
.js-regulatory-field.has-error .form-control,
.js-regulatory-field.has-error .custom-control-input ~ .custom-control-label {
    border-color: #dc3545;
}
.js-regulatory-field .js-field-error {
    display: none;
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
}
.js-regulatory-field.has-error .js-field-error {
    display: block;
}
</style>

<script>
(function ($) {
    'use strict';

    var pendingFormId = null;

    $(document).on('click', '.js-add-slot-btn', function () {
        pendingFormId = $(this).data('form-id');
        $('#addSlotConfirmText').text('I want to apply for ' + $(this).data('label'));
        $('#addSlotConfirmModal').modal('show');
    });

    $(document).on('click', '#addSlotConfirmYes', function () {
        $.post('{{ route("panel.regulatory.add_slot") }}', {
            _token: '{{ csrf_token() }}',
            form_id: pendingFormId
        }, function () {
            $('#addSlotConfirmModal').modal('hide');
            window.location.reload();
        }).fail(function () {
            showToast('error', 'Error', 'Could not add this section, please try again.');
        });
    });

    $(document).on('click', '.js-save-draft, .js-submit-review', function (e) {
        e.preventDefault();

        var $btn    = $(this);
        var $form   = $btn.closest('.js-regulatory-form');
        var isDraft = $btn.hasClass('js-save-draft');
        var url     = isDraft
            ? '{{ route("panel.regulatory.draft") }}'
            : '{{ route("panel.regulatory.submit") }}';

        // reset previous error states
        $form.find('.js-regulatory-field').removeClass('has-error').find('.js-field-error').text('');

        var fields = {};
        var hasError = false;

        $form.find('.js-regulatory-field').each(function () {
            var $fieldWrap = $(this);
            var fieldKey   = $fieldWrap.find('[data-field-key]').first().data('field-key');
            var isRequired = $fieldWrap.data('required') == 1;
            var value;

            var $checkboxGroup = $fieldWrap.find('input[type="checkbox"][name$="[]"]');
            var $radioGroup     = $fieldWrap.find('input[type="radio"]');
            var $singleToggle   = $fieldWrap.find('input[type="checkbox"]:not([name$="[]"])');

            if ($checkboxGroup.length) {
                value = $checkboxGroup.filter(':checked').map(function () { return $(this).val(); }).get();
            } else if ($radioGroup.length) {
                value = $radioGroup.filter(':checked').val() || '';
            } else if ($singleToggle.length) {
                value = $singleToggle.is(':checked') ? '1' : '';
            } else {
                value = $fieldWrap.find('[data-field-key]').first().val();
            }

            fields[fieldKey] = value;

            var isEmpty = (Array.isArray(value) && value.length === 0) ||
                          (!Array.isArray(value) && (value === undefined || value === null || value === ''));

            if (isRequired && isEmpty) {
                hasError = true;
                $fieldWrap.addClass('has-error');
                $fieldWrap.find('.js-field-error').text('This field is required.');
            }
        });

        if (hasError) {
            showToast('error', 'Error', 'Please fill in all required fields.');
            return;
        }

        $btn.prop('disabled', true);

        $.post(url, {
            _token:         '{{ csrf_token() }}',
            form_id:        $form.data('form-id'),
            submission_id:  $form.data('submission-id') || '',
            is_submit:      !isDraft,
            fields:         fields
        }, function (res) {
            showToast('success', '', res.msg);
            $form.attr('data-submission-id', res.submission_id).data('submission-id', res.submission_id);
        }).fail(function (xhr) {
            var msg = 'Something went wrong';
            if (xhr.responseJSON && xhr.responseJSON.errors) {
                msg = Object.values(xhr.responseJSON.errors).flat().join(' ');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            showToast('error', 'Error', msg);
        }).always(function () {
            $btn.prop('disabled', false);
        });
    });

    $(document).on('click', '.js-delete-slot', function () {
        var submissionId = $(this).data('submission-id');
        var $slot = $(this).closest('.js-regulatory-slot');

        if (!confirm('Are you sure you want to delete this?')) return;

        $.ajax({
            url: '{{ url("/panel/regulatory") }}/' + submissionId,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' }
        }).done(function () {
            $slot.remove();
        }).fail(function () {
            showToast('error', 'Error', 'Could not delete, please try again.');
        });
    });

})(jQuery);
</script>