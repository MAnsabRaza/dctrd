@php
    $meta = $booking->meta ?? [];
    $resources = $booking->resources ?? collect();
    $timeSlots = $booking->timeSlots ?? collect();

    $participantsEnabled = old('participants_enabled', !empty($meta['participants_enabled']) ? 'on' : null);
    $resourcesEnabled = old('resources_enabled', !empty($meta['resources_enabled']) ? 'on' : null);
    $assetsEnabled = old('assets_enabled', !empty($meta['assets_enabled']) ? 'on' : null);
    $recurringEnabled = old('recurring_enabled', !empty($meta['recurring_enabled']) ? 'on' : null);

    $dayLabels = [
        'mon' => 'Mon',
        'tue' => 'Tue',
        'wed' => 'Wed',
        'thu' => 'Thu',
        'fri' => 'Fri',
        'sat' => 'Sat',
        'sun' => 'Sun',
    ];
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-users"></i></div>
    <div>
        <h6>Participants &amp; Resources</h6>
        <p class="section-sub">Capacity, staff/resources, and recurring availability for this booking.</p>
    </div>
</div>

<div class="panel-card">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-user-friends"></i></div>
        <div>
            <h6>Participants</h6>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-md-4 col-lg">
            <label class="custom-switch pl-0">
                <input type="checkbox" name="participants_enabled" class="custom-switch-input" {{ $participantsEnabled ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">Enable participants</span>
            </label>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <label class="custom-switch pl-0">
                <input type="checkbox" name="children_allowed" class="custom-switch-input" {{ old('children_allowed', !empty($booking->children_allowed) ? 'on' : null) ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">Children allowed</span>
            </label>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <label class="custom-switch pl-0">
                <input type="checkbox" name="resources_enabled" class="custom-switch-input" {{ $resourcesEnabled ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">Enable resources</span>
            </label>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <label class="custom-switch pl-0">
                <input type="checkbox" name="assets_enabled" class="custom-switch-input" {{ $assetsEnabled ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">Enable assets</span>
            </label>
        </div>
        <div class="col-12 col-md-4 col-lg">
            <label class="custom-switch pl-0">
                <input type="checkbox" name="recurring_enabled" class="custom-switch-input" {{ $recurringEnabled ? 'checked' : '' }}>
                <span class="custom-switch-indicator"></span>
                <span class="custom-switch-description">Recurring slots</span>
            </label>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12 col-md-4">
            <div class="form-group">
                <label>Minimum Persons</label>
                <input type="number" min="0" name="min_persons" class="form-control @error('min_persons') is-invalid @enderror" value="{{ old('min_persons', $booking->min_persons) }}">
                @error('min_persons')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group">
                <label>Maximum Persons</label>
                <input type="number" min="0" name="max_persons" class="form-control @error('max_persons') is-invalid @enderror" value="{{ old('max_persons', $booking->max_persons) }}">
                @error('max_persons')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="form-group">
                <label>Maximum Children</label>
                <input type="number" min="0" name="max_children" class="form-control @error('max_children') is-invalid @enderror" value="{{ old('max_children', $booking->max_children) }}">
                @error('max_children')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>
</div>

<div class="panel-card">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-briefcase"></i></div>
        <div>
            <h6>Resources &amp; Staff</h6>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-striped table-md mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Capacity</th>
                    <th>Extra Price</th>
                    <th>Description</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody id="bookingResourcesBody">
                @forelse($resources as $resource)
                    <tr data-resource-row="{{ $resource->id }}">
                        <td>{{ $resource->name }}</td>
                        <td>{{ ucfirst($resource->type ?: 'resource') }}</td>
                        <td>{{ $resource->capacity ?? 1 }}</td>
                        <td>{{ number_format((float) ($resource->extra_price ?? 0), 2) }}</td>
                        <td>{{ $resource->description }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger js-delete-resource" data-url="{{ route('panel.bookings.resources.destroy', $resource->id) }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-title">No resources added yet.</div>
                                <div class="empty-sub">Add staff, rooms, vehicles, or equipment for this booking.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row align-items-end">
        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Name</label>
                <input type="text" class="form-control" id="newResourceName" placeholder="Room 1, Staff name">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>Type</label>
                <select class="form-control" id="newResourceType">
                    <option value="resource">Resource</option>
                    <option value="staff">Staff</option>
                    <option value="room">Room</option>
                    <option value="vehicle">Vehicle</option>
                    <option value="asset">Asset</option>
                </select>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>Capacity</label>
                <input type="number" min="0" class="form-control" id="newResourceCapacity" value="1">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>Extra Price</label>
                <input type="number" min="0" step="0.01" class="form-control" id="newResourceExtraPrice" value="0">
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Description</label>
                <input type="text" class="form-control" id="newResourceDescription">
            </div>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-outline-primary" id="addBookingResource" data-url="{{ route('panel.bookings.resources.store', $booking->id) }}">
                <i class="fa fa-plus mr-1"></i> Add Resource
            </button>
        </div>
    </div>
</div>

<div class="panel-card mb-0">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-clock"></i></div>
        <div>
            <h6>Recurring Time Slots</h6>
        </div>
    </div>

    <div class="table-responsive mb-3">
        <table class="table table-striped table-md mb-0">
            <thead>
                <tr>
                    <th>Days</th>
                    <th>Time</th>
                    <th>Resource</th>
                    <th>Duration</th>
                    <th>Buffer</th>
                    <th>Max Bookings</th>
                    <th class="text-right">Action</th>
                </tr>
            </thead>
            <tbody id="bookingTimeSlotsBody">
                @forelse($timeSlots as $slot)
                    @php
                        $slotDays = collect($slot->day_of_week ?? [])->map(fn ($day) => $dayLabels[$day] ?? ucfirst($day))->implode(', ');
                        $slotResource = $resources->firstWhere('id', $slot->resource_id);
                    @endphp
                    <tr data-time-slot-row="{{ $slot->id }}">
                        <td>{{ $slotDays ?: '-' }}</td>
                        <td>{{ substr((string) $slot->start_time, 0, 5) }} - {{ substr((string) $slot->end_time, 0, 5) }}</td>
                        <td>{{ $slotResource->name ?? 'Any resource' }}</td>
                        <td>{{ $slot->duration_minutes ?? 60 }} min</td>
                        <td>{{ $slot->buffer_minutes ?? 0 }} min</td>
                        <td>{{ $slot->max_bookings ?? 1 }}</td>
                        <td class="text-right">
                            <button type="button" class="btn btn-sm btn-outline-danger js-delete-time-slot" data-url="{{ route('panel.bookings.time_slots.destroy', $slot->id) }}">
                                <i class="fa fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7">
                            <div class="empty-state">
                                <div class="empty-title">No recurring slots added yet.</div>
                                <div class="empty-sub">Add weekly slots to make this booking available.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="row align-items-end">
        <div class="col-12">
            <label class="d-block">Days</label>
            <div class="d-flex flex-wrap mb-3">
                @foreach($dayLabels as $value => $label)
                    <label class="custom-checkbox mr-3 mb-2">
                        <input type="checkbox" class="js-slot-day" value="{{ $value }}">
                        <span>{{ $label }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>Start</label>
                <input type="time" class="form-control" id="newSlotStart" value="09:00">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>End</label>
                <input type="time" class="form-control" id="newSlotEnd" value="10:00">
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Resource</label>
                <select class="form-control" id="newSlotResource">
                    <option value="">Any resource</option>
                    @foreach($resources as $resource)
                        <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>Duration</label>
                <input type="number" min="0" class="form-control" id="newSlotDuration" value="{{ $booking->duration_minutes ?? 60 }}">
            </div>
        </div>
        <div class="col-6 col-md-1">
            <div class="form-group">
                <label>Buffer</label>
                <input type="number" min="0" class="form-control" id="newSlotBuffer" value="0">
            </div>
        </div>
        <div class="col-6 col-md-2">
            <div class="form-group">
                <label>Max</label>
                <input type="number" min="1" class="form-control" id="newSlotMaxBookings" value="1">
            </div>
        </div>
        <div class="col-12">
            <button type="button" class="btn btn-outline-primary" id="addBookingTimeSlot" data-url="{{ route('panel.bookings.time_slots.store', $booking->id) }}">
                <i class="fa fa-plus mr-1"></i> Add Time Slot
            </button>
        </div>
    </div>
</div>

@push('scripts_bottom')
<script>
(function () {
    const token = document.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
    const resourceDeleteBaseUrl = '{{ url('/panel/bookings/resources') }}';
    const timeSlotDeleteBaseUrl = '{{ url('/panel/bookings/time-slots') }}';

    function removeEmptyRow(tbody) {
        tbody?.querySelector('.empty-row')?.remove();
    }

    function appendResourceRow(resource) {
        const tbody = document.getElementById('bookingResourcesBody');
        removeEmptyRow(tbody);
        const row = document.createElement('tr');
        row.dataset.resourceRow = resource.id;
        row.innerHTML = `
            <td>${escapeHtml(resource.name || '')}</td>
            <td>${escapeHtml(resource.type || 'resource')}</td>
            <td>${resource.capacity ?? 1}</td>
            <td>${Number(resource.extra_price || 0).toFixed(2)}</td>
            <td>${escapeHtml(resource.description || '')}</td>
            <td class="text-right">
                <button type="button" class="btn btn-sm btn-outline-danger js-delete-resource" data-url="${resourceDeleteBaseUrl}/${resource.id}">
                    <i class="fa fa-trash"></i>
                </button>
            </td>`;
        tbody.appendChild(row);

        const select = document.getElementById('newSlotResource');
        if (select) {
            const option = document.createElement('option');
            option.value = resource.id;
            option.textContent = resource.name;
            select.appendChild(option);
        }
    }

    function appendTimeSlotRow(slot) {
        const tbody = document.getElementById('bookingTimeSlotsBody');
        removeEmptyRow(tbody);
        const days = Array.isArray(slot.day_of_week) ? slot.day_of_week.join(', ') : (slot.day_of_week || '-');
        const row = document.createElement('tr');
        row.dataset.timeSlotRow = slot.id;
        row.innerHTML = `
            <td>${escapeHtml(days)}</td>
            <td>${escapeHtml((slot.start_time || '').slice(0, 5))} - ${escapeHtml((slot.end_time || '').slice(0, 5))}</td>
            <td>${escapeHtml(document.getElementById('newSlotResource')?.selectedOptions[0]?.textContent || 'Any resource')}</td>
            <td>${slot.duration_minutes || 60} min</td>
            <td>${slot.buffer_minutes || 0} min</td>
            <td>${slot.max_bookings || 1}</td>
            <td class="text-right">
                <button type="button" class="btn btn-sm btn-outline-danger js-delete-time-slot" data-url="${timeSlotDeleteBaseUrl}/${slot.id}">
                    <i class="fa fa-trash"></i>
                </button>
            </td>`;
        tbody.appendChild(row);
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    document.getElementById('addBookingResource')?.addEventListener('click', function () {
        const payload = new FormData();
        payload.append('_token', token);
        payload.append('name', document.getElementById('newResourceName')?.value || '');
        payload.append('type', document.getElementById('newResourceType')?.value || 'resource');
        payload.append('capacity', document.getElementById('newResourceCapacity')?.value || 1);
        payload.append('extra_price', document.getElementById('newResourceExtraPrice')?.value || 0);
        payload.append('description', document.getElementById('newResourceDescription')?.value || '');

        fetch(this.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: payload
        }).then(response => response.json()).then(result => {
            if (result.success && result.resource) {
                appendResourceRow(result.resource);
                document.getElementById('newResourceName').value = '';
                document.getElementById('newResourceDescription').value = '';
            }
        });
    });

    document.getElementById('addBookingTimeSlot')?.addEventListener('click', function () {
        const days = Array.from(document.querySelectorAll('.js-slot-day:checked')).map(input => input.value);
        const payload = new FormData();
        payload.append('_token', token);
        days.forEach(day => payload.append('day_of_week[]', day));
        payload.append('start_time', document.getElementById('newSlotStart')?.value || '');
        payload.append('end_time', document.getElementById('newSlotEnd')?.value || '');
        payload.append('resource_id', document.getElementById('newSlotResource')?.value || '');
        payload.append('duration_minutes', document.getElementById('newSlotDuration')?.value || 60);
        payload.append('buffer_minutes', document.getElementById('newSlotBuffer')?.value || 0);
        payload.append('max_bookings', document.getElementById('newSlotMaxBookings')?.value || 1);

        fetch(this.dataset.url, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
            body: payload
        }).then(response => response.json()).then(result => {
            if (result.success && result.time_slot) {
                appendTimeSlotRow(result.time_slot);
            }
        });
    });

    document.addEventListener('click', function (event) {
        const resourceButton = event.target.closest('.js-delete-resource');
        const slotButton = event.target.closest('.js-delete-time-slot');
        const button = resourceButton || slotButton;
        if (!button) {
            return;
        }

        fetch(button.dataset.url, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
        }).then(response => response.json()).then(result => {
            if (result.success) {
                button.closest('tr')?.remove();
            }
        });
    });
})();
</script>
@endpush
