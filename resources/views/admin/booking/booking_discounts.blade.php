@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header d-flex justify-content-between align-items-center">
        <div>
            <h1>{{ $pageTitle }}</h1>
        </div>
        @if($activeTab === 'list')
            <a href="#discount-form" data-toggle="pill" class="btn btn-primary">
                <i class="fa fa-plus"></i> {{ trans('admin/main.add_new') }}
            </a>
        @endif
    </div>
    <div class="section-header-breadcrumb">
        <div class="breadcrumb-item active">
            <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
        </div>
        <div class="breadcrumb-item">{{ $pageTitle }}</div>
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
                                
                                <!-- Search & Filter Section -->
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <input type="text" class="form-control" placeholder="{{ trans('admin/main.search') }}" id="searchInput">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" placeholder="From Expiry Date" id="fromDate">
                                    </div>
                                    <div class="col-md-3">
                                        <input type="date" class="form-control" placeholder="To Expiry Date" id="toDate">
                                    </div>
                                    <div class="col-md-2">
                                        <button class="btn btn-primary btn-block" onclick="applyFilters()">{{ trans('admin/main.search') }}</button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-hover">
                                        <thead>
                                            <tr>
                                                <th>{{ trans('admin/main.title') }}</th>
                                                <th>Booking</th>
                                                <th>Bundle</th>
                                                <th>Percentage</th>
                                                <th>{{ trans('admin/main.starts_at') }}</th>
                                                <th>{{ trans('admin/main.expires_at') }}</th>
                                                <th>Usable Times</th>
                                                <th>{{ trans('admin/main.status') }}</th>
                                                <th>{{ trans('admin/main.actions') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($items as $item)
                                                <tr>
                                                    <td><strong>{{ $item->title }}</strong></td>
                                                    <td>{{ optional($item->booking)->title ?? '-' }}</td>
                                                    <td>{{ optional($item->bundle)->title ?? '-' }}</td>
                                                    <td>{{ number_format($item->amount, 2) }}%</td>
                                                    <td>{{ !empty($item->starts_at) ? date('Y-m-d H:i:s', strtotime($item->starts_at)) : '-' }}</td>
                                                    <td>{{ !empty($item->expires_at) ? date('Y-m-d H:i:s', strtotime($item->expires_at)) : '-' }}</td>
                                                    <td>
                                                        @if($item->usage_limit)
                                                            <span class="badge badge-warning">{{ trans('admin/main.remaining') }}: {{ max(0, $item->usage_limit - ($item->used_count ?? 0)) }}</span>
                                                        @else
                                                            <span class="badge badge-info">Unlimited</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-{{ $item->status ? 'success' : 'danger' }}">
                                                            {{ $item->status ? trans('admin/main.active') : 'Expired' }}
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
                                                    <td colspan="9" class="text-center text-gray-500">{{ trans('admin/main.no_result') }}</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-center mt-3">
                                    {{ $items->links() }}
                                </div>
                            </div>

                            <div class="tab-pane fade {{ $activeTab === 'form' ? 'show active' : '' }}" id="discount-form" role="tabpanel" aria-labelledby="discount-form-tab">
                                <form method="POST" action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/discounts/' . $editItem->id . '/update') : getAdminPanelUrl('/booking/discounts/store') }}">
                                    @csrf

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.title') }} <span class="text-danger">*</span></label>
                                        <input type="text" name="title" class="form-control" value="{{ old('title', $editItem->title ?? '') }}" placeholder="Enter a unique title..." required>
                                        @error('title')<div class="text-danger text-small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Booking / Product <span class="text-danger">*</span></label>
                                        <select name="booking_id" class="form-control select2">
                                            <option value="">Search and Select Booking</option>
                                            @foreach($bookings as $booking)
                                                <option value="{{ $booking->id }}" {{ old('booking_id', $editItem->booking_id ?? '') == $booking->id ? 'selected' : '' }}>{{ $booking->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('booking_id')<div class="text-danger text-small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label>Bundle</label>
                                        <select name="bundle_id" class="form-control select2">
                                            <option value="">Select Bundle (optional)</option>
                                            @foreach($bundles as $bundle)
                                                <option value="{{ $bundle->id }}" {{ old('bundle_id', $editItem->bundle_id ?? '') == $bundle->id ? 'selected' : '' }}>{{ $bundle->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Discount Percentage <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="number" name="amount" min="0" max="100" step="0.01" class="form-control" value="{{ old('amount', $editItem->amount ?? '') }}" placeholder="0" required>
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                        @error('amount')<div class="text-danger text-small mt-1">{{ $message }}</div>@enderror
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
                                        <input type="number" name="usage_limit" min="1" class="form-control" value="{{ old('usage_limit', $editItem->usage_limit ?? '') }}" placeholder="Leave blank for unlimited">
                                    </div>

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.status') }}</label>
                                        <select name="status" class="form-control">
                                            <option value="1" {{ old('status', $editItem->status ?? true) ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !old('status', $editItem->status ?? true) ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>

                                    <div class="form-group mt-4">
                                        <button type="submit" class="btn btn-success btn-block">{{ trans('admin/main.save_change') }}</button>
                                    </div>
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

        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            
            // Build query string
            let params = new URLSearchParams();
            if (search) params.append('search', search);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);
            
            // Redirect with filters
            window.location.href = '{{ getAdminPanelUrl('/booking/discounts') }}' + (params.toString() ? '?' + params.toString() : '');
        }
    </script>
@endpush
