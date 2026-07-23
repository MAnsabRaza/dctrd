<div id="bookingSlotPanel" class="bg-white p-16 rounded-24 mt-24">
    <h3 class="font-16 font-weight-bold">{{ trans('update.check_booking') }}</h3>
    <p class="font-12 text-gray-500 mt-4 mb-0">{{ trans('update.select_date_check_slots') }}</p>

    <div class="mt-16">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="form-group-label">{{ trans('public.date') }}</label>
                    <input type="date" id="slotDateInput" class="form-control"
                           value="{{ request()->get('date', now()->toDateString()) }}"
                           min="{{ now()->toDateString() }}">
                    <div class="invalid-feedback d-block js-booking-field-error" data-field="slot_date" style="display:none;"></div>
                </div>
            </div>

            @if(!empty($booking->resources) and count($booking->resources))
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-group-label">{{ trans('update.resource') }}</label>
                        <select id="slotResourceId" class="form-control">
                            <option value="">{{ trans('update.any_resource') }}</option>
                            @foreach($booking->resources as $resource)
                                <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback d-block js-booking-field-error" data-field="resource_id" style="display:none;"></div>
                    </div>
                </div>
            @endif
        </div>

        <button type="button" id="checkSlotsBtn" class="btn btn-outline-primary btn-lg">
            {{ trans('update.check_slots') }}
        </button>
    </div>

    <div class="mt-16" id="slotsContainer">
        @if(!is_null($availableSlots))
            <h4 class="font-14 font-weight-bold">{{ trans('update.available_slots') }}</h4>
            @if(count($availableSlots))
                <div class="d-flex align-items-center flex-wrap gap-8 mt-12">
                    @foreach($availableSlots as $slot)
                        <label class="booking-slot-pill">
                            <input type="radio" name="selected_slot"
                                   value="{{ $slot['start_time'] }}"
                                   data-end="{{ $slot['end_time'] }}"
                                   data-date="{{ request()->get('date') }}">
                            {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                        </label>
                    @endforeach
                </div>
                <p class="font-12 text-gray-500 mt-8">{{ trans('update.select_slot_then_book') }}</p>
            @else
                <div class="mt-12 text-gray-500">{{ trans('update.no_slots_available') }}</div>
            @endif
        @endif
    </div>

    <div class="invalid-feedback d-block js-booking-field-error mt-8" data-field="slot_start" style="display:none;"></div>
    <div class="invalid-feedback d-block js-booking-field-error mt-8" data-field="quantity" style="display:none;"></div>
    <div id="availabilityMessage" class="mt-12" style="display:none;"></div>
</div>
