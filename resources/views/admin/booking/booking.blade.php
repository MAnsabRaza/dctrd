{{-- resources/views/admin/booking/booking.blade.php --}}

@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
    <style>
        .booking-admin-form .booking-section {
            border-top: 1px solid #f1f1f1;
            padding-top: 18px;
            margin-top: 26px;
        }

        .booking-admin-form .booking-section:first-child {
            border-top: 0;
            margin-top: 0;
        }

        .booking-admin-form .booking-section-title {
            border-left: 5px solid #0d8be8;
            color: #34395e;
            font-size: 14px;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 20px;
            padding-left: 12px;
        }

        .booking-admin-form .form-control,
        .booking-admin-form .select2-container--default .select2-selection--single {
            border-color: #edf0f5;
        }

        .booking-admin-form .info-icon {
            align-items: center;
            background: #f4f6f9;
            border-radius: 50%;
            color: #6777ef;
            display: inline-flex;
            font-size: 11px;
            height: 16px;
            justify-content: center;
            margin-left: 5px;
            width: 16px;
        }

        .booking-admin-form .add-button {
            min-width: 42px;
        }

        .booking-location-panel,
        .booking-deposit-panel {
            display: none;
        }

        .booking-location-panel.is-visible,
        .booking-deposit-panel.is-visible {
            display: block;
        }
    </style>
@endpush

@section('content')
    @php
        $bookingPageMode = $bookingPageMode ?? (((!empty($errors) && $errors->any()) || !empty($editBooking) || request()->get('tab') == 'create') ? 'form' : 'list');
        $isFormPage = $bookingPageMode === 'form';
        $inHouseBookings = $inHouseBookings ?? false;

        $bookingListRoute = $inHouseBookings
            ? getAdminPanelUrl() . '/booking/in-house-bookings'
            : getAdminPanelUrl() . '/booking/list';

        $bookingListPageTitle = $inHouseBookings
            ? (trans('update.in-house-bookings') ?? 'In House Bookings')
            : trans('admin/main.booking_list');
    @endphp
    <section class="section">
        <div class="section-header">
            <h1>{{ $isFormPage ? (!empty($editBooking) ? 'Edit Booking' : 'New Booking') : $bookingListPageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">Bookings</div>
                @if($isFormPage)
                    <div class="breadcrumb-item">{{ !empty($editBooking) ? 'Edit' : 'New' }}</div>
                @else
                    <div class="breadcrumb-item">{{ $bookingListPageTitle }}</div>
                @endif
            </div>
        </div>

        <div class="section-body">

            @if(!$isFormPage)

                {{-- ==================== STAT CARDS (own row, no outer card wrapper) ==================== --}}
                <div class="row">
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card-statistic">
                            <div class="card-statistic__mask"></div>
                            <div class="card-statistic__wrap">
                                <div class="d-flex align-items-start justify-content-between">
                                    <span class="text-gray-500 mt-8">Total Bookings</span>
                                    <div class="d-flex-center size-48 bg-success-30 rounded-12">
                                        <x-iconsax-bul-document-download class="icons text-success" width="24px" height="24px"/>
                                    </div>
                                </div>
                                <h5 class="font-24 mt-12 line-height-1 text-black">{{ $totalBookings ?? 0 }}</h5>
                                <span class="text-gray-500 font-14">{{ trans('admin/main.sales') }}: {{ $totalBookingSales ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card-statistic">
                            <div class="card-statistic__mask"></div>
                            <div class="card-statistic__wrap">
                                <div class="d-flex align-items-start justify-content-between">
                                    <span class="text-gray-500 mt-8">Total Booking Sellers</span>
                                    <div class="d-flex-center size-48 bg-danger-30 rounded-12">
                                        <x-iconsax-bul-shop class="icons text-danger" width="24px" height="24px"/>
                                    </div>
                                </div>
                                <h5 class="font-24 mt-12 line-height-1 text-black">{{ $totalBookingSellers ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6 col-sm-6 col-12">
                        <div class="card-statistic">
                            <div class="card-statistic__mask"></div>
                            <div class="card-statistic__wrap">
                                <div class="d-flex align-items-start justify-content-between">
                                    <span class="text-gray-500 mt-8">Total Booking Customers</span>
                                    <div class="d-flex-center size-48 bg-secondary-30 rounded-12">
                                        <x-iconsax-bul-profile-2user class="icons text-secondary" width="24px" height="24px"/>
                                    </div>
                                </div>
                                <h5 class="font-24 mt-12 line-height-1 text-black">{{ $totalBookingCustomers ?? 0 }}</h5>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ==================== FILTER FORM (its own separate card) ==================== --}}
                <section class="card mt-32">
                    <div class="card-body pb-4">
                        <form action="{{ $bookingListRoute }}" method="get" class="mb-0">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.search') }}</label>
                                        <input name="title" type="text" class="form-control" value="{{ request()->get('title') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                        <input type="date" id="from" class="text-center form-control" name="from" value="{{ request()->get('from') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                        <input type="date" id="to" class="text-center form-control" name="to" value="{{ request()->get('to') }}">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.filters') }}</label>
                                        <select name="sort" data-plugin-selectTwo class="form-control populate">
                                            <option value="">{{ trans('admin/main.filter_type') }}</option>
                                            <option value="sales_asc" @if(request()->get('sort') == 'sales_asc') selected @endif>{{ trans('admin/main.sales_ascending') }}</option>
                                            <option value="sales_desc" @if(request()->get('sort') == 'sales_desc') selected @endif>{{ trans('admin/main.sales_descending') }}</option>
                                            <option value="price_asc" @if(request()->get('sort') == 'price_asc') selected @endif>{{ trans('admin/main.Price_ascending') }}</option>
                                            <option value="price_desc" @if(request()->get('sort') == 'price_desc') selected @endif>{{ trans('admin/main.Price_descending') }}</option>
                                            <option value="income_asc" @if(request()->get('sort') == 'income_asc') selected @endif>{{ trans('admin/main.Income_ascending') }}</option>
                                            <option value="income_desc" @if(request()->get('sort') == 'income_desc') selected @endif>{{ trans('admin/main.Income_descending') }}</option>
                                            <option value="created_at_asc" @if(request()->get('sort') == 'created_at_asc') selected @endif>{{ trans('admin/main.create_date_ascending') }}</option>
                                            <option value="created_at_desc" @if(request()->get('sort') == 'created_at_desc') selected @endif>{{ trans('admin/main.create_date_descending') }}</option>
                                            <option value="updated_at_asc" @if(request()->get('sort') == 'updated_at_asc') selected @endif>{{ trans('admin/main.update_date_ascending') }}</option>
                                            <option value="updated_at_desc" @if(request()->get('sort') == 'updated_at_desc') selected @endif>{{ trans('admin/main.update_date_descending') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('update.seller') }}</label>
                                        <select name="creator_ids[]" multiple="multiple" class="form-control search-user-select2" data-placeholder="{{ trans('update.search_seller') }}">
                                            @if(!empty($teachers) and $teachers->count() > 0)
                                                @foreach($teachers as $teacher)
                                                    <option value="{{ $teacher->id }}" selected>{{ $teacher->full_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.category') }}</label>
                                        <select name="category_id" data-plugin-selectTwo class="form-control populate">
                                            <option value="">{{ trans('admin/main.all_categories') }}</option>
                                            @foreach($productCategories ?? [] as $productCategory)
                                                @if(!empty($productCategory->children) and $productCategory->children->count() > 0)
                                                    <optgroup label="{{ $productCategory->title }}">
                                                        @foreach($productCategory->children as $subCategory)
                                                            <option value="{{ $subCategory->id }}" @if(request()->get('category_id') == $subCategory->id) selected @endif>{{ $subCategory->title }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @else
                                                    <option value="{{ $productCategory->id }}" @if(request()->get('category_id') == $productCategory->id) selected @endif>{{ $productCategory->title }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.status') }}</label>
                                        <select name="status" data-plugin-selectTwo class="form-control populate">
                                            <option value="">{{ trans('admin/main.all_status') }}</option>
                                            <option value="draft" @if(request()->get('status') == 'draft') selected @endif>{{ trans('admin/main.draft') }}</option>
                                            <option value="pending" @if(request()->get('status') == 'pending') selected @endif>{{ trans('admin/main.pending') }}</option>
                                            <option value="published" @if(request()->get('status') == 'published') selected @endif>{{ trans('admin/main.published') }}</option>
                                            <option value="rejected" @if(request()->get('status') == 'rejected') selected @endif>{{ trans('public.rejected') }}</option>
                                            <option value="inactive" @if(request()->get('status') == 'inactive') selected @endif>{{ trans('admin/main.inactive') }}</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 d-flex align-items-center">
                                    <button type="submit" class="btn btn-primary btn-block btn-lg">{{ trans('admin/main.show_results') }}</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </section>

                {{-- ==================== BOOKING LIST TABLE (its own separate card) ==================== --}}
                <div class="card mt-32">
                    <div class="card-header justify-content-between">
                        <div>
                            <h5 class="font-14 mb-0">{{ $bookingListPageTitle }}</h5>
                            <p class="font-12 mt-4 mb-0 text-gray-500">Manage all booking services in your store.</p>
                        </div>
                        <div class="d-flex align-items-center gap-12">
                            @can('admin_booking')
                                <a href="{{ getAdminPanelUrl() }}/booking/excel?{{ http_build_query(array_merge(request()->all(), ['in_house_bookings' => $inHouseBookings])) }}" class="btn bg-white bg-hover-gray-100 border-gray-400 text-gray-500">
                                    <x-iconsax-lin-import-2 class="icons text-gray-500" width="18px" height="18px"/>
                                    <span class="ml-4 font-12">{{ trans('admin/main.export_xls') }}</span>
                                </a>
                            @endcan
                            @if($inHouseBookings)
                                @can('admin_booking_create')
                                    <a href="{{ getAdminPanelUrl() }}/booking" class="btn btn-primary">
                                        <x-iconsax-lin-add class="icons text-white" width="18px" height="18px"/>
                                        <span class="ml-4 font-12">{{ trans('admin/main.create_booking') }}</span>
                                    </a>
                                @endcan
                            @else
                                <a href="{{ getAdminPanelUrl() }}/booking" class="btn btn-primary">
                                    <x-iconsax-lin-add class="icons text-white" width="18px" height="18px"/>
                                    <span class="ml-4 font-12">{{ trans('admin/main.create_booking') }}</span>
                                </a>
                            @endif
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table custom-table font-14">
                                <tr>
                                    <th>{{ trans('admin/main.id') }}</th>
                                    <th class="text-left">{{ trans('admin/main.title') }}</th>
                                    <th class="text-left">{{ trans('admin/main.creator') }}</th>
                                    <th>{{ trans('admin/main.booking_type') }}</th>
                                    <th>{{ trans('admin/main.price') }}</th>
                                    <th>{{ trans('admin/main.sales') }}</th>
                                    <th>{{ trans('admin/main.income') }}</th>
                                    <th>{{ trans('admin/main.updated_at') }}</th>
                                    <th>{{ trans('admin/main.created_at') }}</th>
                                    <th>{{ trans('admin/main.status') }}</th>
                                    <th>{{ trans('admin/main.actions') }}</th>
                                </tr>
                                @forelse($bookings as $booking)
                                    <tr class="text-center">
                                        <td>{{ $booking->id }}</td>
                                        <td width="22%" class="text-left">
                                            <a class="text-dark mt-0 mb-1 font-weight-bold" href="{{ $booking->getUrl() ?? '#' }}" target="_blank">{{ $booking->title }}</a>
                                            @if(!empty($booking->category->title))
                                                <div class="text-small text-gray-500">{{ $booking->category->title }}</div>
                                            @else
                                                <div class="text-small text-warning">{{ trans('admin/main.no_category') }}</div>
                                            @endif
                                        </td>
                                        <td class="text-left">{{ optional($booking->creator)->full_name ?? '-' }}</td>
                                        <td>{{ ucfirst($booking->booking_type) }}</td>
                                        <td>
                                            @if($booking->discount_price && $booking->discount_price < $booking->price)
                                                <span class="text-gray-500" style="text-decoration: line-through">{{ $booking->currency }} {{ number_format($booking->price, 2) }}</span>
                                                <span class="d-block font-weight-bold">{{ $booking->currency }} {{ number_format($booking->discount_price, 2) }}</span>
                                            @else
                                                <span class="font-weight-bold">{{ $booking->currency }} {{ number_format($booking->price, 2) }}</span>
                                            @endif
                                        </td>
                                        <td><span class="font-weight-bold">{{ $booking->sales_count ?? 0 }}</span></td>
                                        <td>{{ $booking->currency }} {{ number_format((float) ($booking->booking_income ?? 0), 2) }}</td>
                                        <td>{{ dateTimeFormat($booking->updated_at, 'Y M j | H:i') }}</td>
                                        <td>{{ dateTimeFormat($booking->created_at, 'Y M j | H:i') }}</td>
                                        <td>
                                            @switch($booking->status)
                                                @case('published')
                                                    <span class="badge-status text-success bg-success-30">{{ trans('admin/main.published') }}</span>
                                                    @break
                                                @case('pending')
                                                    <span class="badge-status text-warning bg-warning-30">{{ trans('admin/main.pending') }}</span>
                                                    @break
                                                @case('rejected')
                                                @case('inactive')
                                                    <span class="badge-status text-danger bg-danger-30">{{ trans('public.rejected') }}</span>
                                                    @break
                                                @default
                                                    <span class="badge-status text-dark bg-dark-30">{{ trans('admin/main.draft') }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div class="btn-group dropdown table-actions position-relative">
                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                    <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-right">
                                                    @can('admin_booking_edit')
                                                        <a href="{{ getAdminPanelUrl() }}/booking/{{ $booking->id }}/edit" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                            <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                            <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                        </a>
                                                    @endcan
                                                    @can('admin_booking_delete')
                                                        <a href="#" data-href="{{ getAdminPanelUrl() }}/booking/{{ $booking->id }}/delete" data-toggle="modal" data-target="#deleteModal" class="dropdown-item d-flex align-items-center text-danger mb-0 py-3 px-0 font-14">
                                                            <x-iconsax-lin-trash class="icons text-danger mr-2" width="18px" height="18px"/>
                                                            <span>{{ trans('admin/main.delete') }}</span>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-gray-500 py-4">{{ trans('admin/main.no_result') }}</td>
                                    </tr>
                                @endforelse
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        {{ $bookings->appends(request()->input())->links() }}
                    </div>
                </div>

            @else

                {{-- ==================== FORM PAGE (new / edit booking) — kept in its own card ==================== --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                @include('admin.booking.partials.new_booking_form')
                            </div>
                        </div>
                    </div>
                </div>

            @endif

        </div>
    </section>

    {{-- ==================== DELETE MODAL ==================== --}}
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger mr-2"></i>
                        {{ trans('admin/main.delete_confirm') }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    {{ trans('admin/main.delete_confirm_msg') ?? 'Are you sure you want to delete this booking? This action cannot be undone.' }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        {{ trans('admin/main.cancel') }}
                    </button>
                    {{-- GET route hai isliye simple anchor tag — no form needed --}}
                    <a id="deleteConfirmBtn" href="#" class="btn btn-danger">
                        <i class="fas fa-trash mr-1"></i>
                        {{ trans('admin/main.delete') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script src="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
    <script>
        $(document).ready(function () {

            // ─── Summernote init ────────────────────────────────────────
            $('.summernote').summernote({
                height: 200,
                toolbar: [
                    ['style', ['bold', 'italic', 'underline', 'clear']],
                    ['para',  ['ul', 'ol', 'paragraph']],
                    ['insert',['link']],
                    ['view',  ['codeview']]
                ]
            });

            // ─── FIX 2: Delete Modal — anchor href set karo ─────────────
            $('#deleteModal').on('show.bs.modal', function (event) {
                var trigger   = $(event.relatedTarget);
                var deleteUrl = trigger.data('href');
                $('#deleteConfirmBtn').attr('href', deleteUrl);
            });

            // ─── Modal close pe href clear karo ─────────────────────────
            $('#deleteModal').on('hidden.bs.modal', function () {
                $('#deleteConfirmBtn').attr('href', '#');
            });

            function slugify(value) {
                return value.toString().toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9\s-]/g, '')
                    .replace(/\s+/g, '-')
                    .replace(/-+/g, '-');
            }

            $('#newBookingTitle').on('input', function () {
                var slugInput = $('#newBookingSlug');

                if (!slugInput.val() || slugInput.data('auto-generated')) {
                    slugInput.val(slugify($(this).val())).data('auto-generated', true);
                }
            });

            $('#newBookingSlug').on('input', function () {
                $(this).data('auto-generated', false);
            });

            function togglePanel(switchSelector, panelSelector) {
                var isChecked = $(switchSelector).is(':checked');
                $(panelSelector).toggleClass('is-visible', isChecked);
                $(panelSelector).css('display', isChecked ? '' : 'none');
            }

            togglePanel('#newBookingLocationSwitch', '#newBookingLocationPanel');
            togglePanel('#booking_deposit_enabled', '#bookingDepositPanel');

            $('#newBookingLocationSwitch').on('change', function () {
                togglePanel('#newBookingLocationSwitch', '#newBookingLocationPanel');
            });

            $('#booking_deposit_enabled').on('change', function () {
                togglePanel('#booking_deposit_enabled', '#bookingDepositPanel');
            });

            function syncCategoryWithBookingType() {
                var selectedType = $('#bookingTypeSelect').find('option:selected');
                var parentId = selectedType.data('parent-id');

                if (parentId) {
                    var categorySelect = $('#bookingCategorySelect');
                    categorySelect.val(parentId).trigger('change');
                    categorySelect.trigger('change.select2');
                }
            }

            $('#bookingTypeSelect').on('change', function () {
                syncCategoryWithBookingType();
            });

            // Initialize on load if booking type already selected
            syncCategoryWithBookingType();

            $('input[name="tags"]').on('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    var value = $(this).val().trim();

                    if (value && value.slice(-1) !== ',') {
                        $(this).val(value + ', ');
                    }
                }
            });

        });

        // ─── Location fields toggle ──────────────────────────────────────
        function toggleLocation(show) {
            var panel = document.getElementById('newBookingLocationPanel') || document.getElementById('locationFields');
            if (panel) {
                panel.style.display = show ? '' : 'none';
            }
        }
    </script>
@endpush