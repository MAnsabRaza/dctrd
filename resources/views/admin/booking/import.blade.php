@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_imports') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_imports') }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @php
                            $createActive = (!empty($errors) && $errors->any());
                            $detailActive = !empty($import);
                        @endphp

                        <ul class="nav nav-pills" id="importTab" role="tablist">

                            @can('admin_booking_imports')
                                <li class="nav-item">
                                    <a class="nav-link {{ (!$createActive && !$detailActive) ? 'active' : '' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_imports') }}
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_imports_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ trans('admin/main.import_bookings') }}
                                    </a>
                                </li>
                            @endcan

                            @if($detailActive)
                                <li class="nav-item">
                                    <a class="nav-link active"
                                       id="detail-tab" data-toggle="tab" href="#detailTab" role="tab">
                                        {{ trans('admin/main.import_details') }} #{{ $import->id }}
                                    </a>
                                </li>
                            @endif

                        </ul>

                        <div class="tab-content mt-3">

                            {{-- ==================== LIST TAB ==================== --}}
                            @can('admin_booking_imports')
                                <div class="tab-pane fade {{ (!$createActive && !$detailActive) ? 'active show' : '' }}"
                                     id="listTab" role="tabpanel">

                                    <div class="mb-3">
                                        <a href="{{ getAdminPanelUrl() }}/booking/import/sample"
                                           class="btn btn-outline-secondary btn-sm">
                                            <x-iconsax-lin-document-download class="icons mr-1" width="16px" height="16px"/>
                                            {{ trans('admin/main.download_sample_csv') }}
                                        </a>
                                    </div>

                                    @if(!empty($imports) && $imports->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ trans('admin/main.file_name') }}</th>
                                                        <th>{{ trans('admin/main.type') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.total_rows') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.success_rows') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.failed_rows') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th>{{ trans('admin/main.imported_by') }}</th>
                                                        <th>{{ trans('admin/main.date') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($imports as $item)
                                                        <tr>
                                                            <td>{{ $item->id }}</td>
                                                            <td>{{ $item->file_name }}</td>
                                                            <td>
                                                                <span class="badge badge-secondary text-capitalize">
                                                                    {{ $item->type }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">{{ $item->total_rows }}</td>
                                                            <td class="text-center">
                                                                <span class="text-success font-weight-bold">
                                                                    {{ $item->success_rows }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="{{ $item->failed_rows > 0 ? 'text-danger font-weight-bold' : '' }}">
                                                                    {{ $item->failed_rows }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">
                                                                {!! $item->status_badge !!}
                                                            </td>
                                                            <td>
                                                                @if($item->user)
                                                                    {{ $item->user->name }}
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $item->created_at->format('Y-m-d H:i') }}</td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_imports')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/import/{{ $item->id }}/show"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-eye class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.view') }}</span>
                                                                            </a>
                                                                        @endcan
                                                                        @can('admin_booking_imports_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/import/' . $item->id . '/delete',
                                                                                'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                'btnText'   => trans('admin/main.delete'),
                                                                                'btnIcon'   => 'trash',
                                                                                'iconType'  => 'lin',
                                                                                'iconClass' => 'text-danger mr-2'
                                                                            ])
                                                                        @endcan
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        {{ $imports->links() }}
                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif
                                </div>
                            @endcan

                            {{-- ==================== UPLOAD TAB ==================== --}}
                            @can('admin_booking_imports_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <div class="alert alert-info">
                                                <strong>{{ trans('admin/main.csv_format_note') }}:</strong>
                                                {{ trans('admin/main.csv_columns_info') }}:
                                                <code>title, description, price, capacity, status</code>
                                                <br>
                                                <a href="{{ getAdminPanelUrl() }}/booking/import/sample" class="alert-link">
                                                    {{ trans('admin/main.download_sample_csv') }}
                                                </a>
                                            </div>

                                            <form action="{{ getAdminPanelUrl() }}/booking/import/store"
                                                  method="post" enctype="multipart/form-data">
                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.import_type') }} <span class="text-danger">*</span></label>
                                                    <select name="type" class="form-control @error('type') is-invalid @enderror">
                                                        <option value="bookings" {{ old('type', 'bookings') === 'bookings' ? 'selected' : '' }}>
                                                            {{ trans('admin/main.bookings') }}
                                                        </option>
                                                        <option value="orders" {{ old('type') === 'orders' ? 'selected' : '' }}>
                                                            {{ trans('admin/main.orders') }}
                                                        </option>
                                                    </select>
                                                    @error('type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.csv_file') }} <span class="text-danger">*</span></label>
                                                    <input type="file" name="file" accept=".csv,.txt"
                                                           class="form-control @error('file') is-invalid @enderror">
                                                    <small class="text-muted">
                                                        {{ trans('admin/main.max_file_size') }}: 5MB.
                                                        {{ trans('admin/main.allowed_formats') }}: CSV
                                                    </small>
                                                    @error('file')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <button type="submit" class="btn btn-primary">
                                                    <x-iconsax-lin-import class="icons mr-1" width="16px" height="16px"/>
                                                    {{ trans('admin/main.import_bookings') }}
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                            {{-- ==================== DETAIL TAB ==================== --}}
                            @if($detailActive)
                                <div class="tab-pane fade active show" id="detailTab" role="tabpanel">
                                    <div class="row">

                                        {{-- Summary Card --}}
                                        <div class="col-12 col-md-5">
                                            <div class="card shadow-none border">
                                                <div class="card-header">
                                                    <h4>{{ trans('admin/main.import_summary') }}</h4>
                                                </div>
                                                <div class="card-body">
                                                    <table class="table table-sm">
                                                        <tr>
                                                            <th>{{ trans('admin/main.file_name') }}</th>
                                                            <td>{{ $import->file_name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.type') }}</th>
                                                            <td>
                                                                <span class="badge badge-secondary text-capitalize">
                                                                    {{ $import->type }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.status') }}</th>
                                                            <td>{!! $import->status_badge !!}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.total_rows') }}</th>
                                                            <td>{{ $import->total_rows }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.success_rows') }}</th>
                                                            <td>
                                                                <span class="text-success font-weight-bold">
                                                                    {{ $import->success_rows }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.failed_rows') }}</th>
                                                            <td>
                                                                <span class="{{ $import->failed_rows > 0 ? 'text-danger font-weight-bold' : '' }}">
                                                                    {{ $import->failed_rows }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.imported_by') }}</th>
                                                            <td>{{ $import->user?->name ?? '-' }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>{{ trans('admin/main.date') }}</th>
                                                            <td>{{ $import->created_at->format('Y-m-d H:i:s') }}</td>
                                                        </tr>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Errors Card --}}
                                        @if(!empty($import->errors) && count($import->errors))
                                            <div class="col-12 col-md-7">
                                                <div class="card shadow-none border">
                                                    <div class="card-header">
                                                        <h4 class="text-danger">
                                                            {{ trans('admin/main.failed_rows') }}
                                                            ({{ count($import->errors) }})
                                                        </h4>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                                            <table class="table table-sm font-14 mb-0">
                                                                <thead class="thead-light">
                                                                    <tr>
                                                                        <th>{{ trans('admin/main.row') }}</th>
                                                                        <th>{{ trans('admin/main.error') }}</th>
                                                                        <th>{{ trans('admin/main.data') }}</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach($import->errors as $error)
                                                                        <tr>
                                                                            <td>{{ $error['row'] ?? '-' }}</td>
                                                                            <td class="text-danger">{{ $error['message'] ?? '-' }}</td>
                                                                            <td>
                                                                                @if(!empty($error['data']))
                                                                                    <small class="text-muted">
                                                                                        @foreach($error['data'] as $key => $val)
                                                                                            <span class="mr-2">
                                                                                                <strong>{{ $key }}:</strong> {{ $val }}
                                                                                            </span>
                                                                                        @endforeach
                                                                                    </small>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div class="col-12 col-md-7 d-flex align-items-center">
                                                <div class="alert alert-success w-100">
                                                    <x-iconsax-lin-tick-circle class="icons mr-2" width="18px" height="18px"/>
                                                    {{ trans('admin/main.no_errors_all_rows_imported') }}
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection