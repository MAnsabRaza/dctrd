{{--
    Step 7 — Location & Filters
--}}
@php
    $locationEnabled = old('location_enabled', !empty($booking->location_enabled));
    $specGroups = $specifications ?? collect();
    $tplFields  = $config->fields();
    $meta       = $booking->meta ?? [];

    $hasField = fn (string $field) => in_array($field, $tplFields);

    $amenityOptions  = $config->filters()['meta.amenities']['options'] ?? ['wifi','pool','parking','gym','spa','breakfast','ac','kitchen'];
    $vehicleTypeOptions = $config->filters()['meta.vehicle_type']['options'] ?? ['car','suv','van','truck','motorcycle','bus'];
    $venueTypeOptions = ['indoor' => 'Indoor', 'outdoor' => 'Outdoor', 'hybrid' => 'Hybrid', 'online' => 'Online'];
    $levelOptions = $config->meta()['level_options'] ?? ['beginner','intermediate','advanced','all'];

    $existingExtraFees  = old('meta.extra_fees', $meta['extra_fees'] ?? []);
    $existingTicketTypes = old('meta.ticket_types', $meta['ticket_types'] ?? []);
    $existingExtras      = old('meta.extras', $meta['extras'] ?? []);
    $selectedAmenities   = old('meta.amenities', $meta['amenities'] ?? []);
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-sliders"></i></div>
    <div>
        <h6>{{ $config->label() }} Details</h6>
        <p class="section-sub">Fields specific to this booking template.</p>
    </div>
</div>

{{-- ── Staff / Provider (reuses Step 3 Resources block, type='staff') ──── --}}
@if($config->hasStaff())
    @php $staffList = $staffResources ?? collect(); @endphp
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-user"></i></div>
            <div><h6>Staff / Provider</h6></div>
        </div>

        @if($staffList->isEmpty())
            <div class="empty-state">
                <div class="badge-icon"><i class="fa fa-user-plus"></i></div>
                <div class="empty-title">No staff added yet</div>
                <div class="empty-sub">
                    Go back to <strong>Step 3 → Booking Resources</strong> and add a resource with type
                    <code>staff</code> for each staff member / provider, then come back here to select the default one.
                </div>
            </div>
        @else
            <div class="form-group mb-0">
                <label>Default Staff / Provider</label>
                <select name="meta[staff_id]" class="form-control">
                    <option value="">No specific staff (any available)</option>
                    @foreach($staffList as $staff)
                        <option value="{{ $staff->id }}" {{ old('meta.staff_id', $meta['staff_id'] ?? '') == $staff->id ? 'selected' : '' }}>
                            {{ $staff->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        @endif
    </div>
@endif

{{-- ── Beauty / Spa: Extras & Add-ons ──────────────────────────────────── --}}
@if($hasField('extras'))
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-plus-square"></i></div>
            <div><h6>Extras / Add-ons</h6></div>
        </div>
        <div class="mini-table-wrap">
            <table class="mini-table" id="extrasTable">
                <thead><tr><th>Name</th><th>Price</th><th></th></tr></thead>
                <tbody>
                    @foreach($existingExtras as $i => $extra)
                        <tr>
                            <td><input type="text" class="form-control form-control-sm" name="meta[extras][{{ $i }}][name]" value="{{ $extra['name'] ?? '' }}" placeholder="e.g. Hot towel"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="meta[extras][{{ $i }}][price]" value="{{ $extra['price'] ?? '' }}" placeholder="0.00"></td>
                            <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(empty($existingExtras))
                <div class="empty-state" id="extrasEmptyHint"><div class="empty-title">No extras yet</div></div>
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addExtraRow"><i class="fa fa-plus mr-1"></i> Add extra</button>
    </div>
@endif

{{-- ── Doctors / Clinics ────────────────────────────────────────────────── --}}
@if($hasField('meta.appointment_type'))
    <div class="panel-card">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group mb-0">
                    <label>Service Type</label>
                    <select name="meta[appointment_type]" class="form-control">
                        <option value="">Select type</option>
                        @foreach(['consultation' => 'Consultation', 'diagnostic' => 'Diagnostic', 'therapy' => 'Therapy', 'checkup' => 'Checkup'] as $val => $label)
                            <option value="{{ $val }}" {{ old('meta.appointment_type', $meta['appointment_type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-0">
                    <label>Payment Option</label>
                    <select name="meta[payment_option]" class="form-control">
                        @foreach(['per_appointment' => 'Per Appointment', 'quote_based' => 'Quote Based', 'insurance' => 'Insurance'] as $val => $label)
                            <option value="{{ $val }}" {{ old('meta.payment_option', $meta['payment_option'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ── Online meeting link (doctors / professional services / education) ── --}}
@if($hasField('meta.online_link'))
    <div class="panel-card">
        <div class="form-group mb-0">
            <label>Online Meeting Link</label>
            <input type="url" name="meta[online_link]" class="form-control" placeholder="https://zoom.us/..."
                   value="{{ old('meta.online_link', $meta['online_link'] ?? '') }}">
            <small class="text-muted">Only needed if Sub Type (Step 1) includes "online".</small>
        </div>
    </div>
@endif

{{-- ── Professional Services: required docs ──────────────────────────────── --}}
@if($hasField('meta.required_docs'))
    <div class="panel-card">
        <div class="form-group mb-0">
            <label>Required Notes / Documents</label>
            <textarea name="meta[required_docs]" rows="3" class="form-control"
                      placeholder="e.g. Please bring your ID and last invoice">{{ old('meta.required_docs', $meta['required_docs'] ?? '') }}</textarea>
        </div>
    </div>
@endif

{{-- ── Education / Training: level ─────────────────────────────────────── --}}
@if($hasField('meta.level'))
    <div class="panel-card">
        <div class="form-group mb-0">
            <label>Level</label>
            <select name="meta[level]" class="form-control">
                @foreach($levelOptions as $lvl)
                    <option value="{{ $lvl }}" {{ old('meta.level', $meta['level'] ?? '') === $lvl ? 'selected' : '' }}>{{ ucfirst($lvl) }}</option>
                @endforeach
            </select>
        </div>
    </div>
@endif

{{-- ── Accommodation: room type, amenities, extra fees ────────────────────── --}}
@if($hasField('meta.room_type'))
    <div class="panel-card">
        <div class="form-group mb-0">
            <label>Room / Unit Type</label>
            <input type="text" name="meta[room_type]" class="form-control" placeholder="e.g. Deluxe Double Room"
                   value="{{ old('meta.room_type', $meta['room_type'] ?? '') }}">
        </div>
    </div>
@endif

@if($hasField('meta.amenities'))
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-check-circle"></i></div>
            <div><h6>Amenities</h6></div>
        </div>
        @foreach($amenityOptions as $amenity)
            <label class="chip-check mr-1 mb-1">
                <input type="checkbox" name="meta[amenities][]" value="{{ $amenity }}"
                       {{ in_array($amenity, $selectedAmenities) ? 'checked' : '' }}>
                <span class="chip-box">{{ ucfirst($amenity) }}</span>
            </label>
        @endforeach
    </div>
@endif

@if($hasField('meta.extra_fees'))
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-dollar"></i></div>
            <div><h6>Extra Fees</h6></div>
        </div>
        <div class="mini-table-wrap">
            <table class="mini-table" id="extraFeesTable">
                <thead><tr><th>Fee Name</th><th>Amount</th><th></th></tr></thead>
                <tbody>
                    @foreach($existingExtraFees as $i => $fee)
                        <tr>
                            <td><input type="text" class="form-control form-control-sm" name="meta[extra_fees][{{ $i }}][name]" value="{{ $fee['name'] ?? '' }}" placeholder="e.g. Cleaning fee"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="meta[extra_fees][{{ $i }}][amount]" value="{{ $fee['amount'] ?? '' }}" placeholder="0.00"></td>
                            <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(empty($existingExtraFees))
                <div class="empty-state" id="extraFeesEmptyHint"><div class="empty-title">No extra fees yet</div></div>
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addExtraFeeRow"><i class="fa fa-plus mr-1"></i> Add fee</button>
    </div>
@endif

{{-- ── Automotive: rental fields (only when sub_type = rental) ───────────── --}}
@if($hasField('meta.vehicle_type'))
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-car"></i></div>
            <div><h6>Vehicle</h6></div>
        </div>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Vehicle Type</label>
                    <select name="meta[vehicle_type]" class="form-control">
                        <option value="">Select type</option>
                        @foreach($vehicleTypeOptions as $vt)
                            <option value="{{ $vt }}" {{ old('meta.vehicle_type', $meta['vehicle_type'] ?? '') === $vt ? 'selected' : '' }}>{{ ucfirst($vt) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Vehicle Specifications</label>
                    <input type="text" name="meta[vehicle_specs]" class="form-control" placeholder="e.g. Automatic, Diesel, 5 seats"
                           value="{{ old('meta.vehicle_specs', $meta['vehicle_specs'] ?? '') }}">
                </div>
            </div>
        </div>

        @if($booking->sub_type === 'rental')
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Pickup Location</label>
                        <input type="text" name="meta[pickup_location]" class="form-control"
                               value="{{ old('meta.pickup_location', $meta['pickup_location'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Drop-off Location</label>
                        <input type="text" name="meta[dropoff_location]" class="form-control"
                               value="{{ old('meta.dropoff_location', $meta['dropoff_location'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Default Pickup Date/Time</label>
                        <input type="datetime-local" name="meta[pickup_datetime]" class="form-control"
                               value="{{ old('meta.pickup_datetime', $meta['pickup_datetime'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label>Default Return Date/Time</label>
                        <input type="datetime-local" name="meta[return_datetime]" class="form-control"
                               value="{{ old('meta.return_datetime', $meta['return_datetime'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-group mb-0">
                        <label>Mileage Limit</label>
                        <input type="text" name="meta[mileage_limit]" class="form-control" placeholder="e.g. 200km/day"
                               value="{{ old('meta.mileage_limit', $meta['mileage_limit'] ?? '') }}">
                    </div>
                </div>
            </div>
        @elseif($booking->sub_type === 'service')
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Service Type</label>
                        <input type="text" name="meta[service_type]" class="form-control" placeholder="e.g. Oil change, Brake repair"
                               value="{{ old('meta.service_type', $meta['service_type'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Vehicle Type (for this service)</label>
                        <input type="text" name="meta[vehicle_type_service]" class="form-control"
                               value="{{ old('meta.vehicle_type_service', $meta['vehicle_type_service'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label>Estimated Price / Quote</label>
                        <input type="number" step="0.01" min="0" name="meta[estimated_price]" class="form-control"
                               value="{{ old('meta.estimated_price', $meta['estimated_price'] ?? '') }}">
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-group mb-0">
                        <label>Required Notes / Vehicle Details</label>
                        <textarea name="meta[required_notes]" rows="2" class="form-control">{{ old('meta.required_notes', $meta['required_notes'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif

{{-- ── Events: ticket types, venue type, organizer ─────────────────────── --}}
@if($hasField('meta.venue_type') || $hasField('meta.organizer'))
    <div class="panel-card">
        <div class="row">
            @if($hasField('meta.venue_type'))
                <div class="col-12 col-md-6">
                    <div class="form-group mb-0">
                        <label>Venue Type</label>
                        <select name="meta[venue_type]" class="form-control">
                            <option value="">Select venue type</option>
                            @foreach($venueTypeOptions as $val => $label)
                                <option value="{{ $val }}" {{ old('meta.venue_type', $meta['venue_type'] ?? '') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            @endif
            @if($hasField('meta.organizer'))
                <div class="col-12 col-md-6">
                    <div class="form-group mb-0">
                        <label>Organizer / Provider</label>
                        <input type="text" name="meta[organizer]" class="form-control"
                               value="{{ old('meta.organizer', $meta['organizer'] ?? '') }}">
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

@if($hasField('meta.ticket_types'))
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-ticket"></i></div>
            <div>
                <h6>Ticket Types</h6>
                <p class="section-sub">Optional — leave empty to sell a single ticket type at the base price set in Step 2.</p>
            </div>
        </div>
        <div class="mini-table-wrap">
            <table class="mini-table" id="ticketTypesTable">
                <thead><tr><th>Name</th><th>Price</th><th>Count</th><th></th></tr></thead>
                <tbody>
                    @foreach($existingTicketTypes as $i => $tt)
                        <tr>
                            <td><input type="text" class="form-control form-control-sm" name="meta[ticket_types][{{ $i }}][name]" value="{{ $tt['name'] ?? '' }}" placeholder="e.g. VIP"></td>
                            <td><input type="number" step="0.01" class="form-control form-control-sm" name="meta[ticket_types][{{ $i }}][price]" value="{{ $tt['price'] ?? '' }}" placeholder="0.00"></td>
                            <td><input type="number" min="0" class="form-control form-control-sm" name="meta[ticket_types][{{ $i }}][count]" value="{{ $tt['count'] ?? '' }}" placeholder="Qty"></td>
                            <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fa fa-trash"></i></button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @if(empty($existingTicketTypes))
                <div class="empty-state" id="ticketTypesEmptyHint"><div class="empty-title">No ticket types yet</div></div>
            @endif
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addTicketTypeRow"><i class="fa fa-plus mr-1"></i> Add ticket type</button>
    </div>
@endif

<div class="panel-card">
    <div class="booking-switch-row">
        <label class="booking-switch" for="locationSwitch">
            <input type="checkbox" id="locationSwitch" name="location_enabled"
                   {{ $locationEnabled ? 'checked' : '' }}
                   onchange="document.getElementById('locationFieldsStep7').style.display = this.checked ? 'block' : 'none'">
            <span class="booking-switch-slider"></span>
        </label>
        <label class="booking-switch-label" for="locationSwitch">
            Enable location
            <small>Show address &amp; map coordinates</small>
        </label>
    </div>

    <div id="locationFieldsStep7" class="mt-3" style="{{ $locationEnabled ? 'display:block' : 'display:none' }}">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Address line</label>
                    <input name="address_line" type="text" class="form-control" value="{{ old('address_line', $booking->address_line) }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>City</label>
                    <input name="city" type="text" class="form-control" value="{{ old('city', $booking->city) }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>State / Province</label>
                    <input name="state" type="text" class="form-control" value="{{ old('state', $booking->state) }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Country</label>
                    <input name="country" type="text" class="form-control" value="{{ old('country', $booking->country) }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Postal code</label>
                    <input name="postal_code" type="text" class="form-control" value="{{ old('postal_code', $booking->postal_code) }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Latitude</label>
                    <input id="step7Lat" name="lat" type="number" step="0.000001" class="form-control" value="{{ old('lat', $booking->lat) }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Longitude</label>
                    <input id="step7Lng" name="lng" type="number" step="0.000001" class="form-control" value="{{ old('lng', $booking->lng) }}">
                </div>
            </div>
            <div class="col-12">
                <div class="booking-map-preview">
                    <iframe id="step7MapFrame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="about:blank"></iframe>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="panel-card mb-0">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-filter"></i></div>
        <div><h6>Category Filters</h6></div>
    </div>

    @if($specGroups->isEmpty())
        <div class="empty-state">
            <div class="badge-icon"><i class="fa fa-filter"></i></div>
            <div class="empty-title">No filters available</div>
            <div class="empty-sub">Select a category in Step 1 to see its available filters here.</div>
        </div>
    @else
        <div class="row">
            @foreach($specGroups as $spec)
                <div class="col-12 col-md-4 mb-4">
                    <strong class="d-block small mb-2">{{ $spec->title }}</strong>
                    <div>
                        @foreach($spec->bookingValues as $val)
                            <label class="chip-check mr-1 mb-1">
                                <input type="checkbox" name="specification_values[]"
                                       value="{{ $val->id }}" id="step7spec_{{ $val->id }}"
                                       {{ in_array($val->id, $selectedSpecValueIds ?? []) ? 'checked' : '' }}>
                                <span class="chip-box">{{ $val->value }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

<script>
(function () {
    function updateMap() {
        const lat = document.getElementById('step7Lat')?.value;
        const lng = document.getElementById('step7Lng')?.value;
        const frame = document.getElementById('step7MapFrame');
        if (!lat || !lng || !frame) return;
        const latitude = parseFloat(lat), longitude = parseFloat(lng);
        if (isNaN(latitude) || isNaN(longitude)) return;
        const delta = 0.01;
        const bbox = [longitude - delta, latitude - delta, longitude + delta, latitude + delta].join(',');
        frame.src = `https://www.openstreetmap.org/export/embed.html?bbox=${bbox}&layer=mapnik&marker=${latitude},${longitude}`;
    }
    document.getElementById('step7Lat')?.addEventListener('input', updateMap);
    document.getElementById('step7Lng')?.addEventListener('input', updateMap);
    updateMap();

    // Generic remove-row handler for the extras / extra fees / ticket types tables
    document.querySelectorAll('#extrasTable, #extraFeesTable, #ticketTypesTable').forEach(function (table) {
        table.addEventListener('click', function (e) {
            if (e.target.closest('.remove-row')) e.target.closest('tr').remove();
        });
    });

    function addRepeaterRow(buttonId, tableSelector, emptyHintId, fieldName, columns) {
        let index = document.querySelectorAll(tableSelector + ' tbody tr').length;
        document.getElementById(buttonId)?.addEventListener('click', function () {
            document.getElementById(emptyHintId)?.remove();
            const tbody = document.querySelector(tableSelector + ' tbody');
            const row = document.createElement('tr');
            row.innerHTML = columns.map(function (col) {
                return `<td><input type="${col.type}" ${col.step ? 'step="0.01"' : ''} class="form-control form-control-sm" name="meta[${fieldName}][${index}][${col.key}]" placeholder="${col.placeholder}"></td>`;
            }).join('') + `<td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-row"><i class="fa fa-trash"></i></button></td>`;
            tbody.appendChild(row);
            index++;
        });
    }

    addRepeaterRow('addExtraRow', '#extrasTable', 'extrasEmptyHint', 'extras', [
        { key: 'name', type: 'text', placeholder: 'e.g. Hot towel' },
        { key: 'price', type: 'number', step: true, placeholder: '0.00' },
    ]);
    addRepeaterRow('addExtraFeeRow', '#extraFeesTable', 'extraFeesEmptyHint', 'extra_fees', [
        { key: 'name', type: 'text', placeholder: 'e.g. Cleaning fee' },
        { key: 'amount', type: 'number', step: true, placeholder: '0.00' },
    ]);
    addRepeaterRow('addTicketTypeRow', '#ticketTypesTable', 'ticketTypesEmptyHint', 'ticket_types', [
        { key: 'name', type: 'text', placeholder: 'e.g. VIP' },
        { key: 'price', type: 'number', step: true, placeholder: '0.00' },
        { key: 'count', type: 'number', placeholder: 'Qty' },
    ]);
})();
</script>