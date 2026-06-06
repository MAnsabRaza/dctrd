@php
    $locationModel = $locationModel ?? null;
    $locationPrefix = $locationPrefix ?? '';
    $addressName = $addressName ?? 'address_line';
    $addressValue = old($addressName, $locationModel->address_line ?? $locationModel->address ?? '');
    $latValue = old($locationPrefix . 'lat', $locationModel->lat ?? '');
    $lngValue = old($locationPrefix . 'lng', $locationModel->lng ?? '');
    $pickerId = $pickerId ?? 'locationPicker_' . uniqid();
    $showAjaxSave = $showAjaxSave ?? false;
@endphp

@once
    @push('styles_top')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
        <style>
            .location-picker-suggestions { position: absolute; z-index: 1050; width: 100%; max-height: 220px; overflow-y: auto; }
            .location-picker-suggestion { cursor: pointer; }
            .location-picker-map { height: 400px; min-height: 260px; }
        </style>
    @endpush

    @push('scripts_bottom')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            (function () {
                window.RocketLocationPicker = window.RocketLocationPicker || {
                    instances: [],
                    debounce: function (callback, wait) {
                        var timeout;
                        return function () {
                            var args = arguments;
                            clearTimeout(timeout);
                            timeout = setTimeout(function () { callback.apply(null, args); }, wait);
                        };
                    },
                    init: function (root) {
                        if (!root || root.dataset.ready === '1' || typeof L === 'undefined') {
                            return;
                        }

                        root.dataset.ready = '1';

                        var mapEl = root.querySelector('[data-location-map]');
                        var addressInput = root.querySelector('[data-location-address]');
                        var cityInput = root.querySelector('[data-location-city]');
                        var stateInput = root.querySelector('[data-location-state]');
                        var countryInput = root.querySelector('[data-location-country]');
                        var postalInput = root.querySelector('[data-location-postal]');
                        var latInput = root.querySelector('[data-location-lat]');
                        var lngInput = root.querySelector('[data-location-lng]');
                        var suggestionsEl = root.querySelector('[data-location-suggestions]');
                        var saveButton = root.querySelector('[data-location-save]');
                        var lat = parseFloat(latInput.value) || 33.6844;
                        var lng = parseFloat(lngInput.value) || 73.0479;
                        var map = L.map(mapEl).setView([lat, lng], latInput.value && lngInput.value ? 13 : 5);
                        var marker = L.marker([lat, lng], {draggable: true}).addTo(map);

                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                            maxZoom: 19,
                            attribution: '&copy; OpenStreetMap'
                        }).addTo(map);

                        setTimeout(function () { map.invalidateSize(); }, 300);

                        function fillFields(item) {
                            if (!item) {
                                return;
                            }

                            if (addressInput) addressInput.value = item.display_name || addressInput.value;
                            if (cityInput) cityInput.value = item.city || cityInput.value;
                            if (stateInput) stateInput.value = item.state || stateInput.value;
                            if (countryInput) countryInput.value = item.country || countryInput.value;
                            if (postalInput) postalInput.value = item.postal_code || postalInput.value;
                            if (latInput) latInput.value = item.lat || '';
                            if (lngInput) lngInput.value = item.lng || '';

                            if (item.lat && item.lng) {
                                marker.setLatLng([item.lat, item.lng]);
                                map.setView([item.lat, item.lng], 14);
                            }
                        }

                        function renderSuggestions(items) {
                            suggestionsEl.innerHTML = '';

                            items.forEach(function (item) {
                                var option = document.createElement('button');
                                option.type = 'button';
                                option.className = 'location-picker-suggestion dropdown-item text-wrap py-8';
                                option.textContent = item.display_name;
                                option.addEventListener('click', function () {
                                    fillFields(item);
                                    suggestionsEl.classList.add('d-none');
                                });
                                suggestionsEl.appendChild(option);
                            });

                            suggestionsEl.classList.toggle('d-none', !items.length);
                        }

                        if (addressInput) {
                            var suggestAddress = RocketLocationPicker.debounce(function () {
                                var q = addressInput.value.trim();

                                if (q.length < 3) {
                                    renderSuggestions([]);
                                    return;
                                }

                                fetch('/location/suggestions?q=' + encodeURIComponent(q))
                                    .then(function (response) { return response.json(); })
                                    .then(renderSuggestions)
                                    .catch(function () { renderSuggestions([]); });
                            }, 400);

                            addressInput.addEventListener('keyup', suggestAddress);
                            addressInput.addEventListener('input', suggestAddress);
                        }

                        marker.on('dragend', function () {
                            var point = marker.getLatLng();
                            latInput.value = point.lat.toFixed(8);
                            lngInput.value = point.lng.toFixed(8);

                            fetch('https://nominatim.openstreetmap.org/reverse?format=json&addressdetails=1&lat=' + point.lat + '&lon=' + point.lng)
                                .then(function (response) { return response.json(); })
                                .then(function (item) {
                                    var address = item.address || {};
                                    fillFields({
                                        display_name: item.display_name,
                                        lat: point.lat.toFixed(8),
                                        lng: point.lng.toFixed(8),
                                        city: address.city || address.town || address.village,
                                        state: address.state,
                                        country: address.country,
                                        postal_code: address.postcode
                                    });
                                })
                                .catch(function () {});
                        });

                        if (!cityInput.value) {
                            fetch('/location/detect')
                                .then(function (response) { return response.json(); })
                                .then(function (item) {
                                    if (item.city && !cityInput.value) cityInput.value = item.city;
                                    if (item.country && !countryInput.value) countryInput.value = item.country;
                                })
                                .catch(function () {});
                        }

                        if (saveButton) {
                            saveButton.addEventListener('click', function () {
                                var payload = new FormData();
                                payload.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
                                payload.append('address_line', addressInput.value || '');
                                payload.append('city', cityInput.value || '');
                                payload.append('state', stateInput.value || '');
                                payload.append('country', countryInput.value || '');
                                payload.append('postal_code', postalInput.value || '');
                                payload.append('lat', latInput.value || '');
                                payload.append('lng', lngInput.value || '');

                                fetch('/location/save', {method: 'POST', body: payload, credentials: 'same-origin'})
                                    .then(function () { saveButton.classList.add('btn-success'); });
                            });
                        }
                    }
                };

                function initLocationPickers() {
                    document.querySelectorAll('[data-location-picker]').forEach(RocketLocationPicker.init);
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', initLocationPickers);
                } else {
                    initLocationPickers();
                }
            })();
        </script>
    @endpush
@endonce

<div id="{{ $pickerId }}" data-location-picker>
    <div class="row">
        <div class="col-12 position-relative">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.address') }}</label>
                <input type="text" name="{{ $addressName }}" value="{{ $addressValue }}" class="form-control" autocomplete="off" data-location-address>
                <div class="location-picker-suggestions dropdown-menu d-none show" data-location-suggestions></div>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.city') }}</label>
                <input type="text" name="{{ $locationPrefix }}city" value="{{ old($locationPrefix . 'city', $locationModel->city ?? '') }}" class="form-control" data-location-city>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.state') }}</label>
                <input type="text" name="{{ $locationPrefix }}state" value="{{ old($locationPrefix . 'state', $locationModel->state ?? '') }}" class="form-control" data-location-state>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.country') }}</label>
                <input type="text" name="{{ $locationPrefix }}country" value="{{ old($locationPrefix . 'country', $locationModel->country ?? '') }}" class="form-control" data-location-country>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.postal_code') }}</label>
                <input type="text" name="{{ $locationPrefix }}postal_code" value="{{ old($locationPrefix . 'postal_code', $locationModel->postal_code ?? '') }}" class="form-control" data-location-postal>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.latitude') }}</label>
                <input type="number" step="any" name="{{ $locationPrefix }}lat" value="{{ $latValue }}" class="form-control" readonly data-location-lat>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.longitude') }}</label>
                <input type="number" step="any" name="{{ $locationPrefix }}lng" value="{{ $lngValue }}" class="form-control" readonly data-location-lng>
            </div>
        </div>
    </div>

    <div class="location-picker-map rounded-8 border-gray-200" data-location-map></div>

    @if($showAjaxSave)
        <button type="button" class="btn btn-primary btn-sm mt-16" data-location-save>{{ trans('public.save') }}</button>
    @endif
</div>
