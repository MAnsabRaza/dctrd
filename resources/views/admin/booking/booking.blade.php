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
    @endphp
    <section class="section">
        <div class="section-header">
            <h1>{{ $isFormPage ? (!empty($editBooking) ? 'Edit Booking' : 'New Booking') : trans('admin/main.booking_list') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">Bookings</div>
                @if($isFormPage)
                    <div class="breadcrumb-item">{{ !empty($editBooking) ? 'Edit' : 'New' }}</div>
                @else
                    <div class="breadcrumb-item">{{ trans('admin/main.booking_list') }}</div>
                @endif
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @if(!$isFormPage)
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

                                <section class="card mt-32">
                                    <div class="card-body pb-4">
                                        <form action="{{ getAdminPanelUrl() }}/booking/list" method="get" class="mb-0">
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
                                                            @foreach($categories ?? [] as $category)
                                                                <option value="{{ $category->id }}" @if(request()->get('category_id') == $category->id) selected @endif>{{ $category->title }}</option>
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

                                <div class="card mt-32">
                                    <div class="card-header justify-content-between">
                                        <div>
                                            <h5 class="font-14 mb-0">{{ trans('admin/main.booking_list') }}</h5>
                                            <p class="font-12 mt-4 mb-0 text-gray-500">Manage all booking services in your store.</p>
                                        </div>
                                        <div class="d-flex align-items-center gap-12">
                                            <a href="{{ getAdminPanelUrl() }}/booking/excel?{{ http_build_query(request()->all()) }}" class="btn bg-white bg-hover-gray-100 border-gray-400 text-gray-500">
                                                <x-iconsax-lin-import-2 class="icons text-gray-500" width="18px" height="18px"/>
                                                <span class="ml-4 font-12">{{ trans('admin/main.export_xls') }}</span>
                                            </a>
                                            <a href="{{ getAdminPanelUrl() }}/booking" class="btn btn-primary">
                                                <x-iconsax-lin-add class="icons text-white" width="18px" height="18px"/>
                                                <span class="ml-4 font-12">{{ trans('admin/main.create_booking') }}</span>
                                            </a>
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

                                @include('admin.booking.partials.new_booking_form')

                                    @if(false)
                                    <form action="{{ getAdminPanelUrl() }}/booking/{{ !empty($editBooking) ? $editBooking->id . '/update' : 'store' }}"
                                          method="POST" class="d-none">
                                        {{ csrf_field() }}

                                        <div class="row">
                                            <div class="col-12 col-md-6">

                                                {{-- Language --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('auth.language') }}</label>
                                                    <select name="language" data-plugin-selectTwo
                                                            class="form-control @error('language') is-invalid @enderror">
                                                        @foreach($userLanguages ?? [app()->getLocale() => ucfirst(app()->getLocale())] as $lang => $language)
                                                            <option value="{{ $lang }}"
                                                                {{ old('language', !empty($editBooking) ? $editBooking->language : app()->getLocale()) == $lang ? 'selected' : '' }}>
                                                                {{ $language }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('language')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Title --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.title') }} <span class="text-danger">*</span></label>
                                                    <input type="text" name="title"
                                                           class="form-control @error('title') is-invalid @enderror"
                                                           value="{{ !empty($editBooking) ? $editBooking->title : old('title') }}"
                                                           placeholder="{{ trans('admin/main.choose_title') }}">
                                                    @error('title')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Slug --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.url') }} / Slug</label>
                                                    <input type="text" name="slug"
                                                           class="form-control @error('slug') is-invalid @enderror"
                                                           value="{{ !empty($editBooking) ? $editBooking->slug : old('slug') }}"
                                                           placeholder="auto-generated-if-empty">
                                                    <div class="text-gray-500 text-small mt-1">{{ trans('update.leave_empty_for_auto_generation') }}</div>
                                                    @error('slug')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Category --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.category') }}</label>
                                                    <select name="category_id" data-plugin-selectTwo
                                                            class="form-control @error('category_id') is-invalid @enderror">
                                                        <option value="">— {{ trans('admin/main.choose_category') }} —</option>
                                                        @foreach($allCategories ?? [] as $cat)
                                                            <option value="{{ $cat->id }}"
                                                                {{ (!empty($editBooking) && $editBooking->category_id == $cat->id) || old('category_id') == $cat->id ? 'selected' : '' }}>
                                                                {{ $cat->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Booking Type --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.booking_type') }} <span class="text-danger">*</span></label>
                                                    <select name="booking_type" data-plugin-selectTwo
                                                            class="form-control @error('booking_type') is-invalid @enderror">
                                                        <option value="">— {{ trans('admin/main.select_type') }} —</option>
                                                        @foreach(['tour','activity','rental','event','service','accommodation'] as $type)
                                                            <option value="{{ $type }}"
                                                                {{ (!empty($editBooking) && $editBooking->booking_type === $type) || old('booking_type') === $type ? 'selected' : '' }}>
                                                                {{ ucfirst($type) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Sub Type --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.sub_type') }}</label>
                                                    <input type="text" name="sub_type" class="form-control"
                                                           value="{{ !empty($editBooking) ? $editBooking->sub_type : old('sub_type') }}"
                                                           placeholder="{{ trans('admin/main.sub_type') }}">
                                                </div>

                                                {{-- Requirements --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.requirements') }}</label>
                                                    <input type="text" name="requirements" class="form-control"
                                                           value="{{ !empty($editBooking) ? $editBooking->requirements : old('requirements') }}"
                                                           placeholder="{{ trans('admin/main.requirements') }}">
                                                </div>

                                                {{-- Price --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.price') }} <span class="text-danger">*</span></label>
                                                    <input type="number" name="price" step="0.01" min="0"
                                                           class="form-control @error('price') is-invalid @enderror"
                                                           value="{{ !empty($editBooking) ? $editBooking->price : old('price') }}"
                                                           placeholder="0.00">
                                                    @error('price')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Discount Price --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.discount_price') }}</label>
                                                    <input type="number" name="discount_price" step="0.01" min="0"
                                                           class="form-control"
                                                           value="{{ !empty($editBooking) ? $editBooking->discount_price : old('discount_price') }}"
                                                           placeholder="0.00">
                                                </div>

                                                {{-- Currency --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.currency') }}</label>
                                                    <select name="currency" data-plugin-selectTwo class="form-control">
                                                        @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                                                            <option value="{{ $cur }}"
                                                                {{ (!empty($editBooking) && $editBooking->currency === $cur) || old('currency', 'USD') === $cur ? 'selected' : '' }}>
                                                                {{ $cur }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                {{-- Price Per --}}
                                                <div class="form-group">
                                                    <label class="input-label">
                                                        {{ trans('admin/main.price_per') }}
                                                        <small class="text-muted">({{ trans('admin/main.numeric_value') }})</small>
                                                    </label>
                                                    <input type="number" name="price_per" step="0.01" min="0"
                                                           class="form-control @error('price_per') is-invalid @enderror"
                                                           value="{{ !empty($editBooking) ? $editBooking->price_per : old('price_per') }}"
                                                           placeholder="e.g. 1.00">
                                                    @error('price_per')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Price Unit --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.price_unit_label') }}</label>
                                                    <input type="text" name="price_unit" class="form-control"
                                                           value="{{ !empty($editBooking) ? $editBooking->price_unit : old('price_unit') }}"
                                                           placeholder="e.g. per night, per adult">
                                                </div>
                                                 <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.checkout_message') }}</label>
                                                    <textarea name="checkout_message" class="form-control"
                                                           placeholder="Checkout message">{{ !empty($editBooking) ? $editBooking->checkout_message : old('checkout_message') }}</textarea>
                                                </div>
                                                     <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.reviewer_message') }}</label>
                                                    <textarea name="reviewer_message" class="form-control"
                                                           placeholder="Reviewer message">{{ !empty($editBooking) ? $editBooking->reviewer_message : old('reviewer_message') }}</textarea>
                                                </div>
                                                    

                                            </div>{{-- col-md-6 left --}}

                                           
                                            {{-- RIGHT COLUMN --}}
                                            <div class="col-12 col-md-6">

                                                {{-- Capacity --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.capacity') }}</label>
                                                    <input type="number" name="capacity" min="1"
                                                           class="form-control"
                                                           value="{{ !empty($editBooking) ? $editBooking->capacity : old('capacity') }}"
                                                           placeholder="Leave empty for unlimited">
                                                </div>

                                                <div class="row">
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ trans('admin/main.min_persons') }}</label>
                                                            <input type="number" name="min_persons" min="1"
                                                                   class="form-control"
                                                                   value="{{ !empty($editBooking) ? $editBooking->min_persons : old('min_persons', 1) }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="form-group">
                                                            <label class="input-label">{{ trans('admin/main.max_persons') }}</label>
                                                            <input type="number" name="max_persons" min="1"
                                                                   class="form-control"
                                                                   value="{{ !empty($editBooking) ? $editBooking->max_persons : old('max_persons') }}"
                                                                   placeholder="No limit">
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Duration --}}
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.duration_minutes') }}</label>
                                                    <input type="number" name="duration_minutes" min="1"
                                                           class="form-control"
                                                           value="{{ !empty($editBooking) ? $editBooking->duration_minutes : old('duration_minutes') }}"
                                                           placeholder="Minutes — e.g. 60">
                                                </div>

                                                {{-- Location Toggle --}}
                                                <div class="form-group mt-20 d-flex align-items-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="location_enabled"
                                                               id="locationSwitch" value="on"
                                                               class="custom-control-input"
                                                               {{ (!empty($editBooking) && $editBooking->location_enabled) ? 'checked' : '' }}
                                                               onchange="toggleLocation(this.checked)">
                                                        <label class="custom-control-label" for="locationSwitch"></label>
                                                    </div>
                                                    <label for="locationSwitch" class="mb-0 ml-2">{{ trans('admin/main.enable_location') }}</label>
                                                </div>

                                                {{-- Location Fields --}}
                                                <div id="locationFields" style="{{ (!empty($editBooking) && $editBooking->location_enabled) ? '' : 'display:none' }}">
                                                    @php $locationModel = $editBooking ?? null; @endphp
                                                    @include('partials._location_picker', [
                                                        'locationModel' => $locationModel,
                                                        'addressName' => 'address_line',
                                                        'showAjaxSave' => false,
                                                        'pickerId' => 'adminBookingLocationPicker'
                                                    ])
                                                </div>

                                            </div>{{-- col-md-6 right --}}
                                        </div>{{-- row --}}

                                        {{-- Description --}}
                                        <div class="form-group mt-15">
                                            <label class="input-label">{{ trans('admin/main.description') }}</label>
                                            <textarea name="description" rows="4"
                                                      class="summernote form-control @error('description') is-invalid @enderror">{{ !empty($editBooking) ? $editBooking->description : old('description') }}</textarea>
                                            @error('description')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        {{-- Featured Switch --}}
                                        <div class="form-group mt-30 d-flex align-items-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" name="featured" id="featuredSwitch" value="on"
                                                       class="custom-control-input"
                                                       {{ (!empty($editBooking) && $editBooking->featured) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="featuredSwitch"></label>
                                            </div>
                                            <label for="featuredSwitch" class="mb-0 ml-2">{{ trans('admin/main.featured') }}</label>
                                        </div>

                                        {{-- Status Switch --}}
                                        <div class="form-group mt-15 d-flex align-items-center">
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox" name="status" id="statusSwitch" value="published"
                                                       class="custom-control-input"
                                                       {{ (!empty($editBooking) && $editBooking->status === 'published') || (!isset($editBooking) && old('status', 'published') === 'published') ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="statusSwitch"></label>
                                            </div>
                                            <label for="statusSwitch" class="mb-0 ml-2">{{ trans('admin/main.publish') }}</label>
                                        </div>

                                        <button type="submit" class="btn btn-primary mt-3">
                                            <i class="fas fa-save mr-1"></i>
                                            {{ trans('admin/main.save_change') }}
                                        </button>

                                    </form>
                                    @endif
                            @endif
                        </div>{{-- card-body --}}
                    </div>{{-- card --}}
                </div>
            </div>
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
