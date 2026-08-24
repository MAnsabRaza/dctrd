@extends('design_1.panel.layouts.panel')

@section('content')
<div class="bg-white p-16 rounded-16 border-gray-200">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-16">
        <h2 class="font-16 font-weight-bold mb-0">{{ $pageTitle }}</h2>

        <div class="d-flex flex-wrap gap-2">
            @foreach(['all' => 'All', 'pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'draft' => 'Draft'] as $key => $label)
                <a href="{{ route('panel.regulatory.list', ['status' => $key]) }}"
                   class="btn btn-sm {{ $status === $key ? 'btn-primary' : 'btn-outline-secondary' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    @if($submissions->count())
        <div class="table-responsive">
            <table class="table custom-table font-14">
                <tr>
                    <th class="text-left">Role</th>
                    <th class="text-left">Form</th>
                    <th class="text-center">Level</th>
                    <th class="text-center">Status</th>
                    <th class="text-left">Reason</th>
                    <th class="text-center">Submitted At</th>
                    <th class="text-left">Form Data</th>
                </tr>

                @foreach($submissions as $submission)
                    <tr>
                        <td class="text-left">{{ optional($submission->roleCatalog)->label ?? '-' }}</td>
                        <td class="text-left">{{ optional($submission->form)->title ?? '-' }}</td>
                        <td class="text-center">{{ ucfirst($submission->level ?? '-') }}</td>
                        <td class="text-center">
                            @if($submission->status === 'approved')
                                <span class="badge badge-success">Approved</span>
                            @elseif($submission->status === 'pending')
                                <span class="badge badge-warning">Pending</span>
                            @elseif($submission->status === 'rejected')
                                <span class="badge badge-danger">Rejected</span>
                            @elseif($submission->status === 'draft')
                                <span class="badge badge-secondary">Draft</span>
                            @else
                                <span class="badge badge-secondary">{{ ucfirst($submission->status) }}</span>
                            @endif
                        </td>
                        <td class="text-left">{{ $submission->rejection_reason ?: '-' }}</td>
                        <td class="text-center">{{ optional($submission->created_at)->format('Y-m-d H:i') }}</td>
                        <td class="text-left">
                            @if(!empty($submission->form) and $submission->form->fields->count())
                                <details>
                                    <summary class="cursor-pointer text-primary">View Data</summary>
                                    <div class="mt-10">
                                        @foreach($submission->form->fields as $field)
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

                                                if (is_array($value)) {
                                                    $value = implode(', ', $value);
                                                }
                                            @endphp

                                            <div class="mb-8">
                                                <strong>{{ $field->title }}:</strong>
                                                <span>{{ $value !== null && $value !== '' ? $value : '-' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </details>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>

        <div class="text-center mt-20">
            {{ $submissions->appends(request()->input())->links() }}
        </div>
    @else
        <div class="text-center text-gray-500 mt-30">
            No regulatory submissions found.
        </div>
    @endif
</div>
@endsection
