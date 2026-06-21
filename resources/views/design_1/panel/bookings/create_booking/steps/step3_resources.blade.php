{{--
    Step 3 of 8 — Participants, Resources, Assets, Recurring
    Maps to Image 4 (toggles) + Image 5 (expanded sections), but built
    entirely on your EXISTING schema:
      - Participants  -> Booking.min_persons / max_persons / max_children / children_allowed
      - Resources     -> BookingResource rows where type != 'asset'
      - Assets        -> BookingResource rows where type  = 'asset'
      - Recurring     -> BookingTimeSlot rows (day_of_week, start_time, end_time, max_bookings)
--}}
@php
    $meta = $booking->meta ?? [];
    $participantsEnabled = old('participants_enabled', $meta['participants_enabled'] ?? false);
    $resourcesEnabled    = old('resources_enabled', $meta['resources_enabled'] ?? false);
    $assetsEnabled       = old('assets_enabled', $meta['assets_enabled'] ?? false);
    $recurringEnabled    = old('recurring_enabled', $meta['recurring_enabled'] ?? false);

    $resourceRows = ($booking->resources ?? collect())->where('type', '!=', 'asset');
    $assetRows    = ($booking->resources ?? collect())->where('type', '=', 'asset');
    $timeSlots    = $booking->timeSlots ?? collect();

    $weekDays = ['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri', 'sat' => 'Sat', 'sun' => 'Sun'];
@endphp

<form data-wiz-form id="stepResourcesForm">
    <h5 class="mb-1">Participants, Resources, Assets &amp; Recurring</h5>
    <p class="text-muted mb-4">Turn on only what this booking type needs.</p>

    {{-- ── Booking Participants ─────────────────────────────────── --}}
    <div class="booking-switch-row mb-2">
        <label class="booking-switch" for="toggleParticipants">
            <input type="checkbox" id="toggleParticipants" name="participants_enabled" value="1"
                   {{ $participantsEnabled ? 'checked' : '' }}
                   onchange="document.getElementById('participantsBlock').style.display = this.checked ? 'block' : 'none'">
            <span class="booking-switch-slider"></span>
        </label>
        <label class="booking-switch-label" for="toggleParticipants">Booking Participants</label>
    </div>

    <div id="participantsBlock" class="pl-2 mb-4 row" style="{{ $participantsEnabled ? '' : 'display:none' }}">
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
                <input class="form-check-input" type="checkbox" id="childrenAllowed" name="children_allowed" value="1"
                       {{ old('children_allowed', $booking->children_allowed) ? 'checked' : '' }}>
                <label class="form-check-label" for="childrenAllowed">Children allowed</label>
            </div>
        </div>
    </div>

    {{-- ── Booking Resources ────────────────────────────────────── --}}
    <div class="booking-switch-row mb-2">
        <label class="booking-switch" for="toggleResources">
            <input type="checkbox" id="toggleResources" name="resources_enabled" value="1"
                   {{ $resourcesEnabled ? 'checked' : '' }}
                   onchange="document.getElementById('resourcesBlock').style.display = this.checked ? 'block' : 'none'">
            <span class="booking-switch-slider"></span>
        </label>
        <label class="booking-switch-label" for="toggleResources">Booking Resources</label>
    </div>

    <div id="resourcesBlock" class="pl-2 mb-4" style="{{ $resourcesEnabled ? '' : 'display:none' }}">
        <table class="table table-sm" id="resourcesTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Extra Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($resourceRows as $r)
                    <tr data-id="{{ $r->id }}">
                        <td>{{ $r->name }}</td>
                        <td>{{ $r->type }}</td>
                        <td>{{ $r->capacity ?? '—' }}</td>
                        <td>{{ number_format($r->extra_price, 2) }}</td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger remove-resource"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row align-items-end">
            <div class="col-6 col-md-4"><input type="text" class="form-control form-control-sm" id="newResourceName" placeholder="e.g. Room 101"></div>
            <div class="col-6 col-md-3"><input type="text" class="form-control form-control-sm" id="newResourceType" placeholder="Type, e.g. room"></div>
            <div class="col-6 col-md-2"><input type="number" min="0" class="form-control form-control-sm" id="newResourceCapacity" placeholder="Capacity"></div>
            <div class="col-6 col-md-2"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="newResourceExtraPrice" placeholder="Extra price"></div>
            <div class="col-12 col-md-1">
                <button type="button" class="btn btn-sm btn-primary btn-block" id="addResourceBtn"><i class="fa fa-plus"></i></button>
            </div>
        </div>
        <small class="text-muted">Resource type can't be "asset" — that's reserved for the Assets section below.</small>
    </div>

    {{-- ── Booking Assets ───────────────────────────────────────── --}}
    <div class="booking-switch-row mb-2">
        <label class="booking-switch" for="toggleAssets">
            <input type="checkbox" id="toggleAssets" name="assets_enabled" value="1"
                   {{ $assetsEnabled ? 'checked' : '' }}
                   onchange="document.getElementById('assetsBlock').style.display = this.checked ? 'block' : 'none'">
            <span class="booking-switch-slider"></span>
        </label>
        <label class="booking-switch-label" for="toggleAssets">Booking Assets</label>
    </div>

    <div id="assetsBlock" class="pl-2 mb-4" style="{{ $assetsEnabled ? '' : 'display:none' }}">
        <table class="table table-sm" id="assetsTable">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Capacity / Qty</th>
                    <th>Extra Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($assetRows as $a)
                    <tr data-id="{{ $a->id }}">
                        <td>{{ $a->name }}</td>
                        <td>{{ $a->capacity ?? '—' }}</td>
                        <td>{{ number_format($a->extra_price, 2) }}</td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger remove-asset"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row align-items-end">
            <div class="col-6 col-md-5"><input type="text" class="form-control form-control-sm" id="newAssetName" placeholder="e.g. Kayak"></div>
            <div class="col-6 col-md-3"><input type="number" min="1" class="form-control form-control-sm" id="newAssetQty" placeholder="Qty / capacity" value="1"></div>
            <div class="col-6 col-md-3"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="newAssetExtraPrice" placeholder="Extra price"></div>
            <div class="col-12 col-md-1">
                <button type="button" class="btn btn-sm btn-primary btn-block" id="addAssetBtn"><i class="fa fa-plus"></i></button>
            </div>
        </div>
        <small class="text-muted">Stored as a Booking Resource with type = "asset".</small>
    </div>

    {{-- ── Recurring Bookings ───────────────────────────────────── --}}
    <div class="booking-switch-row mb-2">
        <label class="booking-switch" for="toggleRecurring">
            <input type="checkbox" id="toggleRecurring" name="recurring_enabled" value="1"
                   {{ $recurringEnabled ? 'checked' : '' }}
                   onchange="document.getElementById('recurringBlock').style.display = this.checked ? 'block' : 'none'">
            <span class="booking-switch-slider"></span>
        </label>
        <label class="booking-switch-label" for="toggleRecurring">
            Recurring Bookings
            <small>Sets up a weekly schedule of available time slots</small>
        </label>
    </div>

    <div id="recurringBlock" class="pl-2" style="{{ $recurringEnabled ? '' : 'display:none' }}">
        <table class="table table-sm" id="timeSlotsTable">
            <thead>
                <tr>
                    <th>Days</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Duration (min)</th>
                    <th>Max Bookings</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($timeSlots as $slot)
                    <tr data-id="{{ $slot->id }}">
                        <td>{{ implode(', ', array_map(fn($d) => $weekDays[$d] ?? $d, $slot->day_of_week ?? [])) }}</td>
                        <td>{{ $slot->start_time }}</td>
                        <td>{{ $slot->end_time }}</td>
                        <td>{{ $slot->duration_minutes }}</td>
                        <td>{{ $slot->max_bookings }}</td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger remove-timeslot"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row align-items-end">
            <div class="col-12 mb-2">
                <label class="d-block small mb-1">Days of week</label>
                @foreach($weekDays as $val => $label)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="newSlotDay_{{ $val }}" value="{{ $val }}">
                        <label class="form-check-label small" for="newSlotDay_{{ $val }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
            <div class="col-6 col-md-2"><label class="small">Start</label><input type="time" class="form-control form-control-sm" id="newSlotStart"></div>
            <div class="col-6 col-md-2"><label class="small">End</label><input type="time" class="form-control form-control-sm" id="newSlotEnd"></div>
            <div class="col-6 col-md-2"><label class="small">Duration (min)</label><input type="number" min="0" class="form-control form-control-sm" id="newSlotDuration"></div>
            <div class="col-6 col-md-2"><label class="small">Max bookings</label><input type="number" min="1" class="form-control form-control-sm" id="newSlotMax" value="1"></div>
            <div class="col-12 col-md-2">
                <button type="button" class="btn btn-sm btn-primary btn-block" id="addTimeSlotBtn">
                    <i class="fa fa-plus mr-1"></i> Add slot
                </button>
            </div>
        </div>
    </div>
</form>

<script>
(function () {
    const bookingId = document.getElementById('bookingWizardApp')?.dataset.bookingId;
    const baseUrl = '{{ url('panel/bookings/wizard') }}';
    const csrf = '{{ csrf_token() }}';

    function post(path, payload) {
        return fetch(`${baseUrl}/${path}`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        }).then(r => r.json());
    }

    function del(path) {
        return fetch(`${baseUrl}/${path}`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
        }).then(r => r.json());
    }

    // Resources
    document.getElementById('addResourceBtn')?.addEventListener('click', function () {
        const name = document.getElementById('newResourceName').value.trim();
        if (!name) return;
        const payload = {
            name,
            type: document.getElementById('newResourceType').value || 'resource',
            capacity: document.getElementById('newResourceCapacity').value || null,
            extra_price: document.getElementById('newResourceExtraPrice').value || 0,
        };
        post(`${bookingId}/resources`, payload).then(data => {
            if (!data.success) return;
            const r = data.resource;
            const tbody = document.querySelector('#resourcesTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = r.id;
            row.innerHTML = `<td>${r.name}</td><td>${r.type ?? ''}</td><td>${r.capacity ?? '—'}</td><td>${Number(r.extra_price).toFixed(2)}</td><td><button type="button" class="btn btn-sm btn-link text-danger remove-resource"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.getElementById('newResourceName').value = '';
        });
    });

    document.getElementById('resourcesTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-resource');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`resources/${row.dataset.id}`).then(data => { if (data.success) row.remove(); });
    });

    // Assets (same endpoint as resources, forced type = asset)
    document.getElementById('addAssetBtn')?.addEventListener('click', function () {
        const name = document.getElementById('newAssetName').value.trim();
        if (!name) return;
        const payload = {
            name,
            type: 'asset',
            capacity: document.getElementById('newAssetQty').value || 1,
            extra_price: document.getElementById('newAssetExtraPrice').value || 0,
        };
        post(`${bookingId}/resources`, payload).then(data => {
            if (!data.success) return;
            const a = data.resource;
            const tbody = document.querySelector('#assetsTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = a.id;
            row.innerHTML = `<td>${a.name}</td><td>${a.capacity ?? '—'}</td><td>${Number(a.extra_price).toFixed(2)}</td><td><button type="button" class="btn btn-sm btn-link text-danger remove-asset"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.getElementById('newAssetName').value = '';
        });
    });

    document.getElementById('assetsTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-asset');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`resources/${row.dataset.id}`).then(data => { if (data.success) row.remove(); });
    });

    // Recurring time slots
    document.getElementById('addTimeSlotBtn')?.addEventListener('click', function () {
        const days = Array.from(document.querySelectorAll('#recurringBlock input[id^="newSlotDay_"]:checked')).map(el => el.value);
        const start = document.getElementById('newSlotStart').value;
        const end = document.getElementById('newSlotEnd').value;
        if (!days.length || !start || !end) {
            alert('Please pick at least one day, a start time, and an end time.');
            return;
        }
        const payload = {
            day_of_week: days,
            start_time: start,
            end_time: end,
            duration_minutes: document.getElementById('newSlotDuration').value || null,
            max_bookings: document.getElementById('newSlotMax').value || 1,
        };
        post(`${bookingId}/time-slots`, payload).then(data => {
            if (!data.success) return;
            const s = data.time_slot;
            const tbody = document.querySelector('#timeSlotsTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = s.id;
            const dayLabels = { mon: 'Mon', tue: 'Tue', wed: 'Wed', thu: 'Thu', fri: 'Fri', sat: 'Sat', sun: 'Sun' };
            const daysText = (s.day_of_week || []).map(d => dayLabels[d] || d).join(', ');
            row.innerHTML = `<td>${daysText}</td><td>${s.start_time}</td><td>${s.end_time}</td><td>${s.duration_minutes ?? ''}</td><td>${s.max_bookings}</td><td><button type="button" class="btn btn-sm btn-link text-danger remove-timeslot"><i class="fa fa-trash"></i></button></td>`;
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
        del(`time-slots/${row.dataset.id}`).then(data => { if (data.success) row.remove(); });
    });
})();
</script>
