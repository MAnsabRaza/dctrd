{{-- resources/views/admin/booking/booking.blade.php --}}

@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.booking') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.booking') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                $createActive = ((!empty($errors) && $errors->any()) || !empty($editBooking) || request()->get('tab') == 'create');
                            @endphp

                            <ul class="nav nav-pills" id="bookingTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab"
                                       role="tab" aria-controls="listTab" aria-selected="{{ $createActive ? 'false' : 'true' }}">
                                        <i class="fas fa-list mr-1"></i>
                                        {{ trans('admin/main.booking_list') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab"
                                       role="tab" aria-controls="createTab" aria-selected="{{ $createActive ? 'true' : 'false' }}">
                                        <i class="fas fa-plus mr-1"></i>
                                        {{ trans('admin/main.create_booking') }}
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content mt-4" id="bookingTabsContent">

                                {{-- ==================== LIST TAB ==================== --}}
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel" aria-labelledby="list-tab">

                                    {{-- Search Filters --}}
                                    <form action="{{ getAdminPanelUrl() }}/booking" method="get" class="mb-4">
                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.search') }}</label>
                                                    <input name="title" type="text" class="form-control"
                                                           value="{{ request()->get('title') }}"
                                                           placeholder="{{ trans('admin/main.search_by_title') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.category') }}</label>
                                                    <select name="category_id" data-plugin-selectTwo class="form-control">
                                                        <option value="">{{ trans('admin/main.all_categories') }}</option>
                                                        @foreach($categories ?? [] as $category)
                                                            <option value="{{ $category->id }}"
                                                                    @if(request()->get('category_id') == $category->id) selected @endif>
                                                                {{ $category->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.booking_type') }}</label>
                                                    <select name="booking_type" data-plugin-selectTwo class="form-control">
                                                        <option value="">{{ trans('admin/main.all_types') }}</option>
                                                        @foreach(['tour','activity','rental','event','service','accommodation'] as $type)
                                                            <option value="{{ $type }}"
                                                                    @if(request()->get('booking_type') == $type) selected @endif>
                                                                {{ ucfirst($type) }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-2 d-flex align-items-center">
                                                <button type="submit" class="btn btn-primary btn-block">
                                                    <i class="fas fa-search mr-1"></i>
                                                    {{ trans('admin/main.show_results') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="row mt-2">
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                                    <input type="date" name="from" class="form-control"
                                                           value="{{ request()->get('from') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                                    <input type="date" name="to" class="form-control"
                                                           value="{{ request()->get('to') }}">
                                                </div>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.status') }}</label>
                                                    <select name="status" data-plugin-selectTwo class="form-control">
                                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                                        <option value="draft"     @if(request()->get('status') == 'draft')     selected @endif>{{ trans('admin/main.draft') }}</option>
                                                        <option value="published" @if(request()->get('status') == 'published') selected @endif>{{ trans('admin/main.published') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    {{-- Bookings Table --}}
                                    <div class="table-responsive">
                                        <table class="table custom-table font-14">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('admin/main.title') }}</th>
                                                    <th>{{ trans('admin/main.category') }}</th>
                                                    <th>{{ trans('admin/main.booking_type') }}</th>
                                                    <th>{{ trans('admin/main.price') }}</th>
                                                    <th>{{ trans('public.date') }}</th>
                                                    <th>{{ trans('admin/main.status') }}</th>
                                                    <th>{{ trans('admin/main.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($bookings as $booking)
                                                    <tr>
                                                        <td>
                                                            <a class="text-dark font-weight-bold"
                                                               href="{{ $booking->getUrl() ?? '#' }}" target="_blank">
                                                                {{ $booking->title }}
                                                            </a>
                                                            @if($booking->featured)
                                                                <span class="badge badge-warning ml-1">{{ trans('admin/main.featured') }}</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $booking->category->title ?? '-' }}</td>
                                                        <td>
                                                            <span class="badge badge-info">{{ ucfirst($booking->booking_type) }}</span>
                                                        </td>
                                                        <td>
                                                            @if($booking->discount_price && $booking->discount_price < $booking->price)
                                                                <span class="text-muted" style="text-decoration:line-through">
                                                                    {{ $booking->currency }} {{ number_format($booking->price, 2) }}
                                                                </span><br>
                                                                <span class="text-success font-weight-bold">
                                                                    {{ $booking->currency }} {{ number_format($booking->discount_price, 2) }}
                                                                </span>
                                                            @else
                                                                <span class="font-weight-bold">
                                                                    {{ $booking->currency }} {{ number_format($booking->price, 2) }}
                                                                </span>
                                                            @endif
                                                            @if($booking->price_unit)
                                                                <small class="d-block text-muted">{{ $booking->price_unit }}</small>
                                                            @elseif($booking->price_per)
                                                                <small class="d-block text-muted">/ {{ number_format($booking->price_per, 2) }}</small>
                                                            @endif
                                                        </td>
                                                        <td>{{ dateTimeFormat($booking->created_at, 'j M Y | H:i') }}</td>
                                                        <td>
                                                            <span class="badge-status {{ $booking->status == 'draft' ? 'text-warning bg-warning-30' : 'text-success bg-success-30' }}">
                                                                {{ $booking->status == 'draft' ? trans('admin/main.draft') : trans('admin/main.published') }}
                                                            </span>
                                                        </td>
                                                        <td width="150px">
                                                            <div class="btn-group dropdown table-actions position-relative">
                                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                    <i class="fas fa-ellipsis-v text-gray-500"></i>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    @can('admin_booking_edit')
                                                                        <a href="{{ getAdminPanelUrl() }}/booking/{{ $booking->id }}/edit"
                                                                           class="dropdown-item d-flex align-items-center">
                                                                            <i class="fas fa-edit mr-2 text-primary"></i>
                                                                            <span>{{ trans('admin/main.edit') }}</span>
                                                                        </a>
                                                                    @endcan
                                                                    @can('admin_booking_delete')
                                                                        <a href="#"
                                                                           data-href="{{ getAdminPanelUrl() }}/booking/{{ $booking->id }}/delete"
                                                                           data-toggle="modal"
                                                                           data-target="#deleteModal"
                                                                           class="dropdown-item d-flex align-items-center text-danger">
                                                                            <i class="fas fa-trash mr-2"></i>
                                                                            <span>{{ trans('admin/main.delete') }}</span>
                                                                        </a>
                                                                    @endcan
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="7" class="text-center text-gray-500 py-4">
                                                            <i class="fas fa-calendar-alt mr-1"></i>
                                                            {{ trans('admin/main.no_result') }}
                                                        </td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-center mt-3">
                                        {{ $bookings->appends(request()->input())->links() }}
                                    </div>
                                </div>
                                {{-- end listTab --}}

                                {{-- ==================== CREATE / EDIT TAB ==================== --}}
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel" aria-labelledby="create-tab">

                                    <form action="{{ getAdminPanelUrl() }}/booking/{{ !empty($editBooking) ? $editBooking->id . '/update' : 'store' }}"
                                          method="POST">
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
                                                    <label class="input-label">{{ trans('admin/main.reviewer_message') }}</label>
                                                    <textarea name="reviewer_message" class="form-control"
                                                           placeholder="Reviewer message">{{ !empty($editBooking) ? $editBooking->reviewer_message : old('reviewer_message') }}</textarea>
                                                </div>
                                                     <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.checkout_message') }}</label>
                                                    <textarea name="checkout_message" class="form-control"
                                                           placeholder="Checkout message">{{ !empty($editBooking) ? $editBooking->checkout_message : old('checkout_message') }}</textarea>
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
                                </div>{{-- createTab --}}

                            </div>{{-- tab-content --}}
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

            // ─── FIX 1: Tab switching — proper Bootstrap tab activation ─
            // Jab tab link click ho toh Bootstrap tab() call karo
            $('#bookingTabs a[data-toggle="tab"]').on('click', function (e) {
                e.preventDefault();
                $(this).tab('show');
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

        });

        // ─── Location fields toggle ──────────────────────────────────────
        function toggleLocation(show) {
            document.getElementById('locationFields').style.display = show ? '' : 'none';
        }
    </script>
@endpush
