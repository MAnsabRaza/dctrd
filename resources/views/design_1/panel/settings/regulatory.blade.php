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

            {{-- Primary form — NOTE: <form> nahi, <div> hai (outer settings form ke andar
                 nested <form> HTML-invalid hai aur browser use silently drop kar deta hai) --}}
            @if($stack['primaryTemplate'])
                @php
                    $savedPrimaryCountry = data_get($stack['primarySubmission']->data ?? [], 'country');
                    $primarySelectedCountry = $savedPrimaryCountry ?: $userCountry;
                @endphp

                <div class="js-regulatory-form" data-template-id="{{ $stack['primaryTemplate']->id }}"
                     data-submission-id="{{ $stack['primarySubmission']->id ?? '' }}">
                    <h4 class="font-14 font-weight-bold mb-12">{{ $stack['primaryTemplate']->label }}</h4>

                    <div class="form-group">
                        <label class="form-group-label">Country</label>
                        <select name="country" class="form-control select2">
                            <option value="">Select Country</option>
                            @foreach($countries as $country)
                                <option value="{{ $country->name }}" {{ $primarySelectedCountry == $country->name ? 'selected' : '' }}>
                                    {{ $country->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @foreach($stack['primaryTemplate']->fields as $field)
                        @include('design_1.panel.settings.includes.regulatory_field', [
                            'field' => $field,
                            'value' => data_get($stack['primarySubmission']->data ?? [], $field['key']),
                        ])
                    @endforeach

                    <div class="d-flex gap-3 mt-16">
                        <button type="button" class="btn btn-outline-secondary js-save-draft">Save Draft</button>
                        <button type="button" class="btn btn-primary js-submit-review">Submit for Review</button>
                    </div>
                </div>
            @endif

            {{-- Secondary/Tertiary/etc --}}
            @foreach($stack['extraTemplates'] as $extraTemplate)
                <div class="mt-24 pt-24 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary js-add-slot-btn"
                            data-template-id="{{ $extraTemplate->id }}"
                            data-label="{{ $extraTemplate->label }}">
                        + I want to add {{ $extraTemplate->label }}
                    </button>

                    <div class="js-slots-container mt-12">
                        @foreach($stack['extraSubmissions']->get($extraTemplate->id, []) as $slotSubmission)
                            @php
                                $savedSlotCountry = data_get($slotSubmission->data, 'country');
                                $slotSelectedCountry = $savedSlotCountry ?: $userCountry;
                            @endphp

                            <div class="border-gray-200 rounded-12 p-12 mb-12 js-regulatory-slot">
                                <div class="js-regulatory-form" data-template-id="{{ $extraTemplate->id }}"
                                     data-submission-id="{{ $slotSubmission->id }}">

                                    <div class="form-group">
                                        <label class="form-group-label">Country</label>
                                        <select name="country" class="form-control select2">
                                            <option value="">Select Country</option>
                                            @foreach($countries as $country)
                                                <option value="{{ $country->name }}" {{ $slotSelectedCountry == $country->name ? 'selected' : '' }}>
                                                    {{ $country->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    @foreach($extraTemplate->fields as $field)
                                        @include('design_1.panel.settings.includes.regulatory_field', [
                                            'field' => $field,
                                            'value' => data_get($slotSubmission->data, $field['key']),
                                        ])
                                    @endforeach

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

<script>
(function ($) {
    'use strict';

    var pendingTemplateId = null;

    // ── "I want to add Branch/Warehouse" button -> confirm popup ──────
    $(document).on('click', '.js-add-slot-btn', function () {
        pendingTemplateId = $(this).data('template-id');
        $('#addSlotConfirmText').text('I want to apply for ' + $(this).data('label'));
        $('#addSlotConfirmModal').modal('show');
    });

    $(document).on('click', '#addSlotConfirmYes', function () {
        $.post('{{ route("panel.regulatory.add_slot") }}', {
            _token: '{{ csrf_token() }}',
            template_id: pendingTemplateId
        }, function () {
            $('#addSlotConfirmModal').modal('hide');
            window.location.reload();
        }).fail(function () {
            showToast('error', 'Error', 'Could not add this section, please try again.');
        });
    });

    // ── Save Draft / Submit for Review ─────────────────────────────────
    $(document).on('click', '.js-save-draft, .js-submit-review', function (e) {
        e.preventDefault();

        var $btn    = $(this);
        var $form   = $btn.closest('.js-regulatory-form'); // <div>, not <form> — nested form issue se bacha
        var isDraft = $btn.hasClass('js-save-draft');
        var url     = isDraft
            ? '{{ route("panel.regulatory.draft") }}'
            : '{{ route("panel.regulatory.submit") }}';

        var fields = {};
        $form.find('[name]').each(function () {
            fields[$(this).attr('name')] = $(this).val();
        });

        $btn.prop('disabled', true);

        $.post(url, {
            _token:         '{{ csrf_token() }}',
            template_id:    $form.data('template-id'),
            submission_id:  $form.data('submission-id') || '',
            fields:         fields
        }, function (res) {
            showToast('success', '', res.msg);
            // Pehli save ke baad submission_id set kar do, taake agli save "update" ho, naya row na bane
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
    }); // <-- YEH closing bracket pehle MISSING thi — yehi asal bug tha

    // ── Delete secondary/tertiary slot ──────────────────────────────────
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