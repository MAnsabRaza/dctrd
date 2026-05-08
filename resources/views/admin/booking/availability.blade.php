@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_availability') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_availability') }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        @php
                            $createActive = (
                                (!empty($errors) && $errors->any()) ||
                                !empty($editAvailability) ||
                                ((empty($availabilities) || !$availabilities->count()) &&
                                  auth()->user()->can('admin_booking_availability_create'))
                            );
                        @endphp

                        <ul class="nav nav-pills" id="availabilityTab" role="tablist">
                            @can('admin_booking_availability')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_availability') }}
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_availability_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ trans('admin/main.create_booking_availability') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- ==================== LIST TAB ==================== --}}
                            @can('admin_booking_availability')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel">

                                    @if(!empty($availabilities) && $availabilities->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('admin/main.booking') }}</th>
                                                        <th>{{ trans('admin/main.resource') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.date') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.is_available') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.slots_available') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.price_override') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.close_reason') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($availabilities as $availability)
                                                        <tr>
                                                            <td>
                                                                @if($availability->booking)
                                                                    #{{ $availability->booking->id }} - {{ $availability->booking->title }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($availability->resource)
                                                                    {{ $availability->resource->name }}
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $availability->date->format('Y-m-d') }}
                                                            </td>
                                                            <td class="text-center">
                                                                @if($availability->is_available)
                                                                    <span class="badge badge-success">
                                                                        {{ trans('admin/main.available') }}
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger">
                                                                        {{ trans('admin/main.unavailable') }}
                                                                    </span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $availability->slots_available ?? '-' }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $availability->price_override
                                                                    ? number_format($availability->price_override, 2)
                                                                    : '-' }}
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $availability->close_reason ?? '-' }}
                                                            </td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button"
                                                                            class="btn-transparent dropdown-toggle"
                                                                            data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500"
                                                                                            width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_availability_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/availability/{{ $availability->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2
                                                                                    class="icons text-gray-500 mr-2"
                                                                                    width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">
                                                                                    {{ trans('admin/main.edit') }}
                                                                                </span>
                                                                            </a>
                                                                        @endcan

                                                                        @can('admin_booking_availability_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/availability/' . $availability->id . '/delete',
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

                                        {{ $availabilities->links() }}

                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif

                                </div>
                            @endcan

                            {{-- ==================== CREATE / EDIT TAB ==================== --}}
                            @can('admin_booking_availability_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <form action="{{ getAdminPanelUrl() }}/booking/availability/{{ !empty($editAvailability) ? $editAvailability->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Booking --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php
                                                        $selBooking = !empty($editAvailability)
                                                            ? $editAvailability->booking_id
                                                            : old('booking_id');
                                                    @endphp
                                                    <select name="booking_id"
                                                            class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}"
                                                                {{ (string)$selBooking === (string)$booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Resource --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.resource') }}</label>
                                                    @php
                                                        $selResource = !empty($editAvailability)
                                                            ? $editAvailability->resource_id
                                                            : old('resource_id');
                                                    @endphp
                                                    <select name="resource_id"
                                                            class="form-control @error('resource_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }} ({{ trans('admin/main.optional') }})</option>
                                                        @foreach($bookingResources as $resource)
                                                            <option value="{{ $resource->id }}"
                                                                {{ (string)$selResource === (string)$resource->id ? 'selected' : '' }}>
                                                                {{ $resource->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('resource_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Date --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.date') }} <span class="text-danger">*</span></label>
                                                    <input type="date" name="date"
                                                           class="form-control @error('date') is-invalid @enderror"
                                                           value="{{ !empty($editAvailability) ? $editAvailability->date->format('Y-m-d') : old('date') }}"/>
                                                    @error('date')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Is Available --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_available"
                                                               class="custom-control-input" id="is_available"
                                                               {{ (!empty($editAvailability) && $editAvailability->is_available) || empty($editAvailability) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_available">
                                                            {{ trans('admin/main.is_available') }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Slots Available --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.slots_available') }}</label>
                                                    <input type="number" name="slots_available" min="0"
                                                           class="form-control @error('slots_available') is-invalid @enderror"
                                                           value="{{ !empty($editAvailability) ? $editAvailability->slots_available : old('slots_available') }}"
                                                           placeholder="{{ trans('admin/main.optional') }}"/>
                                                    @error('slots_available')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Price Override --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.price_override') }}</label>
                                                    <input type="number" name="price_override" step="0.01" min="0"
                                                           class="form-control @error('price_override') is-invalid @enderror"
                                                           value="{{ !empty($editAvailability) ? $editAvailability->price_override : old('price_override') }}"
                                                           placeholder="{{ trans('admin/main.optional') }}"/>
                                                    @error('price_override')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Close Reason --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.close_reason') }}</label>
                                                    <input type="text" name="close_reason"
                                                           class="form-control @error('close_reason') is-invalid @enderror"
                                                           value="{{ !empty($editAvailability) ? $editAvailability->close_reason : old('close_reason') }}"
                                                           placeholder="{{ trans('admin/main.close_reason_placeholder') }}"/>
                                                    @error('close_reason')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editAvailability))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/availability"
                                                           class="btn btn-secondary mr-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ trans('admin/main.save_change') }}
                                                    </button>
                                                </div>

                                            </form>

                                        </div>
                                    </div>
                                </div>
                            @endcan

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection