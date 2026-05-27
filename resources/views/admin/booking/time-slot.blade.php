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

                            {{-- ===================== LIST TAB ===================== --}}
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
                                                            <td class="text-center">{{ $slot->buffer_minutes ?? 0 }} min</td>
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

                            {{-- ===================== CREATE / EDIT TAB ===================== --}}
                            @can('admin_booking_time_slots_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="{{ getAdminPanelUrl() }}/booking/time-slot/{{ !empty($editSlot) ? $editSlot->id . '/update' : 'store' }}" method="post">
                                                {{ csrf_field() }}

                                                {{-- Step 1: Booking --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selectedBooking = !empty($editSlot) ? (string) $editSlot->booking_id : (string) old('booking_id'); @endphp
                                                    <select name="booking_id" id="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}" {{ $selectedBooking === (string) $booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{--
                                                    Step 2: Resource
                                                    - Always visible (same as order dropdown in review)
                                                    - Disabled by default until a booking is selected
                                                    - Filters to only show resources of the selected booking
                                                    - Shows hint text below like review's order dropdown
                                                --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.resource') }}</label>
                                                    @php $selectedResource = !empty($editSlot) ? (string) $editSlot->resource_id : (string) old('resource_id'); @endphp
                                                    <select name="resource_id" id="resource_id"
                                                            class="form-control @error('resource_id') is-invalid @enderror"
                                                            disabled>
                                                        <option value="">— Select Booking First —</option>
                                                        @foreach($resources as $resource)
                                                            <option
                                                                value="{{ $resource->id }}"
                                                                data-booking="{{ $resource->booking_id }}"
                                                                {{ $selectedResource === (string) $resource->id ? 'selected' : '' }}
                                                                style="display:none">
                                                                #{{ $resource->id }} - {{ $resource->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <small class="text-muted" id="resourceHint">Please select a booking first.</small>
                                                    @error('resource_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Days of week --}}
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

                                                {{-- Start time --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.start_time') }} <span class="text-danger">*</span></label>
                                                    <input type="time" name="start_time" class="form-control @error('start_time') is-invalid @enderror" value="{{ !empty($editSlot) ? substr($editSlot->start_time, 0, 5) : old('start_time') }}">
                                                    @error('start_time')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- End time --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.end_time') }} <span class="text-danger">*</span></label>
                                                    <input type="time" name="end_time" class="form-control @error('end_time') is-invalid @enderror" value="{{ !empty($editSlot) ? substr($editSlot->end_time, 0, 5) : old('end_time') }}">
                                                    @error('end_time')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Duration --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.duration') }} (minutes) <span class="text-danger">*</span></label>
                                                    <input type="number" name="duration_minutes" class="form-control @error('duration_minutes') is-invalid @enderror" min="1" value="{{ !empty($editSlot) ? $editSlot->duration_minutes : old('duration_minutes', 60) }}">
                                                    @error('duration_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Buffer --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.buffer') }} (minutes)</label>
                                                    <input type="number" name="buffer_minutes" class="form-control @error('buffer_minutes') is-invalid @enderror" min="0" value="{{ !empty($editSlot) ? $editSlot->buffer_minutes : old('buffer_minutes', 0) }}">
                                                    @error('buffer_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Max bookings --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.max_bookings') }} <span class="text-danger">*</span></label>
                                                    <input type="number" name="max_bookings" class="form-control @error('max_bookings') is-invalid @enderror" min="1" value="{{ !empty($editSlot) ? $editSlot->max_bookings : old('max_bookings', 1) }}">
                                                    @error('max_bookings')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Status --}}
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

                        </div>{{-- end .tab-content --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var bookingSelect  = document.getElementById('booking_id');
    var resourceSelect = document.getElementById('resource_id');
    var resourceHint   = document.getElementById('resourceHint');

    if (!bookingSelect || !resourceSelect) return;

    // PHP se values JS mein (edit mode ya validation fail dono handle)
    var preSelectedBookingId  = '{{ !empty($editSlot) ? $editSlot->booking_id : old("booking_id", "") }}';
    var preSelectedResourceId = '{{ !empty($editSlot) ? $editSlot->resource_id : old("resource_id", "") }}';

    // Sare resource options ek baar cache karo (data-booking wale)
    var allResourceOptions = Array.prototype.slice.call(
        resourceSelect.querySelectorAll('option[data-booking]')
    );

    function filterResources(restoreId) {
        var bookingId = String(bookingSelect.value).trim();

        // Reset dropdown
        resourceSelect.innerHTML = '';
        resourceSelect.disabled  = true;
        resourceHint.textContent = 'Please select a booking first.';

        if (!bookingId) {
            var opt = document.createElement('option');
            opt.value       = '';
            opt.textContent = '— Select Booking First —';
            resourceSelect.appendChild(opt);
            return;
        }

        // Sirf is booking ki resources filter karo
        var matched = allResourceOptions.filter(function (opt) {
            return String(opt.getAttribute('data-booking')).trim() === bookingId;
        });

        if (matched.length === 0) {
            var noOpt = document.createElement('option');
            noOpt.value       = '';
            noOpt.textContent = '— No resources for this booking —';
            resourceSelect.appendChild(noOpt);
            resourceHint.textContent = 'No resources found for this booking.';
            return;
        }

        // Placeholder
        var placeholder = document.createElement('option');
        placeholder.value       = '';
        placeholder.textContent = '— Select Resource (Optional) —';
        resourceSelect.appendChild(placeholder);

        // Matched options add karo (style:none hata ke)
        matched.forEach(function (opt) {
            var clone = opt.cloneNode(true);
            clone.style.display = '';
            resourceSelect.appendChild(clone);
        });

        resourceSelect.disabled  = false;
        resourceHint.textContent = matched.length + ' resource(s) found.';

        // Agar restoreId diya hai aur matched mein hai to select karo
        if (restoreId) {
            var exists = matched.some(function (o) {
                return String(o.value).trim() === String(restoreId).trim();
            });
            resourceSelect.value = exists ? restoreId : '';
        }
    }

    // User change kare to fresh filter (koi restore nahi)
    bookingSelect.addEventListener('change', function () {
        filterResources('');
    });

    // ── Page load ──
    // Agar booking already selected hai (edit mode ya validation fail)
    if (preSelectedBookingId) {
        bookingSelect.value = preSelectedBookingId; // booking set karo
        filterResources(preSelectedResourceId);     // filter + resource restore
    }

})();
</script>
@endpush