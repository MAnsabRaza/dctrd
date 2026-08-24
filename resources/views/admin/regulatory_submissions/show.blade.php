@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <strong>{{ $form->title }}</strong> —
                            <span class="text-gray-500">{{ optional($submission->user)->full_name }}</span>
                        </div>
                        <div class="card-body">
                            @php
                                $items = optional($submission->formSubmission)->items ?? collect();
                            @endphp

                         @foreach($form->fields as $field)
    @php
        $fieldKey = 'field_' . $field->id;
        $value = data_get($submission->data ?? [], $fieldKey);

        if (in_array($field->type, ['dropdown', 'radio']) and !empty($value)) {
            $option = $field->options->firstWhere('id', $value);
            $value = $option->title ?? $value;
        }

        if ($field->type === 'checkbox' and is_array($value)) {
            $value = $field->options->whereIn('id', $value)->pluck('title')->implode(', ');
        }

        if ($field->type === 'toggle') {
            $value = !empty($value) ? 'Yes' : 'No';
        }
    @endphp

    <div class="form-group">
        <label class="input-label font-weight-bold">{{ $field->title }}</label>
        <p class="mb-0">{{ $value !== null && $value !== '' ? $value : '—' }}</p>
    </div>
@endforeach
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="card">
                        <div class="card-body">
                            <p><strong>Status:</strong> {{ ucfirst($submission->status) }}</p>

                            @if($submission->status == 'rejected' and !empty($submission->rejection_reason))
                                <p class="text-danger"><strong>Rejection reason:</strong> {{ $submission->rejection_reason }}</p>
                            @endif

                            @if($submission->status == 'pending')
                                <form method="post" action="{{ getAdminPanelUrl('/regulatory-submissions/' . $submission->id . '/approve') }}" class="mb-2">
                                    {{ csrf_field() }}
                                    <button type="submit" class="btn btn-success w-100">Approve</button>
                                </form>

                                <form method="post" action="{{ getAdminPanelUrl('/regulatory-submissions/' . $submission->id . '/reject') }}">
                                    {{ csrf_field() }}
                                    <div class="form-group">
                                        <textarea name="rejection_reason" class="form-control" placeholder="Rejection reason" required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-danger w-100">Reject</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection