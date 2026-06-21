{{--
    Step 7 of 8 — Location & Filters
    Reuses your existing location toggle + OSM map pattern, plus the
    category specifications (Image 8: Logistics, Amenities, Requirements).
--}}
<style>
.booking-switch-row { display: flex; align-items: center; gap: 12px; padding: 6px 0; }
.booking-switch { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
.booking-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
.booking-switch-slider { position: absolute; inset: 0; background: #ccc; border-radius: 26px; cursor: pointer; transition: background .2s; }
.booking-switch-slider:before { content: ''; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.booking-switch input:checked + .booking-switch-slider { background: #2196F3; }
.booking-switch input:checked + .booking-switch-slider:before { transform: translateX(22px); }
.booking-switch-label { font-size: 14px; color: #495057; font-weight: 500; cursor: pointer; user-select: none; margin-bottom: 0; }
.booking-switch-label small { display: block; font-size: 12px; color: #999; font-weight: 400; }
.booking-map-preview { width: 100%; height: 220px; border: 1px solid #e1e5eb; border-radius: 12px; overflow: hidden; background: #f7f8fa; }
.booking-map-preview iframe { width: 100%; height: 100%; border: 0; }
</style>

@php
    $locationEnabled = old('location_enabled', !empty($booking->location_enabled));
    $specGroups = $specifications ?? collect();
@endphp

<form data-wiz-form id="stepLocationForm">
    <h5 class="mb-1">Location &amp; Filters</h5>
    <p class="text-muted mb-4">Where this happens, and the attributes customers can filter by.</p>

    <div class="booking-switch-row mb-3">
        <label class="booking-switch" for="locationSwitch">
            <input type="checkbox" id="locationSwitch" name="location_enabled" value="1"
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
</form>

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
