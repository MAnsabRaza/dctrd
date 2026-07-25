{{--
    Step 3 — Participants, Resources, Assets, Recurring, Availability Overrides

    Participants = plain Booking columns (saved on normal step submit).
    Resources/Assets (BookingResource, type column), Recurring (BookingTimeSlot),
    and Availability Overrides (BookingAvailability) are added/removed live via
    small AJAX calls — this avoids losing the rest of the form's state on every
    add/remove, while everything else on this page still uses the normal
    page-reload submit like the other steps.
--}}
@php
    $meta = $booking->meta ?? [];
    $participantsEnabled = old('participants_enabled', $meta['participants_enabled'] ?? false);
    $resourcesEnabled    = old('resources_enabled', $meta['resources_enabled'] ?? false);
    $assetsEnabled       = old('assets_enabled', $meta['assets_enabled'] ?? false);
    $recurringEnabled    = old('recurring_enabled', $meta['recurring_enabled'] ?? false);
    $availabilityEnabled = old('availability_enabled', $meta['availability_enabled'] ?? false);

    $resourceRows = ($booking->resources ?? collect())->where('type', '!=', 'asset');
    $assetRows    = ($booking->resources ?? collect())->where('type', '=', 'asset');
    $timeSlots    = $booking->timeSlots ?? collect();
    $availabilityRows = $booking->availabilities ?? collect();

    $weekDays = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-users"></i></div>
    <div>
        <h6>Participants, Resources, Assets &amp; Recurring</h6>
        <p class="section-sub">Turn on only what this booking type needs.</p>
    </div>
</div>

<div class="panel-card">

    {{-- ── Participants ─────────────────────────────────── --}}
    <div class="booking-switch-row-bordered">
        <div class="booking-switch-row">
            <label class="booking-switch" for="toggleParticipants">
                <input type="checkbox" id="toggleParticipants" name="participants_enabled"
                       {{ $participantsEnabled ? 'checked' : '' }}
                       onchange="document.getElementById('participantsBlock').style.display = this.checked ? 'block' : 'none'">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label mb-0" for="toggleParticipants">
                Booking Participants <span class="text-danger">*</span>
                <span class="field-hint" title="Limit how many people can join a single booking">?</span>
            </label>
        </div>

        <div id="participantsBlock" class="row mt-2" style="{{ $participantsEnabled ? '' : 'display:none' }}">
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Min Persons</label>
                    <input type="number" min="0" name="min_persons" class="form-control"
                           value="{{ old('min_persons', $booking->min_persons ?? 1) }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Max Persons</label>
                    <input type="number" min="0" name="max_persons" class="form-control"
                           value="{{ old('max_persons', $booking->max_persons ?? '') }}" placeholder="No limit">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Max Children</label>
                    <input type="number" min="0" name="max_children" class="form-control"
                           value="{{ old('max_children', $booking->max_children ?? '') }}">
                </div>
            </div>
            <div class="col-12 col-md-3 d-flex align-items-center">
                <div class="form-check mt-4">
                    <input class="form-check-input" type="checkbox" id="childrenAllowed" name="children_allowed"
                           {{ old('children_allowed', $booking->children_allowed) ? 'checked' : '' }}>
                    <label class="form-check-label" for="childrenAllowed">Children allowed</label>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Resources ─────────────────────────────────────── --}}
    <div class="booking-switch-row-bordered">
        <div class="booking-switch-row">
            <label class="booking-switch" for="toggleResources">
                <input type="checkbox" id="toggleResources" name="resources_enabled"
                       {{ $resourcesEnabled ? 'checked' : '' }}
                       onchange="document.getElementById('resourcesBlock').style.display = this.checked ? 'block' : 'none'">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label mb-0" for="toggleResources">
                Booking Resources <span class="text-danger">*</span>
                <span class="field-hint" title="Rooms, tables, vehicles — things assigned per booking">?</span>
            </label>
        </div>

        <div id="resourcesBlock" class="mt-2" style="{{ $resourcesEnabled ? '' : 'display:none' }}">
            <div class="mini-table-wrap">
                <table class="mini-table" id="resourcesTable">
                    <thead><tr><th>Name</th><th>Type</th><th>Capacity</th><th>Extra Price</th><th></th></tr></thead>
                    <tbody>
                        @foreach($resourceRows as $r)
                            <tr data-id="{{ $r->id }}">
                                <td>{{ $r->name }}</td><td>{{ $r->type }}</td><td>{{ $r->capacity ?? '—' }}</td>
                                <td>{{ number_format($r->extra_price, 2) }}</td>
                                <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-resource"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($resourceRows->isEmpty())
                    <div class="empty-state" id="resourcesEmptyHint">
                        <div class="badge-icon"><i class="fa fa-cube"></i></div>
                        <div class="empty-title">No resources yet</div>
                    </div>
                @endif
            </div>
            <div class="row align-items-end">
                <div class="col-6 col-md-4"><input type="text" class="form-control form-control-sm" id="newResourceName" placeholder="e.g. Room 101"></div>
                <div class="col-6 col-md-3"><input type="text" class="form-control form-control-sm" id="newResourceType" placeholder="Type, e.g. room"></div>
                <div class="col-6 col-md-2"><input type="number" min="0" class="form-control form-control-sm" id="newResourceCapacity" placeholder="Capacity"></div>
                <div class="col-6 col-md-2"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="newResourceExtraPrice" placeholder="Extra price"></div>
                <div class="col-12 col-md-1"><button type="button" class="btn btn-sm btn-primary btn-block" id="addResourceBtn"><i class="fa fa-plus"></i></button></div>
            </div>
            @if(empty($booking->id))
                <small class="text-warning d-block mt-2">Save step 1 first to start adding resources.</small>
            @endif
        </div>
    </div>

    {{-- ── Assets ────────────────────────────────────────── --}}
    <div class="booking-switch-row-bordered">
        <div class="booking-switch-row">
            <label class="booking-switch" for="toggleAssets">
                <input type="checkbox" id="toggleAssets" name="assets_enabled"
                       {{ $assetsEnabled ? 'checked' : '' }}
                       onchange="document.getElementById('assetsBlock').style.display = this.checked ? 'block' : 'none'">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label mb-0" for="toggleAssets">Booking Assets</label>
        </div>

        <div id="assetsBlock" class="mt-2" style="{{ $assetsEnabled ? '' : 'display:none' }}">
            <div class="mini-table-wrap">
                <table class="mini-table" id="assetsTable">
                    <thead><tr><th>Name</th><th>Capacity / Qty</th><th>Extra Price</th><th></th></tr></thead>
                    <tbody>
                        @foreach($assetRows as $a)
                            <tr data-id="{{ $a->id }}">
                                <td>{{ $a->name }}</td><td>{{ $a->capacity ?? '—' }}</td>
                                <td>{{ number_format($a->extra_price, 2) }}</td>
                                <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-asset"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($assetRows->isEmpty())
                    <div class="empty-state" id="assetsEmptyHint">
                        <div class="badge-icon"><i class="fa fa-suitcase"></i></div>
                        <div class="empty-title">No assets yet</div>
                    </div>
                @endif
            </div>
            <div class="row align-items-end">
                <div class="col-6 col-md-5"><input type="text" class="form-control form-control-sm" id="newAssetName" placeholder="e.g. Kayak"></div>
                <div class="col-6 col-md-3"><input type="number" min="1" class="form-control form-control-sm" id="newAssetQty" placeholder="Qty / capacity" value="1"></div>
                <div class="col-6 col-md-3"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="newAssetExtraPrice" placeholder="Extra price"></div>
                <div class="col-12 col-md-1"><button type="button" class="btn btn-sm btn-primary btn-block" id="addAssetBtn"><i class="fa fa-plus"></i></button></div>
            </div>
        </div>
    </div>

    {{-- ── Recurring (Time Slots) ──────────────────────────── --}}
    <div class="booking-switch-row-bordered">
        <div class="booking-switch-row">
            <label class="booking-switch" for="toggleRecurring">
                <input type="checkbox" id="toggleRecurring" name="recurring_enabled"
                       {{ $recurringEnabled ? 'checked' : '' }}
                       onchange="document.getElementById('recurringBlock').style.display = this.checked ? 'block' : 'none'">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label mb-0" for="toggleRecurring">
                Recurring Bookings
                <small>Please ensure your booking period works for recurring slots</small>
            </label>
        </div>

        <div id="recurringBlock" class="mt-2" style="{{ $recurringEnabled ? '' : 'display:none' }}">
            <div class="mini-table-wrap">
                <table class="mini-table" id="timeSlotsTable">
                    <thead><tr><th>Days</th><th>Start</th><th>End</th><th>Duration</th><th>Max</th><th></th></tr></thead>
                    <tbody>
                        @foreach($timeSlots as $slot)
                            <tr data-id="{{ $slot->id }}">
                                <td>{{ implode(', ', array_map(fn($d) => $weekDays[$d] ?? $d, $slot->day_of_week ?? [])) }}</td>
                                <td>{{ $slot->start_time }}</td><td>{{ $slot->end_time }}</td>
                                <td>{{ $slot->duration_minutes }}</td><td>{{ $slot->max_bookings }}</td>
                                <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-timeslot"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($timeSlots->isEmpty())
                    <div class="empty-state" id="timeSlotsEmptyHint">
                        <div class="badge-icon"><i class="fa fa-repeat"></i></div>
                        <div class="empty-title">No recurring slots yet</div>
                    </div>
                @endif
            </div>
            <div class="row align-items-end">
                <div class="col-12 mb-2">
                    <label class="d-block small mb-1">Days of week</label>
                    @foreach($weekDays as $val => $label)
                        <label class="chip-check mr-1 mb-1">
                            <input type="checkbox" id="newSlotDay_{{ $val }}" value="{{ $val }}">
                            <span class="chip-box">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                <div class="col-6 col-md-2"><label class="small">Start</label><input type="time" class="form-control form-control-sm" id="newSlotStart"></div>
                <div class="col-6 col-md-2"><label class="small">End</label><input type="time" class="form-control form-control-sm" id="newSlotEnd"></div>
                <div class="col-6 col-md-2"><label class="small">Duration (min)</label><input type="number" min="0" class="form-control form-control-sm" id="newSlotDuration"></div>
                <div class="col-6 col-md-2"><label class="small">Max bookings</label><input type="number" min="1" class="form-control form-control-sm" id="newSlotMax" value="1"></div>
                <div class="col-12 col-md-2"><button type="button" class="btn btn-sm btn-primary btn-block" id="addTimeSlotBtn"><i class="fa fa-plus mr-1"></i> Add</button></div>
            </div>
        </div>
    </div>

    {{-- ── Availability Overrides (NEW) ────────────────────── --}}
    <div class="booking-switch-row-bordered mb-0">
        <div class="booking-switch-row">
            <label class="booking-switch" for="toggleAvailability">
                <input type="checkbox" id="toggleAvailability" name="availability_enabled"
                       {{ $availabilityEnabled ? 'checked' : '' }}
                       onchange="document.getElementById('availabilityBlock').style.display = this.checked ? 'block' : 'none'">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label mb-0" for="toggleAvailability">
                Availability Overrides
                <small>Block a specific date, or override slots/price for that day only</small>
            </label>
        </div>

        <div id="availabilityBlock" class="mt-2" style="{{ $availabilityEnabled ? '' : 'display:none' }}">
            <div class="mini-table-wrap">
                <table class="mini-table" id="availabilityTable">
                    <thead><tr><th>Date</th><th>Resource</th><th>Status</th><th>Slots</th><th>Price Override</th><th>Reason</th><th></th></tr></thead>
                    <tbody>
                        @foreach($availabilityRows as $avail)
                            <tr data-id="{{ $avail->id }}">
                                <td>{{ optional($avail->date)->format('Y-m-d') ?? $avail->date }}</td>
                                <td>{{ optional($avail->resource)->name ?? 'Any / Whole Booking' }}</td>
                                <td>{{ $avail->is_available ? 'Open' : 'Blocked' }}</td>
                                <td>{{ $avail->slots_available ?? '—' }}</td>
                                <td>{{ $avail->price_override ?? '—' }}</td>
                                <td>{{ $avail->close_reason ?? '—' }}</td>
                                <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-availability"><i class="fa fa-trash"></i></button></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
                @if($availabilityRows->isEmpty())
                    <div class="empty-state" id="availabilityEmptyHint">
                        <div class="badge-icon"><i class="fa fa-calendar-times-o"></i></div>
                        <div class="empty-title">No overrides yet</div>
                        <div class="empty-sub">By default every day follows the Recurring slots above.</div>
                    </div>
                @endif
            </div>
            <div class="row align-items-end">
                <div class="col-6 col-md-2"><label class="small">Date</label><input type="date" class="form-control form-control-sm" id="avDate"></div>
                <div class="col-6 col-md-3">
                    <label class="small">Resource</label>
                    <select class="form-control form-control-sm" id="avResourceId">
                        <option value="">Any / Whole Booking</option>
                        @foreach($resourceRows as $r)
                            <option value="{{ $r->id }}">{{ $r->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <label class="small d-block">Available?</label>
                    <div class="booking-switch-row py-0">
                        <label class="booking-switch" for="avIsAvailable">
                            <input type="checkbox" id="avIsAvailable" checked>
                            <span class="booking-switch-slider"></span>
                        </label>
                    </div>
                </div>
                <div class="col-6 col-md-1"><label class="small">Slots</label><input type="number" min="0" class="form-control form-control-sm" id="avSlots" placeholder="opt."></div>
                <div class="col-6 col-md-2"><label class="small">Price Override</label><input type="number" min="0" step="0.01" class="form-control form-control-sm" id="avPrice" placeholder="opt."></div>
                <div class="col-12 col-md-2"><button type="button" class="btn btn-sm btn-primary btn-block" id="addAvailabilityBtn"><i class="fa fa-plus mr-1"></i> Add</button></div>
                <div class="col-12 mt-2">
                    <input type="text" class="form-control form-control-sm" id="avReason" placeholder="Reason if blocked, e.g. Public holiday, fully booked">
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    @if(!empty($booking->id))
    const bookingId = {{ $booking->id }};
    const csrf = '{{ csrf_token() }}';

    function post(path, payload) {
        return fetch(`/panel/bookings/${path}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(r => r.json());
    }
    function del(path) {
        return fetch(`/panel/bookings/${path}`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
        }).then(r => r.json());
    }

    document.getElementById('addResourceBtn')?.addEventListener('click', function () {
        const name = document.getElementById('newResourceName').value.trim();
        if (!name) return;
        post(`${bookingId}/resources`, {
            name,
            type: document.getElementById('newResourceType').value || 'resource',
            capacity: document.getElementById('newResourceCapacity').value || null,
            extra_price: document.getElementById('newResourceExtraPrice').value || 0,
        }).then(data => {
            if (!data.success) return;
            document.getElementById('resourcesEmptyHint')?.remove();
            const r = data.resource;
            const tbody = document.querySelector('#resourcesTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = r.id;
            row.innerHTML = `<td>${r.name}</td><td>${r.type ?? ''}</td><td>${r.capacity ?? '—'}</td><td>${Number(r.extra_price).toFixed(2)}</td><td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-resource"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.getElementById('newResourceName').value = '';

            // keep the availability-override "Resource" dropdown in sync
            const avSelect = document.getElementById('avResourceId');
            if (avSelect) {
                const opt = document.createElement('option');
                opt.value = r.id;
                opt.textContent = r.name;
                avSelect.appendChild(opt);
            }
        });
    });

    document.getElementById('resourcesTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-resource');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`resources/${row.dataset.id}`).then(d => { if (d.success) row.remove(); });
    });

    document.getElementById('addAssetBtn')?.addEventListener('click', function () {
        const name = document.getElementById('newAssetName').value.trim();
        if (!name) return;
        post(`${bookingId}/resources`, {
            name,
            type: 'asset',
            capacity: document.getElementById('newAssetQty').value || 1,
            extra_price: document.getElementById('newAssetExtraPrice').value || 0,
        }).then(data => {
            if (!data.success) return;
            document.getElementById('assetsEmptyHint')?.remove();
            const a = data.resource;
            const tbody = document.querySelector('#assetsTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = a.id;
            row.innerHTML = `<td>${a.name}</td><td>${a.capacity ?? '—'}</td><td>${Number(a.extra_price).toFixed(2)}</td><td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-asset"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.getElementById('newAssetName').value = '';
        });
    });

    document.getElementById('assetsTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-asset');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`resources/${row.dataset.id}`).then(d => { if (d.success) row.remove(); });
    });

    document.getElementById('addTimeSlotBtn')?.addEventListener('click', function () {
        const days = Array.from(document.querySelectorAll('#recurringBlock input[id^="newSlotDay_"]:checked')).map(el => el.value);
        const start = document.getElementById('newSlotStart').value;
        const end = document.getElementById('newSlotEnd').value;
        if (!days.length || !start || !end) {
            alert('Please pick at least one day, a start time, and an end time.');
            return;
        }
        post(`${bookingId}/time-slots`, {
            day_of_week: days,
            start_time: start,
            end_time: end,
            duration_minutes: document.getElementById('newSlotDuration').value || null,
            max_bookings: document.getElementById('newSlotMax').value || 1,
        }).then(data => {
            if (!data.success) return;
            document.getElementById('timeSlotsEmptyHint')?.remove();
            const s = data.time_slot;
            const tbody = document.querySelector('#timeSlotsTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = s.id;
            const dayLabels = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' };
            const daysText = (s.day_of_week || []).map(d => dayLabels[d] || d).join(', ');
            row.innerHTML = `<td>${daysText}</td><td>${s.start_time}</td><td>${s.end_time}</td><td>${s.duration_minutes ?? ''}</td><td>${s.max_bookings}</td><td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-timeslot"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.querySelectorAll('#recurringBlock input[id^="newSlotDay_"]').forEach(el => el.checked = false);
            document.getElementById('newSlotStart').value = '';
            document.getElementById('newSlotEnd').value = '';
            document.getElementById('newSlotDuration').value = '';
        });
    });

    document.getElementById('timeSlotsTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-timeslot');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`time-slots/${row.dataset.id}`).then(d => { if (d.success) row.remove(); });
    });

    // ── Availability Overrides (NEW) ──────────────────────
    document.getElementById('addAvailabilityBtn')?.addEventListener('click', function () {
        const date = document.getElementById('avDate').value;
        if (!date) {
            alert('Please pick a date.');
            return;
        }
        post(`${bookingId}/availability-overrides`, {
            date,
            resource_id: document.getElementById('avResourceId').value || null,
            is_available: document.getElementById('avIsAvailable').checked ? 'on' : '',
            slots_available: document.getElementById('avSlots').value || null,
            price_override: document.getElementById('avPrice').value || null,
            close_reason: document.getElementById('avReason').value || null,
        }).then(data => {
            if (!data.success) return;
            document.getElementById('availabilityEmptyHint')?.remove();
            const a = data.availability;
            const tbody = document.querySelector('#availabilityTable tbody');
            const resourceSelect = document.getElementById('avResourceId');
            const resourceText = resourceSelect.selectedOptions[0] ? resourceSelect.selectedOptions[0].text : 'Any / Whole Booking';
            const row = document.createElement('tr');
            row.dataset.id = a.id;
            row.innerHTML = `<td>${a.date}</td><td>${resourceText}</td><td>${a.is_available ? 'Open' : 'Blocked'}</td><td>${a.slots_available ?? '—'}</td><td>${a.price_override ?? '—'}</td><td>${a.close_reason ?? '—'}</td><td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-availability"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);

            document.getElementById('avDate').value = '';
            document.getElementById('avSlots').value = '';
            document.getElementById('avPrice').value = '';
            document.getElementById('avReason').value = '';
            document.getElementById('avIsAvailable').checked = true;
        });
    });

    document.getElementById('availabilityTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-availability');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`availability-overrides/${row.dataset.id}`).then(d => { if (d.success) row.remove(); });
    });
    @endif
})();
</script>