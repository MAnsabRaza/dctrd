{{--
    Step 3 of 8 — Participants, Resources, Assets, Recurring
    Maps directly to Image 4 (toggles) + Image 5 (expanded tables)
--}}
@php
    $meta = $booking->meta ?? [];
    $participantsEnabled = old('participants_enabled', $meta['participants_enabled'] ?? false);
    $resourcesEnabled    = old('resources_enabled', $meta['resources_enabled'] ?? false);
    $assetsEnabled       = old('assets_enabled', $meta['assets_enabled'] ?? false);
    $recurringEnabled    = old('recurring_enabled', $meta['recurring_enabled'] ?? false);
    $recurrence          = $booking->recurrence ?? null;
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

    <div id="participantsBlock" class="pl-2 mb-4" style="{{ $participantsEnabled ? '' : 'display:none' }}">
        <table class="table table-sm" id="participantsTable">
            <thead>
                <tr>
                    <th>Label / Type</th>
                    <th>Min</th>
                    <th>Max</th>
                    <th>Per-Participant Cost</th>
                    <th>Charge / Day</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->participants ?? [] as $p)
                    <tr data-id="{{ $p->id }}">
                        <td>{{ $p->label }}</td>
                        <td>{{ $p->min }}</td>
                        <td>{{ $p->max ?? '—' }}</td>
                        <td>{{ number_format($p->per_participant_cost, 2) }}</td>
                        <td>{{ $p->charge_per_day ? 'Yes' : 'No' }}</td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger remove-participant"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row align-items-end">
            <div class="col-6 col-md-3"><input type="text" class="form-control form-control-sm" id="newParticipantLabel" placeholder="e.g. Adults"></div>
            <div class="col-3 col-md-2"><input type="number" min="0" class="form-control form-control-sm" id="newParticipantMin" placeholder="Min" value="1"></div>
            <div class="col-3 col-md-2"><input type="number" min="0" class="form-control form-control-sm" id="newParticipantMax" placeholder="Max"></div>
            <div class="col-6 col-md-2"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="newParticipantCost" placeholder="Cost"></div>
            <div class="col-6 col-md-2">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="newParticipantChargePerDay">
                    <label class="form-check-label small" for="newParticipantChargePerDay">Per day</label>
                </div>
            </div>
            <div class="col-12 col-md-1">
                <button type="button" class="btn btn-sm btn-primary btn-block" id="addParticipantBtn"><i class="fa fa-plus"></i></button>
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
                @foreach($booking->resources ?? [] as $r)
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
                    <th>Type</th>
                    <th>Qty</th>
                    <th>Extra Price</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($booking->assets ?? [] as $a)
                    <tr data-id="{{ $a->id }}">
                        <td>{{ $a->name }}</td>
                        <td>{{ $a->type }}</td>
                        <td>{{ $a->quantity }}</td>
                        <td>{{ number_format($a->extra_price, 2) }}</td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger remove-asset"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row align-items-end">
            <div class="col-6 col-md-4"><input type="text" class="form-control form-control-sm" id="newAssetName" placeholder="e.g. Kayak"></div>
            <div class="col-6 col-md-3"><input type="text" class="form-control form-control-sm" id="newAssetType" placeholder="Type, e.g. equipment"></div>
            <div class="col-6 col-md-2"><input type="number" min="1" class="form-control form-control-sm" id="newAssetQty" placeholder="Qty" value="1"></div>
            <div class="col-6 col-md-2"><input type="number" step="0.01" min="0" class="form-control form-control-sm" id="newAssetExtraPrice" placeholder="Extra price"></div>
            <div class="col-12 col-md-1">
                <button type="button" class="btn btn-sm btn-primary btn-block" id="addAssetBtn"><i class="fa fa-plus"></i></button>
            </div>
        </div>
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
            <small>Repeats this booking automatically on a schedule</small>
        </label>
    </div>

    <div id="recurringBlock" class="pl-2" style="{{ $recurringEnabled ? '' : 'display:none' }}">
        <div class="row">
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Frequency</label>
                    <select name="recurrence[frequency]" class="form-control">
                        @foreach(['daily' => 'Daily', 'weekly' => 'Weekly', 'monthly' => 'Monthly'] as $val => $label)
                            <option value="{{ $val }}" {{ ($recurrence->frequency ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Repeat every</label>
                    <input type="number" min="1" name="recurrence[interval]" class="form-control" value="{{ $recurrence->interval ?? 1 }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Starts on</label>
                    <input type="date" name="recurrence[starts_on]" class="form-control" value="{{ $recurrence->starts_on ?? '' }}">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label>Ends on</label>
                    <input type="date" name="recurrence[ends_on]" class="form-control" value="{{ $recurrence->ends_on ?? '' }}">
                </div>
            </div>
            <div class="col-12">
                <label class="d-block">Days of week</label>
                @foreach(['mon','tue','wed','thu','fri','sat','sun'] as $day)
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" name="recurrence[days_of_week][]" value="{{ $day }}"
                               {{ in_array($day, $recurrence->days_of_week ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label small">{{ ucfirst($day) }}</label>
                    </div>
                @endforeach
            </div>
        </div>
        <small class="text-danger">Please ensure your Booking Period is set to "Fixed Blocks Of" for Recurrent Bookings to work.</small>
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

    // Participants
    document.getElementById('addParticipantBtn')?.addEventListener('click', function () {
        const label = document.getElementById('newParticipantLabel').value.trim();
        if (!label) return;
        const payload = {
            label,
            min: document.getElementById('newParticipantMin').value || 0,
            max: document.getElementById('newParticipantMax').value || null,
            per_participant_cost: document.getElementById('newParticipantCost').value || 0,
            charge_per_day: document.getElementById('newParticipantChargePerDay').checked ? 1 : 0,
        };
        post(`${bookingId}/participants`, payload).then(data => {
            if (!data.success) return;
            const p = data.participant;
            const tbody = document.querySelector('#participantsTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = p.id;
            row.innerHTML = `<td>${p.label}</td><td>${p.min}</td><td>${p.max ?? '—'}</td><td>${Number(p.per_participant_cost).toFixed(2)}</td><td>${p.charge_per_day ? 'Yes' : 'No'}</td><td><button type="button" class="btn btn-sm btn-link text-danger remove-participant"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.getElementById('newParticipantLabel').value = '';
        });
    });

    document.getElementById('participantsTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-participant');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`participants/${row.dataset.id}`).then(data => { if (data.success) row.remove(); });
    });

    // Resources
    document.getElementById('addResourceBtn')?.addEventListener('click', function () {
        const name = document.getElementById('newResourceName').value.trim();
        if (!name) return;
        const payload = {
            name,
            type: document.getElementById('newResourceType').value || null,
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

    // Assets
    document.getElementById('addAssetBtn')?.addEventListener('click', function () {
        const name = document.getElementById('newAssetName').value.trim();
        if (!name) return;
        const payload = {
            name,
            type: document.getElementById('newAssetType').value || null,
            quantity: document.getElementById('newAssetQty').value || 1,
            extra_price: document.getElementById('newAssetExtraPrice').value || 0,
        };
        post(`${bookingId}/assets`, payload).then(data => {
            if (!data.success) return;
            const a = data.asset;
            const tbody = document.querySelector('#assetsTable tbody');
            const row = document.createElement('tr');
            row.dataset.id = a.id;
            row.innerHTML = `<td>${a.name}</td><td>${a.type ?? ''}</td><td>${a.quantity}</td><td>${Number(a.extra_price).toFixed(2)}</td><td><button type="button" class="btn btn-sm btn-link text-danger remove-asset"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            document.getElementById('newAssetName').value = '';
        });
    });

    document.getElementById('assetsTable')?.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-asset');
        if (!btn) return;
        const row = btn.closest('tr');
        del(`assets/${row.dataset.id}`).then(data => { if (data.success) row.remove(); });
    });
})();
</script>
