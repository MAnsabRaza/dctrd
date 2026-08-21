@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ trans('update.regulatory_submissions') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="card">
                <div class="card-header d-flex gap-3">
                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected', 'draft' => 'Draft', 'all' => 'All'] as $key => $label)
                        <a href="{{ getAdminPanelUrl('/regulatory-submissions') }}?status={{ $key }}"
                           class="btn btn-sm {{ $status == $key ? 'btn-primary' : 'btn-outline-secondary' }}">{{ $label }}</a>
                    @endforeach
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table custom-table font-14">
                            <tr>
                                <th>{{ trans('admin/main.user') }}</th>
                                <th>Role</th>
                                <th>Form</th>
                                <th>Status</th>
                                <th>{{ trans('update.submission_date') }}</th>
                                <th>{{ trans('admin/main.actions') }}</th>
                            </tr>
                            @forelse($submissions as $submission)
                                <tr>
                                    <td>{{ optional($submission->user)->full_name ?? '—' }}</td>
                                    <td>{{ optional($submission->roleCatalog)->label ?? '—' }}</td>
                                    <td>{{ optional($submission->form)->title ?? '—' }}</td>
                                    <td>
                                        <span class="badge-status
                                            {{ $submission->status == 'approved' ? 'text-success bg-success-30' : '' }}
                                            {{ $submission->status == 'rejected' ? 'text-danger bg-danger-30' : '' }}
                                            {{ $submission->status == 'pending' ? 'text-warning bg-warning-30' : '' }}">
                                            {{ ucfirst($submission->status) }}
                                        </span>
                                    </td>
                                    <td>{{ dateTimeFormat($submission->created_at, 'j M Y H:i') }}</td>
                                    <td>
                                        <a href="{{ getAdminPanelUrl('/regulatory-submissions/' . $submission->id . '/show') }}" class="btn btn-sm btn-outline-primary">
                                            {{ trans('update.show_details') }}
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-gray-500">No submissions found.</td></tr>
                            @endforelse
                        </table>
                    </div>
                </div>

                <div class="card-footer text-center">
                    {{ $submissions->appends(request()->input())->links() }}
                </div>
            </div>
        </div>
    </section>
@endsection