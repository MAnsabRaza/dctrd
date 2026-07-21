@extends("design_1.panel.layouts.app")

@section('content')
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

            {{-- Primary form --}}
            @if($stack['primaryTemplate'])
                <form class="js-regulatory-form" data-template-id="{{ $stack['primaryTemplate']->id }}"
                      data-submission-id="{{ $stack['primarySubmission']->id ?? '' }}">
                    <h4 class="font-14 font-weight-bold mb-12">{{ $stack['primaryTemplate']->label }}</h4>

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
                </form>
            @endif

            {{-- Secondary/Tertiary/etc — "I want to add Brand/Warehouse" buttons --}}
            @foreach($stack['extraTemplates'] as $extraTemplate)
                <div class="mt-24 pt-24 border-top">
                    <button type="button" class="btn btn-sm btn-outline-primary js-add-slot-btn"
                            data-template-id="{{ $extraTemplate->id }}"
                            data-label="{{ $extraTemplate->label }}">
                        + I want to add {{ $extraTemplate->label }}
                    </button>

                    <div class="js-slots-container mt-12">
                        @foreach($stack['extraSubmissions']->get($extraTemplate->id, []) as $slotSubmission)
                            <div class="border-gray-200 rounded-12 p-12 mb-12 js-regulatory-slot">
                                <form class="js-regulatory-form" data-template-id="{{ $extraTemplate->id }}"
                                      data-submission-id="{{ $slotSubmission->id }}">
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
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @empty
        <div class="text-gray-500 mt-20">Koi regulatory form abhi applicable nahi hai.</div>
    @endforelse
</div>

{{-- Confirmation modal for "add slot" buttons --}}
<div class="modal fade" id="addSlotConfirmModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-24">
                <p id="addSlotConfirmText" class="mb-16"></p>
                <div class="d-flex justify-content-center gap-3">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">No, I chose it by Mistake</button>
                    <button type="button" class="btn btn-primary" id="addSlotConfirmYes">Yes, I want to apply for this</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
<script>
(function ($) {
    var pendingTemplateId = null;

    $('.js-add-slot-btn').on('click', function () {
        pendingTemplateId = $(this).data('template-id');
        $('#addSlotConfirmText').text('Are you sure you want to apply for ' + $(this).data('label') + '?');
        $('#addSlotConfirmModal').modal('show');
    });

    $('#addSlotConfirmYes').on('click', function () {
        $.post('{{ route("panel.regulatory.add_slot") }}', {
            _token: '{{ csrf_token() }}',
            template_id: pendingTemplateId
        }, function () {
            $('#addSlotConfirmModal').modal('hide');
            window.location.reload();
        });
    });

    $('.js-save-draft, .js-submit-review').on('click', function () {
        var $form  = $(this).closest('form.js-regulatory-form');
        var isDraft = $(this).hasClass('js-save-draft');
        var url = isDraft ? '{{ route("panel.regulatory.draft") }}' : '{{ route("panel.regulatory.submit") }}';

        var fields = {};
        $form.find('[name]').each(function () {
            fields[$(this).attr('name')] = $(this).val();
        });

        $.post(url, {
            _token: '{{ csrf_token() }}',
            template_id: $form.data('template-id'),
            submission_id: $form.data('submission-id'),
            fields: fields
        }, function (res) {
            showToast('success', '', res.msg);
        }).fail(function () {
            showToast('error', 'Error', 'Something went wrong');
        });
    });

    $('.js-delete-slot').on('click', function () {
        var submissionId = $(this).data('submission-id');
        var $slot = $(this).closest('.js-regulatory-slot');

        if (!confirm('Are you sure you want to delete this?')) return;

        $.ajax({
            url: '{{ url("/panel/regulatory") }}/' + submissionId,
            method: 'DELETE',
            data: { _token: '{{ csrf_token() }}' }
        }).done(function () {
            $slot.remove();
        });
    });
})(jQuery);
</script>
@endpush