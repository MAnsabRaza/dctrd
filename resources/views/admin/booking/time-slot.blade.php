@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_time_slots') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">
                {{ trans('admin/main.admin_booking_time_slots') }}
            </div>
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

                            $dayNames = [
                                1 => 'Mon', 2 => 'Tue', 3 => 'Wed',
                                4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'
                            ];
                        @endphp

                        {{-- TABS --}}
                        <ul class="nav nav-pills" id="timeslotTab" role="tablist">

                            @can('admin_booking_time_slots')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_time_slots') }}
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_time_slots_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editSlot) ? trans('admin/main.edit_time_slot') : trans('admin/main.create_time_slot') }}
                                    </a>
                                </li>
                            @endcan

                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
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
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/time-slot/{{ $slot->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
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

                            {{-- CREATE / EDIT TAB --}}
                            @can('admin_booking_time_slots_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <form action="{{ getAdminPanelUrl() }}/booking/time-slot/{{ !empty($editSlot) ? $editSlot->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- BOOKING --}}
                                                <div class="form-group">
                                                    <label>
                                                        {{ trans('admin/main.booking') }}
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    @php
                                                        $selBook = !empty($editSlot)
                                                            ? (string) $editSlot->booking_id
                                                            : (string) old('booking_id');
                                                    @endphp

                                                    <select name="booking_id"
                                                            id="booking_id"
                                                            class="form-control @error('booking_id') is-invalid @enderror"
                                                            required>
                                                        <option value="">-- Select Booking --</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}"
                                                                {{ $selBook === (string) $booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('booking_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- RESOURCE --}}
                                                <div class="form-group">
                                                    <label>
                                                        {{ trans('admin/main.resource') }}
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    @php
                                                        $selResource = !empty($editSlot)
                                                            ? (string) $editSlot->resource_id
                                                            : (string) old('resource_id');
                                                    @endphp

                                                    {{--
                                                        resource_id select mein data-preselect attribute rakha hai.
                                                        JS AJAX call karega booking select hone par aur
                                                        SELECT * FROM booking_resources WHERE booking_id = ?
                                                        wala kaam controller karega.
                                                        Edit mode mein editResources already controller se aa rahe hain.
                                                    --}}
                                                    <select name="resource_id"
                                                            id="resource_id"
                                                            class="form-control @error('resource_id') is-invalid @enderror"
                                                            data-preselect="{{ $selResource }}"
                                                            {{ empty($selBook) ? 'disabled' : '' }}>

                                                        @if(empty($selBook))
                                                            <option value="">-- Pehle Booking Select Karein --</option>
                                                        @else
                                                            {{-- Edit mode: controller ne already resources bheje hain --}}
                                                            <option value="">-- Resource Select Karein --</option>
                                                            @foreach($editResources ?? [] as $res)
                                                                <option value="{{ $res->id }}"
                                                                    {{ $selResource === (string) $res->id ? 'selected' : '' }}>
                                                                    #{{ $res->id }} - {{ $res->name }}
                                                                </option>
                                                            @endforeach
                                                        @endif

                                                    </select>

                                                    <small id="resource_loading" class="text-muted d-none">
                                                        Loading resources...
                                                    </small>

                                                    @error('resource_id')
                                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- DAYS --}}
                                                <div class="form-group">
                                                    <label>
                                                        {{ trans('admin/main.days') }}
                                                        <span class="text-danger">*</span>
                                                    </label>

                                                    @php
                                                        $selectedDays = array_map('strval', (array) (
                                                            !empty($editSlot) ? $editSlot->day_of_week : old('day_of_week', [])
                                                        ));
                                                    @endphp

                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($dayNames as $day => $label)
                                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                                <input type="checkbox"
                                                                       id="day_{{ $day }}"
                                                                       name="day_of_week[]"
                                                                       value="{{ $day }}"
                                                                       class="custom-control-input"
                                                                       {{ in_array((string) $day, $selectedDays) ? 'checked' : '' }}>
                                                                <label class="custom-control-label" for="day_{{ $day }}">
                                                                    {{ $label }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                {{-- START TIME --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.start_time') }}</label>
                                                    <input type="time" name="start_time" class="form-control"
                                                           value="{{ !empty($editSlot) ? substr($editSlot->start_time, 0, 5) : old('start_time') }}">
                                                </div>

                                                {{-- END TIME --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.end_time') }}</label>
                                                    <input type="time" name="end_time" class="form-control"
                                                           value="{{ !empty($editSlot) ? substr($editSlot->end_time, 0, 5) : old('end_time') }}">
                                                </div>

                                                {{-- DURATION --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.duration') }}</label>
                                                    <input type="number" min="1" name="duration_minutes" class="form-control"
                                                           value="{{ !empty($editSlot) ? $editSlot->duration_minutes : old('duration_minutes', 60) }}">
                                                </div>

                                                {{-- BUFFER --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.buffer') }}</label>
                                                    <input type="number" min="0" name="buffer_minutes" class="form-control"
                                                           value="{{ !empty($editSlot) ? $editSlot->buffer_minutes : old('buffer_minutes', 0) }}">
                                                </div>

                                                {{-- MAX BOOKINGS --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.max_bookings') }}</label>
                                                    <input type="number" min="1" name="max_bookings" class="form-control"
                                                           value="{{ !empty($editSlot) ? $editSlot->max_bookings : old('max_bookings', 1) }}">
                                                </div>

                                                {{-- STATUS --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox"
                                                               class="custom-control-input"
                                                               id="statusSwitch"
                                                               name="status"
                                                               {{ (isset($editSlot) ? $editSlot->status : old('status', 1)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="statusSwitch">
                                                            {{ trans('admin/main.active') }}
                                                        </label>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    var bookingSelect  = document.getElementById('booking_id');
    var resourceSelect = document.getElementById('resource_id');
    var loadingText    = document.getElementById('resource_loading');

    // AJAX URL — controller ka getResources method
    var ajaxUrl = "{{ getAdminPanelUrl() }}/booking/time-slot/resources";

    if (!bookingSelect || !resourceSelect) return;

    /**
     * Booking ID de do — ye function AJAX se
     * SELECT * FROM booking_resources WHERE booking_id = bookingId
     * chalayega aur dropdown fill karega
     */
    function loadResources(bookingId, preSelectId) {

        if (!bookingId) {
            resourceSelect.innerHTML = '<option value="">-- Pehle Booking Select Karein --</option>';
            resourceSelect.disabled = true;
            return;
        }

        // Loading state
        resourceSelect.disabled = true;
        loadingText.classList.remove('d-none');
        resourceSelect.innerHTML = '<option value="">Loading...</option>';

        fetch(ajaxUrl + '?booking_id=' + bookingId, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(function (response) {
            if (!response.ok) throw new Error('Server error: ' + response.status);
            return response.json();
        })
        .then(function (resources) {

            loadingText.classList.add('d-none');
            resourceSelect.innerHTML = '';

            if (!resources.length) {
                resourceSelect.innerHTML = '<option value="">Is booking ka koi resource nahi</option>';
                resourceSelect.disabled = true;
                return;
            }

            resourceSelect.disabled = false;

            var placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = '-- Resource Select Karein --';
            resourceSelect.appendChild(placeholder);

            resources.forEach(function (res) {
                var opt = document.createElement('option');
                opt.value = res.id;
                opt.textContent = '#' + res.id + ' - ' + res.name;
                // Agar edit mode hai toh pre-select karo
                if (preSelectId && String(res.id) === String(preSelectId)) {
                    opt.selected = true;
                }
                resourceSelect.appendChild(opt);
            });
        })
        .catch(function (err) {
            loadingText.classList.add('d-none');
            resourceSelect.innerHTML = '<option value="">Error loading resources</option>';
            resourceSelect.disabled = true;
            console.error('Resource load error:', err);
        });
    }

    // User jab booking change kare
    bookingSelect.addEventListener('change', function () {
        loadResources(this.value, null);
    });

    // Edit mode: page load par already selected booking ka resource load karo
    var initialBookingId = bookingSelect.value;
    if (initialBookingId) {
        var preSelectId = resourceSelect.getAttribute('data-preselect') || '';
        // Edit mode mein server ne options render kar diye hain, AJAX ki zaroorat nahi
        // Sirf disabled hata do
        resourceSelect.disabled = false;
    }

});
</script>
@endpush