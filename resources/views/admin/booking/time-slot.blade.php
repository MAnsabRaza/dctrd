@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_time_slots') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_time_slots') }}</div>
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
                                !empty($editSlot) ||
                                (empty($timeSlots) || !$timeSlots->count())
                            );
                            $dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                        @endphp

                        <ul class="nav nav-pills" id="timeslotTab" role="tablist">
                            @can('admin_booking_time_slots')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}" id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_time_slots') }}
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_time_slots_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}" id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editSlot) ? trans('admin/main.edit_time_slot') : trans('admin/main.create_time_slot') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">
                            @can('admin_booking_time_slots')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}" id="listTab" role="tabpanel">
                                    @if(!empty($timeSlots) && $timeSlots->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('admin/main.booking') }}</th>
                                                        <th>{{ trans('admin/main.resource') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.days') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.start_time') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.end_time') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.duration') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.buffer') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.max_bookings') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($timeSlots as $slot)
                                                        <tr>
                                                            <td>
                                                                @if($slot->booking)
                                                                    #{{ $slot->booking->id }} - {{ $slot->booking->title }}
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($slot->resource)
                                                                    #{{ $slot->resource->id }} - {{ $slot->resource->name }}
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @foreach((array) $slot->day_of_week as $day)
                                                                    <span class="badge badge-info mr-1">{{ $dayNames[$day] ?? $day }}</span>
                                                                @endforeach
                                                            </td>
                                                            <td class="text-center">{{ substr($slot->start_time, 0, 5) }}</td>
                                                            <td class="text-center">{{ substr($slot->end_time, 0, 5) }}</td>
                                                            <td class="text-center">{{ $slot->duration_minutes }} min</td>
                                                            <td class="text-center">{{ $slot->buffer_minutes }} min</td>
                                                            <td class="text-center">{{ $slot->max_bookings }}</td>
                                                            <td class="text-center">
                                                                @if($slot->status)
                                                                    <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                @else
                                                                    <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
                                                                @endif
                                                            </td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_time_slots_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/time-slot/{{ $slot->id }}/edit" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan
                                                                        @can('admin_booking_time_slots_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/time-slot/' . $slot->id . '/delete',
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

                                        {{ $timeSlots->links() }}
                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif
                                </div>
                            @endcan

                            @can('admin_booking_time_slots_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="{{ getAdminPanelUrl() }}/booking/time-slot/{{ !empty($editSlot) ? $editSlot->id . '/update' : 'store' }}" method="post">
                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selectedBooking = !empty($editSlot) ? $editSlot->booking_id : old('booking_id'); @endphp
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}" {{ (string) $selectedBooking === (string) $booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.resource') }}</label>
                                                    @php $selectedResource = !empty($editSlot) ? $editSlot->resource_id : old('resource_id'); @endphp
                                                    <select name="resource_id" class="form-control @error('resource_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($resources as $resource)
                                                            <option value="{{ $resource->id }}" {{ (string) $selectedResource === (string) $resource->id ? 'selected' : '' }}>
                                                                #{{ $resource->id }} - {{ $resource->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('resource_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.days') }} <span class="text-danger">*</span></label>
                                                    @php $selectedDays = array_map('strval', (array) (!empty($editSlot) ? $editSlot->day_of_week : old('day_of_week', []))); @endphp
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($dayNames as $day => $label)
                                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                                <input type="checkbox" id="day_{{ $day }}" name="day_of_week[]" value="{{ $day }}" class="custom-control-input" {{ in_array((string) $day, $selectedDays) ? 'checked' : '' }}>
                                                                <label class="custom-control-label" for="day_{{ $day }}">{{ $label }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('day_of_week')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                    @error('day_of_week.*')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.start_time') }} <span class="text-danger">*</span></label>
                                                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ !empty($editSlot) ? substr($editSlot->start_time, 0, 5) : old('start_time') }}">
                                                    @error('start_time')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.end_time') }} <span class="text-danger">*</span></label>
                                                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ !empty($editSlot) ? substr($editSlot->end_time, 0, 5) : old('end_time') }}">
                                                    @error('end_time')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.duration') }} (minutes) <span class="text-danger">*</span></label>
                                                    <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" min="1" value="{{ !empty($editSlot) ? $editSlot->duration_minutes : old('duration_minutes', 60) }}">
                                                    @error('duration_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.buffer') }} (minutes)</label>
                                                    <input type="number" name="buffer_minutes" class="form-control @error('buffer_minutes') is-invalid @enderror" min="0" value="{{ !empty($editSlot) ? $editSlot->buffer_minutes : old('buffer_minutes', 0) }}">
                                                    @error('buffer_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.max_bookings') }} <span class="text-danger">*</span></label>
                                                    <input type="number" name="max_bookings" class="form-control @error('max_bookings') is-invalid @enderror" min="1" value="{{ !empty($editSlot) ? $editSlot->max_bookings : old('max_bookings', 1) }}">
                                                    @error('max_bookings')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" class="custom-control-input" id="statusSwitch" name="status" {{ (isset($editSlot) ? $editSlot->status : old('status', 1)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="statusSwitch">{{ trans('admin/main.active') }}</label>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary">
                                                    {{ !empty($editSlot) ? trans('admin/main.update_time_slot') : trans('admin/main.create_time_slot') }}
                                                </button>
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
