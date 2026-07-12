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

    <div id="availabilityMessage" class="mt-12" style="display:none;"></div>

    {{-- ✅ NAYA — Book button --}}
    <button type="button" id="bookSlotBtn" class="btn btn-primary btn-lg mt-16" disabled>
        {{ trans('update.book_this_slot') ?? 'Book This Slot' }}
    </button>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const bookingId  = {{ $booking->id }};
    const slotsBox   = document.getElementById('slotsContainer');
    const msgBox     = document.getElementById('availabilityMessage');
    const bookBtn    = document.getElementById('bookSlotBtn');

    // Radio select hone par book button enable karo
    slotsBox.addEventListener('change', function (e) {
        if (e.target.name === 'selected_slot') {
            bookBtn.disabled = false;
        }
    });

    bookBtn.addEventListener('click', function () {
        const selected = document.querySelector('input[name="selected_slot"]:checked');

        if (!selected) {
            msgBox.style.display = 'block';
            msgBox.className = 'mt-12 text-danger';
            msgBox.innerText = "{{ trans('update.select_slot_first') ?? 'Please select a slot first.' }}";
            return;
        }

        const date       = document.getElementById('slotDateInput').value;
        const startTime  = selected.value;
        const endTime    = selected.dataset.end;
        const resourceEl = document.getElementById('slotResourceId');
        const resourceId = resourceEl ? resourceEl.value : '';

        bookBtn.disabled = true;
        bookBtn.innerText = "{{ trans('public.loading') ?? 'Please wait...' }}";

        // Step 1: Server-side re-check
        fetch(`/bookings/{{ $booking->slug }}/check-availability`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                date: date,
                start_time: startTime,
                resource_id: resourceId || null,
            }),
        })
        .then(res => res.json())
        .then(result => {
            if (!result.available) {
                msgBox.style.display = 'block';
                msgBox.className = 'mt-12 text-danger';
                msgBox.innerText = result.message;
                bookBtn.disabled = false;
                bookBtn.innerText = "{{ trans('update.book_this_slot') ?? 'Book This Slot' }}";
                return;
            }

            // Step 2: Available hai to cart mein add karo
            fetch(`/cart/store`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({
                    item_id: bookingId,
                    item_name: 'booking_id',
                    slot_date: date,
                    slot_start: startTime,
                    slot_end: endTime,
                    resource_id: resourceId || null,
                }),
            })
            .then(res => res.json())
            .then(cartResult => {
                if (cartResult.code === 200 || cartResult.status === 'success') {
                    window.location.href = '/cart';
                } else {
                    msgBox.style.display = 'block';
                    msgBox.className = 'mt-12 text-danger';
                    msgBox.innerText = cartResult.toast_alert?.msg
                        || cartResult.msg
                        || "{{ trans('public.request_failed') ?? 'Something went wrong.' }}";
                    bookBtn.disabled = false;
                    bookBtn.innerText = "{{ trans('update.book_this_slot') ?? 'Book This Slot' }}";
                }
            });
        })
        .catch(() => {
            msgBox.style.display = 'block';
            msgBox.className = 'mt-12 text-danger';
            msgBox.innerText = "{{ trans('public.request_failed') ?? 'Something went wrong.' }}";
            bookBtn.disabled = false;
            bookBtn.innerText = "{{ trans('update.book_this_slot') ?? 'Book This Slot' }}";
        });
    });
});
</script>