{{--
    Step 7 — Location & Filters
--}}
@php
    $locationEnabled = old('location_enabled', !empty($booking->location_enabled));
    $specGroups = $specifications ?? collect();
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-map-marker"></i></div>
    <div>
        <h6>Location &amp; Filters</h6>
        <p class="section-sub">Where this happens, and the attributes customers can filter by.</p>
    </div>
</div>

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
})();
</script>