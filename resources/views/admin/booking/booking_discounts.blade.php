@extends('admin.layouts.app')

@section('content')
@php $activeTab = !empty($editItem) ? 'form' : 'list'; @endphp
<section class="section">
    <div class="section-header">
        <h1>{{ $pageTitle }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
            <div class="breadcrumb-item">{{ $pageTitle }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="tab-content" id="discountTabsContent">
            <!-- LIST TAB -->
            <div class="tab-pane {{ $activeTab === 'list' ? 'active' : '' }}" id="discount-list">
                <section class="card">
                    <div class="card-body">
                        <form method="get" class="mb-0">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.search') }}</label>
                                        <input type="text" class="form-control text-center" name="search" id="searchInput" value="{{ request()->get('search') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.starts_at') }}</label>
                                        <div class="input-group">
                                            <input type="date" class="text-center form-control" name="from_date" id="fromDate" value="{{ request()->get('from_date') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.expires_at') }}</label>
                                        <div class="input-group">
                                            <input type="date" class="text-center form-control" name="to_date" id="toDate" value="{{ request()->get('to_date') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="submit" class="btn btn-primary btn-block btn-lg">{{ trans('admin/main.show_results') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>

                <div class="row">
                    <div class="col-12 col-md-12">
                        <div class="card">
                            <div class="card-header justify-content-between">
                                <div>
                                    <h5 class="font-14 mb-0">{{ $pageTitle }}</h5>
                                    <p class="font-12 mt-2 mb-0 text-gray-500">{{ trans('admin/main.manage_all_items') ?? 'Manage all booking discounts in a single place' }}</p>
                                </div>
                                <div class="d-flex align-items-center gap-12">
                                    @can('admin_booking_discounts_create')
                                        <a href="#discount-form" onclick="switchTab(event, 'form')" class="btn btn-primary">
                                            <i class="fa fa-plus"></i>
                                            <span class="ml-2 font-12">{{ trans('admin/main.add_new') }}</span>
                                        </a>
                                    @endcan
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table custom-table font-14 text-center">
                                        <tr>
                                            <th>{{ trans('admin/main.title') }}</th>
                                            <th class="text-left">{{ trans('admin/main.booking') }}</th>
                                            <th>{{ trans('admin/main.percentage') }}</th>
                                            <th>{{ trans('admin/main.starts_at') }}</th>
                                            <th>{{ trans('admin/main.expires_at') }}</th>
                                            <th width="150">{{ trans('admin/main.usable_times') }}</th>
                                            <th>{{ trans('admin/main.status') }}</th>
                                            <th>{{ trans('admin/main.actions') }}</th>
                                        </tr>

                                        @forelse($items as $discount)
                                            <tr>
                                                <td>{{ $discount->title }}</td>
                                                <td class="text-left">
                                                    <span class="text-dark">{{ optional($discount->booking)->title ?? '-' }}</span>
                                                </td>
                                                <td>{{ $discount->amount ? $discount->amount . '%' : '-' }}</td>
                                                <td class="font-12">{{ !empty($discount->starts_at) ? date('Y/m/d H:i:s', strtotime($discount->starts_at)) : '-' }}</td>
                                                <td class="font-12">{{ !empty($discount->expires_at) ? date('Y/m/d H:i:s', strtotime($discount->expires_at)) : '-' }}</td>
                                                <td>
                                                    @if(!empty($discount->usage_limit))
                                                        <div class="media-body">
                                                            <div class="mt-0 mb-1">{{ $discount->usage_limit }}</div>
                                                            <div class="text-gray-500 text-small">{{ trans('admin/main.remaining') }}: {{ max(0, $discount->usage_limit - ($discount->used_count ?? 0)) }}</div>
                                                        </div>
                                                    @else
                                                        Unlimited
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($discount->starts_at > now())
                                                        <span class="badge-status text-warning bg-warning-30">{{ trans('admin/main.pending') }}</span>
                                                    @elseif($discount->expires_at < now())
                                                        <span class="badge-status text-danger bg-danger-30">{{ trans('admin/main.expired') }}</span>
                                                    @else
                                                        <span class="{{ $discount->status ? 'badge-status text-success bg-success-30' : 'badge-status text-danger bg-danger-30' }}">{{ $discount->status ? trans('admin/main.active') : trans('admin/main.inactive') }}</span>
                                                    @endif
                                                </td>
                                                <td width="80px">
                                                    <div class="btn-group dropdown table-actions position-relative">
                                                        <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v text-gray-500"></i>
                                                        </button>
                                                        <div class="dropdown-menu dropdown-menu-right">
                                                            @can('admin_booking_discounts_edit')
                                                                {{-- Normal link to the controller's edit($id) route.
                                                                     This causes a full page reload, but the controller already
                                                                     fetches $editItem and the Blade above already auto-selects
                                                                     the "form" tab and pre-fills every field via old()/$editItem.
                                                                     No AJAX or extra JS is required for this to work correctly. --}}
                                                                <a href="{{ getAdminPanelUrl('/booking/discounts/' . $discount->id . '/edit') }}" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                    <i class="fa fa-edit text-gray-500 mr-2"></i>
                                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                </a>
                                                            @endcan
                                                            @can('admin_booking_discounts_delete')
                                                                <a href="{{ getAdminPanelUrl('/booking/discounts/' . $discount->id . '/delete') }}" class="dropdown-item text-danger mb-0 py-3 px-0 font-14" onclick="return confirm('{{ trans('admin/main.delete_confirm_msg') }}');">
                                                                    <i class="fa fa-trash text-danger mr-2"></i>
                                                                    <span>{{ trans('admin/main.delete') }}</span>
                                                                </a>
                                                            @endcan
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center text-gray-500">{{ trans('admin/main.no_result') }}</td>
                                            </tr>
                                        @endforelse
                                    </table>
                                </div>
                            </div>

                            <div class="card-footer text-center">
                                {{ $items->appends(request()->input())->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- FORM TAB -->
            <div class="tab-pane {{ $activeTab === 'form' ? 'active' : '' }}" id="discount-form">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-8 col-lg-6">
                                <form action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/discounts/' . $editItem->id . '/update') : getAdminPanelUrl('/booking/discounts/store') }}" method="POST">
                                    @csrf

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.title') }}</label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $editItem->title ?? '') }}" placeholder="{{ trans('admin/main.name_placeholder') }}" required/>
                                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.booking') }}</label>
                                        <select name="booking_id" class="form-control select2 @error('booking_id') is-invalid @enderror" data-placeholder="Search and Select Booking" required>
                                            <option></option>
                                            @foreach($bookings as $booking)
                                                <option value="{{ $booking->id }}" {{ old('booking_id', $editItem->booking_id ?? '') == $booking->id ? 'selected' : '' }}>{{ $booking->title }}</option>
                                            @endforeach
                                        </select>
                                        @error('booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <!-- <div class="form-group">
                                        <label>{{ trans('admin/main.bundle') }}</label>
                                        <select name="bundle_id" class="form-control select2" data-placeholder="Select Bundle (optional)">
                                            <option></option>
                                            @foreach($bundles as $bundle)
                                                <option value="{{ $bundle->id }}" {{ old('bundle_id', $editItem->bundle_id ?? '') == $bundle->id ? 'selected' : '' }}>{{ $bundle->title }}</option>
                                            @endforeach
                                        </select>
                                    </div> -->

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.discount_percentage') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <div class="input-group-text">
                                                    <i class="fas fa-percentage"></i>
                                                </div>
                                            </div>
                                            <input type="number" name="amount" class="spinner-input form-control text-center @error('amount') is-invalid @enderror" value="{{ old('amount', $editItem->amount ?? '') }}" maxlength="3" min="0" max="100" required/>
                                            @error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.starts_at') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            <input type="text" name="starts_at" class="form-control text-center datetimepicker" autocomplete="off" value="{{ old('starts_at', !empty($editItem->starts_at) ? date('Y-m-d H:i', strtotime($editItem->starts_at)) : '') }}"/>
                                            @error('starts_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.expires_at') }}</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                            </div>
                                            <input type="text" name="expires_at" class="form-control text-center datetimepicker" autocomplete="off" value="{{ old('expires_at', !empty($editItem->expires_at) ? date('Y-m-d H:i', strtotime($editItem->expires_at)) : '') }}"/>
                                            @error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.usage_limit') }}</label>
                                        <input type="number" name="usage_limit" class="form-control text-center @error('usage_limit') is-invalid @enderror" value="{{ old('usage_limit', $editItem->usage_limit ?? '') }}" placeholder="Leave blank for unlimited"/>
                                        @error('usage_limit')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label>{{ trans('admin/main.status') }}</label>
                                        <select name="status" class="form-control custom-select @error('status') is-invalid @enderror">
                                            <option value="1" {{ old('status', $editItem->status ?? true) ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                                            <option value="0" {{ !old('status', $editItem->status ?? true) ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
                                        </select>
                                        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <input type="hidden" name="discount_type" value="percent">

                                    <div class="mt-4">
                                        <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                        @if(!empty($editItem))
                                            <a href="{{ getAdminPanelUrl('/booking/discounts') }}" class="btn btn-secondary ml-2">{{ trans('admin/main.cancel') }}</a>
                                        @endif
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

        // Pure client-side tab switch — used only by "Add New",
        // since both panes already exist in the DOM and no server data is needed.
        function switchTab(event, tab) {
            if (event) event.preventDefault();

            document.querySelectorAll('#discountTabsContent .tab-pane').forEach(function (pane) {
                pane.classList.remove('active');
            });

            document.getElementById(tab === 'list' ? 'discount-list' : 'discount-form').classList.add('active');
        }

        function applyFilters() {
            const search = document.getElementById('searchInput').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            let params = new URLSearchParams();
            if (search) params.append('search', search);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);

            window.location.href = '{{ getAdminPanelUrl('/booking/discounts') }}' + (params.toString() ? '?' + params.toString() : '');
        }
    </script>
@endpush