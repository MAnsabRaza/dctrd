@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $pageTitle }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ $pageTitle }}</div>
        </div>
    </div>

    <div class="section-body">
        @php $activeTab = !empty($editItem) ? 'form' : 'list'; @endphp
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <ul class="nav nav-pills" id="discountTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'list' ? 'active' : '' }}" id="discount-list-tab" data-toggle="pill" href="#discount-list" role="tab" aria-controls="discount-list" aria-selected="{{ $activeTab === 'list' ? 'true' : 'false' }}">
                                        {{ trans('admin/main.list') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'form' ? 'active' : '' }}" id="discount-form-tab" data-toggle="pill" href="#discount-form" role="tab" aria-controls="discount-form" aria-selected="{{ $activeTab === 'form' ? 'true' : 'false' }}">
                                        {{ !empty($editItem) ? trans('admin/main.edit') : trans('admin/main.create') }}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="tab-content" id="discountTabsContent">
                            <div class="tab-pane fade {{ $activeTab === 'list' ? 'show active' : '' }}" id="discount-list" role="tabpanel" aria-labelledby="discount-list-tab">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ trans('admin/main.title') }}</th>
                                                <th>Booking</th>
                                                <th>Bundle</th>
                                                <th>Discount %</th>
                                                <th>{{ trans('admin/main.starts_at') }}</th>
                                                <th>{{ trans('admin/main.expires_at') }}</th>
                                                <th>{{ trans('admin/main.usage_limit') }}</th>
                                                <th>{{ trans('admin/main.status') }}</th>
                                                <th>{{ trans('admin/main.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($items as $item)
                                                <tr>
                                                    <td>{{ $item->id }}</td>
                                                    <td>{{ $item->title }}</td>
                                                    <td>{{ optional($item->booking)->title ?? '-' }}</td>
                                                    <td>{{ optional($item->bundle)->title ?? '-' }}</td>
                                                    <td>{{ number_format($item->amount, 2) }}%</td>
                                                    <td>{{ !empty($item->starts_at) ? date('Y-m-d', strtotime($item->starts_at)) : '-' }}</td>
                                                    <td>{{ !empty($item->expires_at) ? date('Y-m-d', strtotime($item->expires_at)) : '-' }}</td>
                                                    <td>{{ $item->usage_limit ?: '-' }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $item->status ? 'success' : 'secondary' }}">
                                                            {{ $item->status ? trans('admin/main.active') : trans('admin/main.inactive') }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        @can('admin_booking_discounts_edit')
                                                            <a href="{{ getAdminPanelUrl('/booking/discounts/' . $item->id . '/edit') }}" class="btn btn-sm btn-primary">{{ trans('admin/main.edit') }}</a>
                                                        @endcan
                                                        @can('admin_booking_discounts_delete')
                                                            <a href="{{ getAdminPanelUrl('/booking/discounts/' . $item->id . '/delete') }}" class="btn btn-sm btn-danger" onclick="return confirm('{{ trans('admin/main.delete_confirm_msg') }}');">{{ trans('admin/main.delete') }}</a>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="10" class="text-center text-gray-500">{{ trans('admin/main.no_result') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                {{ $items->links() }}
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'form' ? 'show active' : '' }}" id="discount-form" role="tabpanel" aria-labelledby="discount-form-tab">
                                <form method="POST" action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/discounts/' . $editItem->id . '/update') : getAdminPanelUrl('/booking/discounts/store') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.title') }}</label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $editItem->title ?? '') }}" required>
                                    </div>

                                    <div class="form-group">
                                        <label>Booking</label>
                                        <select name="booking_id" class="form-control select2">
                                            <option value="">{{ trans('admin/main.select') }}</option>
                                            @foreach($bookings as $booking)
                                                <option value="{{ $booking->id }}" {{ old('booking_id', $editItem->booking_id ?? '') == $booking->id ? 'selected' : '' }}>{{ $booking->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Bundle</label>
                                        <select name="bundle_id" class="form-control select2">
                                            <option value="">{{ trans('admin/main.select') }}</option>
                                            @foreach($bundles as $bundle)
                                                <option value="{{ $bundle->id }}" {{ old('bundle_id', $editItem->bundle_id ?? '') == $bundle->id ? 'selected' : '' }}>{{ $bundle->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.discount_percentage') }}</label>
                                        <input type="number" name="amount" min="0" max="100" step="0.01" class="form-control" value="{{ old('amount', $editItem->amount ?? '') }}" required>
                                        <small class="form-text text-muted">This value is stored as percentage and discount type is always percent.</small>
                                    </div>

                                    <input type="hidden" name="discount_type" value="percent">

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.starts_at') }}</label>
                                        <input type="date" name="starts_at" class="form-control" value="{{ old('starts_at', !empty($editItem->starts_at) ? date('Y-m-d', strtotime($editItem->starts_at)) : '') }}">
                                    </div>

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.expires_at') }}</label>
                                        <input type="date" name="expires_at" class="form-control" value="{{ old('expires_at', !empty($editItem->expires_at) ? date('Y-m-d', strtotime($editItem->expires_at)) : '') }}">
                                    </div>

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.usage_limit') }}</label>
                                        <input type="number" name="usage_limit" min="1" class="form-control" value="{{ old('usage_limit', $editItem->usage_limit ?? '') }}">
                                    </div>

                                    <div class="form-group form-check">
                                        <input type="checkbox" name="status" id="discountStatus" class="form-check-input" value="1" {{ old('status', $editItem->status ?? true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="discountStatus">{{ trans('admin/main.active') }}</label>
                                    </div>

                                    <button type="submit" class="btn btn-success btn-block">{{ trans('admin/main.save_change') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/select2/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });
        });
    </script>
@endpush
