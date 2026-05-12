@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Booking Time Slots</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">Booking Time Slots</div>
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
                        @endphp

                        {{-- Tabs --}}
                        <ul class="nav nav-pills" id="timeslotTab" role="tablist">
                            @can('admin_booking_time_slots')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        Booking Time Slots
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_time_slots_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editSlot) ? 'Edit Time Slot' : 'Create Time Slot' }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            @can('admin_booking_time_slots')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel">

                                    @if(!empty($timeSlots) && $timeSlots->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('admin/main.booking') }}</th>
                                                        <th>{{ trans('admin/main.resource') }}</th>
                                                        <th class="text-center">Days</th>
                                                        <th class="text-center">Start</th>
                                                        <th class="text-center">End</th>
                                                        <th class="text-center">Duration</th>
                                                        <th class="text-center">Buffer</th>
                                                        <th class="text-center">Max Bookings</th>
                                                        <th class="text-center">Status</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($timeSlots as $slot)
                                                        <tr>
                                                            <td>
                                                                @if($slot->booking)
                                                                    #{{ $slot->booking->id }} — {{ $slot->booking->title }}
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($slot->resource)
                                                                    #{{ $slot->resource->id }} — {{ $slot->resource->name }}
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @foreach($slot->day_of_week as $day)
                                                                    @php
                                                                        $dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                                                                    @endphp
                                                                    <span class="badge badge-info mr-1">{{ $dayNames[$day] ?? $day }}</span>
                                                                @endforeach
                                                            </td>
                                                            <td class="text-center">{{ $slot->start_time }}</td>
                                                            <td class="text-center">{{ $slot->end_time }}</td>
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
                                                                    <button type="button"
                                                                            class="btn-transparent dropdown-toggle"
                                                                            data-toggle="dropdown">
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
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <form action="{{ getAdminPanelUrl() }}/booking/time-slot/{{ !empty($editSlot) ? $editSlot->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selectedBooking = !empty($editSlot) ? $editSlot->booking_id : old('booking_id'); @endphp
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}"
                                                                {{ (string)$selectedBooking === (string)$booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} — {{ $booking->title }}
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
                                                            <option value="{{ $resource->id }}"
                                                                {{ (string)$selectedResource === (string)$resource->id ? 'selected' : '' }}>
                                                                #{{ $resource->id }} — {{ $resource->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('resource_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>Days <span class="text-danger">*</span></label>
                                                    @php
                                                        $selectedDays = array_map('strval', (array)(!empty($editSlot) ? $editSlot->day_of_week : old('day_of_week', [])));
                                                        $weekDays = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                                                    @endphp
                                                    <div class="d-flex flex-wrap gap-2">
                                                        @foreach($weekDays as $day => $label)
                                                            <div class="custom-control custom-checkbox custom-control-inline">
                                                                <input type="checkbox"
                                                                       id="day_{{ $day }}"
                                                                       name="day_of_week[]"
                                                                       value="{{ $day }}"
                                                                       class="custom-control-input"
                                                                       {{ in_array((string)$day, $selectedDays) ? 'checked' : '' }}>
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
                                                    <label>Start Time <span class="text-danger">*</span></label>
                                                    <input type="time" name="start_time"
                                                           class="form-control @error('start_time') is-invalid @enderror"
                                                           value="{{ !empty($editSlot) ? $editSlot->start_time : old('start_time') }}">
                                                    @error('start_time')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>End Time <span class="text-danger">*</span></label>
                                                    <input type="time" name="end_time"
                                                           class="form-control @error('end_time') is-invalid @enderror"
                                                           value="{{ !empty($editSlot) ? $editSlot->end_time : old('end_time') }}">
                                                    @error('end_time')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>Duration (minutes) <span class="text-danger">*</span></label>
                                                    <input type="number" name="duration_minutes"
                                                           class="form-control @error('duration_minutes') is-invalid @enderror"
                                                           min="1"
                                                           value="{{ !empty($editSlot) ? $editSlot->duration_minutes : old('duration_minutes', 60) }}">
                                                    @error('duration_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>Buffer (minutes)</label>
                                                    <input type="number" name="buffer_minutes"
                                                           class="form-control @error('buffer_minutes') is-invalid @enderror"
                                                           min="0"
                                                           value="{{ !empty($editSlot) ? $editSlot->buffer_minutes : old('buffer_minutes', 0) }}">
                                                    @error('buffer_minutes')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label>Max Bookings <span class="text-danger">*</span></label>
                                                    <input type="number" name="max_bookings"
                                                           class="form-control @error('max_bookings') is-invalid @enderror"
                                                           min="1"
                                                           value="{{ !empty($editSlot) ? $editSlot->max_bookings : old('max_bookings', 1) }}">
                                                    @error('max_bookings')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox"
                                                               class="custom-control-input"
                                                               id="statusSwitch"
                                                               name="status"
                                                               {{ (isset($editSlot) ? $editSlot->status : old('status', 1)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="statusSwitch">{{ trans('admin/main.active') }}</label>
                                                    </div>
                                                </div>

                                                <button type="submit" class="btn btn-primary">{{ !empty($editSlot) ? 'Update Time Slot' : 'Create Time Slot' }}</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                        </div>{{-- /tab-content --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection


                                                {{-- Sort Order — auto-generated, user can override --}}
                                                <div class="form-group">
                                                    <label>
                                                        Sort Order
                                                        <small class="text-muted ml-1">
                                                            — auto-assigned
                                                            @if(empty($editVariant))
                                                                (next: <strong>{{ $nextSortOrder }}</strong>)
                                                            @endif
                                                        </small>
                                                    </label>
                                                    <input type="number" name="sort_order" min="0" readonly
                                                           class="form-control @error('sort_order') is-invalid @enderror"
                                                           value="{{ !empty($editVariant) ? $editVariant->sort_order : old('sort_order', $nextSortOrder) }}"
                                                           placeholder="Leave 0 to auto-assign"/>
                                                    <small class="text-muted">
                                                        Leave as-is for automatic numbering. Change only to reorder manually.
                                                    </small>
                                                    @error('sort_order')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- Affects Availability --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="affects_availability"
                                                               class="custom-control-input"
                                                               id="affects_availability"
                                                               {{ (!empty($editVariant) && $editVariant->affects_availability) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="affects_availability">
                                                            Affects Availability
                                                        </label>
                                                    </div>
                                                    <small class="text-muted">
                                                        If enabled, selecting this variant reduces available slots.
                                                    </small>
                                                </div>

                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="status"
                                                               class="custom-control-input" id="status"
                                                               {{ (empty($editVariant) || (!empty($editVariant) && $editVariant->status)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="status">
                                                            Active
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Actions --}}
                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editVariant))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/variant"
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

                        </div>{{-- /tab-content --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts_bottom')
<script>
(function () {
    // ── Add option row ────────────────────────────────────────────────────
    document.getElementById('add-option').addEventListener('click', function () {
        const wrapper = document.getElementById('options-wrapper');
        const row = document.createElement('div');
        row.className = 'input-group mb-2 option-row';
        row.innerHTML =
            '<input type="text" name="options[]" class="form-control" placeholder="Option value"/>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-outline-danger remove-option" title="Remove">' +
                    '<i class="fas fa-times"></i>' +
                '</button>' +
            '</div>';
        wrapper.appendChild(row);
        row.querySelector('input').focus();
    });

    // ── Remove option row (keep at least one) ─────────────────────────────
    document.getElementById('options-wrapper').addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-option');
        if (!btn) return;
        const rows = document.querySelectorAll('#options-wrapper .option-row');
        if (rows.length > 1) {
            btn.closest('.option-row').remove();
        } else {
            // Clear value instead of removing last row
            btn.closest('.option-row').querySelector('input').value = '';
        }
    });
})();
</script>
@endpush