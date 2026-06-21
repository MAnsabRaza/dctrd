{{--
    Step 7 — Location & Filters
--}}
@php
    $locationEnabled = old('location_enabled', !empty($booking->location_enabled));
    $specGroups = $specifications ?? collect();
@endphp

<h5 class="mb-1">Location &amp; Filters</h5>
<p class="text-muted mb-4">Where this happens, and the attributes customers can filter by.</p>

<div class="booking-switch-row mb-3">
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

<div id="locationFieldsStep7" style="{{ $locationEnabled ? 'display:block' : 'display:none' }}">
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

<hr class="my-4">

<h6 class="font-weight-bold mb-3">Category Filters</h6>

@if($specGroups->isEmpty())
    <div class="text-muted">Select a category in Step 1 to see its available filters here.</div>
@else
    <div class="row">
        @foreach($specGroups as $spec)
            <div class="col-12 col-md-4 mb-3">
                <strong class="d-block small mb-1">{{ $spec->title }}</strong>
                @foreach($spec->bookingValues as $val)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="specification_values[]"
                               value="{{ $val->id }}" id="step7spec_{{ $val->id }}"
                               {{ in_array($val->id, $selectedSpecValueIds ?? []) ? 'checked' : '' }}>
                        <label class="form-check-label small" for="step7spec_{{ $val->id }}">{{ $val->value }}</label>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
@endif

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
