<!-- <div class="position-relative products-lists-filters">
    <div class="products-lists-filters__mask"></div>

    <div id="bookingLeftFiltersAccordion" class="position-relative card-before-line card-before-line__4-12 bg-white py-16 rounded-24 z-index-2">
        <div class="font-14 font-weight-bold text-dark px-16">{{ trans('update.booking_categories') }}</div>

        @foreach($bookingCategories as $bookingCategory)
            @if(!empty($bookingCategory->children) and count($bookingCategory->children))
                <div class="accordion border-bottom-gray-100 mt-16 px-16 pb-16">
                    <div class="accordion__title d-flex align-items-center justify-content-between">
                        <div class="font-14 font-weight-bold text-dark cursor-pointer"
                             href="#bookingLeftFiltersCategory{{ $bookingCategory->id }}"
                             data-parent="#bookingLeftFiltersAccordion"
                             role="button" data-toggle="collapse">
                            {{ $bookingCategory->title }}
                        </div>
                        <span class="collapse-arrow-icon d-flex cursor-pointer"
                              href="#bookingLeftFiltersCategory{{ $bookingCategory->id }}"
                              data-parent="#bookingLeftFiltersAccordion"
                              role="button" data-toggle="collapse">
                            <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                        </span>
                    </div>
                    <div id="bookingLeftFiltersCategory{{ $bookingCategory->id }}"
                         class="accordion__collapse pt-0 mt-0 border-0 {{ $loop->first ? 'show' : '' }}"
                         role="tabpanel">
                        <div class="pl-8">
                            @foreach($bookingCategory->children as $subCategory)
                                <div class="js-get-view-data-by-tab mt-16 cursor-pointer {{ (request()->get('category_id') == $subCategory->id) ? 'active' : '' }}"
                                     data-filter-name="category_id"
                                     data-filter-value="{{ $subCategory->id }}"
                                     data-container-id="bookingsListsContainer">
                                    {{ $subCategory->title }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="js-get-view-data-by-tab mt-16 px-16 cursor-pointer font-weight-bold {{ (request()->get('category_id') == $bookingCategory->id) ? 'active' : '' }}"
                     data-filter-name="category_id"
                     data-filter-value="{{ $bookingCategory->id }}"
                     data-container-id="bookingsListsContainer">
                    {{ $bookingCategory->title }}
                </div>
            @endif
        @endforeach
    </div>
</div> 

<div class="position-relative products-lists-filters mt-28"> -->
    <div class="products-lists-filters__mask"></div>

    <div class="position-relative bg-white py-16 rounded-24 z-index-2">

        {{-- Booking Type — built from the real admin categories (parent, then its children) --}}
        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer"
                     href="#bookingTypesFilter" data-parent="#bookingLeftFiltersAccordion"
                     role="button" data-toggle="collapse">
                    {{ trans('update.booking_type') }}
                </div>
                <span class="collapse-arrow-icon d-flex cursor-pointer"
                      href="#bookingTypesFilter" data-parent="#bookingLeftFiltersAccordion"
                      role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>
            <div id="bookingTypesFilter" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                @forelse($bookingCategories as $typeCategory)
                    {{-- Parent --}}
                    <div class="custom-control custom-checkbox {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                        <input type="checkbox" name="booking_type[]" value="{{ $typeCategory->id }}"
                               id="filter_booking_type_{{ $typeCategory->id }}" class="custom-control-input js-booking-type-parent"
                               data-children="{{ !empty($typeCategory->children) ? $typeCategory->children->pluck('id')->implode(',') : '' }}">
                        <label class="custom-control__label cursor-pointer font-weight-bold" for="filter_booking_type_{{ $typeCategory->id }}">
                            {{ $typeCategory->title }}
                        </label>
                    </div>

                    {{-- Children --}}
                    @if(!empty($typeCategory->children) && count($typeCategory->children))
                        <div class="pl-24">
                            @foreach($typeCategory->children as $typeChild)
                                <div class="custom-control custom-checkbox mt-8">
                                    <input type="checkbox" name="booking_type[]" value="{{ $typeChild->id }}"
                                           id="filter_booking_type_{{ $typeChild->id }}" class="custom-control-input js-booking-type-child"
                                           data-parent="{{ $typeCategory->id }}">
                                    <label class="custom-control__label cursor-pointer" for="filter_booking_type_{{ $typeChild->id }}">
                                        {{ $typeChild->title }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @empty
                    <div class="font-12 text-gray-500 mt-16">{{ trans('update.no_categories_found') }}</div>
                @endforelse
            </div>
        </div>

        {{-- More Options --}}
        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 mt-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer"
                     href="#bookingMoreOptionsFilter" data-parent="#bookingLeftFiltersAccordion"
                     role="button" data-toggle="collapse">
                    {{ trans('site.more_options') }}
                </div>
                <span class="collapse-arrow-icon d-flex cursor-pointer"
                      href="#bookingMoreOptionsFilter" data-parent="#bookingLeftFiltersAccordion"
                      role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>
            <div id="bookingMoreOptionsFilter" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                @foreach(['featured' => trans('update.featured'), 'instant_booking' => trans('update.instant_booking'), 'location_enabled' => trans('update.has_location')] as $moreOption => $label)
                    <div class="custom-control custom-checkbox {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                        <input type="checkbox" name="options[]" value="{{ $moreOption }}"
                               id="filter_booking_option_{{ $moreOption }}" class="custom-control-input">
                        <label class="custom-control__label cursor-pointer" for="filter_booking_option_{{ $moreOption }}">
                            {{ $label }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Price --}}
        <div class="accordion card-before-line card-before-line__4-12 p-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer"
                     href="#bookingPriceFilter" data-parent="#bookingLeftFiltersAccordion"
                     role="button" data-toggle="collapse">
                    {{ trans('public.price') }}
                </div>
                <span class="collapse-arrow-icon d-flex cursor-pointer"
                      href="#bookingPriceFilter" data-parent="#bookingLeftFiltersAccordion"
                      role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>
            <div id="bookingPriceFilter" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                <div class="d-flex align-items-center mt-16">
                    <div class="form-group mb-0">
                        <input type="tel" readonly value="{{ trans('update.free') }}"
                               class="js-filters-min-price form-control input-xs bg-white text-center text-gray-500">
                    </div>
                    <div class="mx-4"></div>
                    <div class="form-group mb-0">
                        <input type="tel" readonly value="{{ handlePrice($filterMaxPrice) }}"
                               class="js-filters-max-price form-control input-xs bg-white text-center text-gray-500">
                    </div>
                </div>
                <div class="course-list-price-range range wrunner-value-bottom no-bottom-value-note mt-8"
                     id="priceRange"
                     data-minLimit="0"
                     data-maxLimit="{{ $filterMaxPrice }}"
                     data-step="{{ ($filterMaxPrice < 100) ? 2 : (($filterMaxPrice < 500) ? 50 : 100) }}">
                    <input type="hidden" name="min_price" value="" class="js-range-input-view-data">
                    <input type="hidden" name="max_price" value="" class="js-range-input-view-data">
                </div>
            </div>
        </div>

        {{-- Location / Nearby --}}
        <div class="accordion card-before-line card-before-line__4-12 p-16 mt-16">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer"
                     href="#bookingLocationFilter" data-parent="#bookingLeftFiltersAccordion"
                     role="button" data-toggle="collapse">
                    {{ trans('update.location') }}
                </div>
                <span class="collapse-arrow-icon d-flex cursor-pointer"
                      href="#bookingLocationFilter" data-parent="#bookingLeftFiltersAccordion"
                      role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="bookingLocationFilter" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">

                {{-- "Use my location" button --}}
                <button type="button" id="btn-use-my-location"
                        class="btn btn-sm btn-outline-primary w-100 mt-16">
                    <i class="fa fa-crosshairs mr-1"></i>
                    {{ trans('update.use_my_location') }}
                </button>

                {{-- Address input (optional manual entry) --}}
                <div class="form-group mt-12 mb-0">
                    <input type="text"
                           id="location-address-input"
                           placeholder="{{ trans('update.enter_location') }}"
                           class="form-control bg-white">
                </div>

                {{-- Radius select --}}
                <div class="form-group mt-12 mb-0">
                    <select name="radius" class="form-control bg-white js-range-input-view-data">
                        @foreach([5, 10, 25, 50, 100] as $km)
                            <option value="{{ $km }}" {{ request('radius', 25) == $km ? 'selected' : '' }}>
                                {{ $km }} km
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Hidden lat/lng sent with filter --}}
                <input type="hidden" name="lat" id="filter-lat"
                       value="{{ request('lat') }}" class="js-range-input-view-data">
                <input type="hidden" name="lng" id="filter-lng"
                       value="{{ request('lng') }}" class="js-range-input-view-data">

            </div>
        </div>


        {{-- Provider --}}
        <div class="accordion card-before-line card-before-line__4-12 p-16">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer"
                     href="#bookingProviderFilter" data-parent="#bookingLeftFiltersAccordion"
                     role="button" data-toggle="collapse">
                    {{ trans('update.provider') }}
                </div>
                <span class="collapse-arrow-icon d-flex cursor-pointer"
                      href="#bookingProviderFilter" data-parent="#bookingLeftFiltersAccordion"
                      role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>
            <div id="bookingProviderFilter" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                <div class="form-group mb-0 mt-24">
                    <label class="form-group-label">{{ trans('update.provider') }}</label>
                    <select name="provider" class="form-control searchable-select bg-white"
                            data-allow-clear="true"
                            data-placeholder="{{ trans('update.search_and_select_instructor') }}"
                            data-api-path="/users/search"
                            data-item-column-name="full_name"
                            data-option="just_teachers">
                    </select>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts_bottom')
<script>
    // Checking a parent Booking Type also checks/unchecks all of its children,
    // so the applied filter matches what the admin database actually holds.
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.js-booking-type-parent').forEach(function (parentCheckbox) {
            parentCheckbox.addEventListener('change', function () {
                var childIds = (this.dataset.children || '').split(',').filter(Boolean);
                childIds.forEach(function (childId) {
                    var childEl = document.getElementById('filter_booking_type_' + childId);
                    if (childEl) {
                        childEl.checked = parentCheckbox.checked;
                    }
                });
            });
        });

        document.querySelectorAll('.js-booking-type-child').forEach(function (childCheckbox) {
            childCheckbox.addEventListener('change', function () {
                var parentId = this.dataset.parent;
                var parentEl = document.getElementById('filter_booking_type_' + parentId);
                if (!parentEl) return;

                var siblings = document.querySelectorAll('.js-booking-type-child[data-parent="' + parentId + '"]');
                var allChecked = Array.from(siblings).every(function (el) { return el.checked; });
                var noneChecked = Array.from(siblings).every(function (el) { return !el.checked; });

                parentEl.checked = allChecked;
                parentEl.indeterminate = !allChecked && !noneChecked;
            });
        });
    });
</script>

<script>
(function ($) {
    'use strict';

    /* ════════════════════════════════════════
       Translation strings (Blade se seedha)
    ════════════════════════════════════════ */
    var LANG = {
        geolocationNotSupported: '{{ trans("update.geolocation_not_supported") ?? "Geolocation not supported by your browser" }}',
        detectingLocation: '{{ trans("update.detecting_location") ?? "Detecting..." }}',
        couldNotGetLocation: '{{ trans("update.could_not_get_location") ?? "Could not get your location" }}'
    };

    /* ════════════════════════════════════════
       Booking list ko AJAX se reload karo,
       current filters + naye lat/lng/radius ke sath
    ════════════════════════════════════════ */
    function reloadBookingsList(extraParams) {
        var $container = $('#bookingsListsContainer');
        if (!$container.length) return;

        var params = new URLSearchParams(window.location.search);

        Object.keys(extraParams || {}).forEach(function (key) {
            var val = extraParams[key];
            if (val === null || val === '' || typeof val === 'undefined') {
                params.delete(key);
            } else {
                params.set(key, val);
            }
        });

        $container.css('opacity', '0.5');

        $.ajax({
            url: window.location.pathname + '?' + params.toString(),
            method: 'GET',
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).done(function (res) {
            if (res && res.data) {
                $container.html(res.data);
            }
            window.history.replaceState({}, '', window.location.pathname + '?' + params.toString());
        }).always(function () {
            $container.css('opacity', '1');
        });
    }

    /* ════════════════════════════════════════
       "Use my location" — browser geolocation
    ════════════════════════════════════════ */
    $(document).on('click', '#btn-use-my-location', function () {
        var $btn = $(this);

        if (!navigator.geolocation) {
            if (typeof showToast === 'function') {
                showToast('error', '', LANG.geolocationNotSupported);
            } else {
                alert(LANG.geolocationNotSupported);
            }
            return;
        }

        var originalText = $btn.text();
        $btn.prop('disabled', true).text(LANG.detectingLocation);

        navigator.geolocation.getCurrentPosition(
            function (position) {
                var lat = position.coords.latitude;
                var lng = position.coords.longitude;

                $('#filter-lat').val(lat);
                $('#filter-lng').val(lng);
                $btn.prop('disabled', false).text(originalText);

                reloadBookingsList({
                    lat: lat,
                    lng: lng,
                    radius: $('select[name="radius"]').val() || 25
                });
            },
            function () {
                $btn.prop('disabled', false).text(originalText);
                if (typeof showToast === 'function') {
                    showToast('error', '', LANG.couldNotGetLocation);
                } else {
                    alert(LANG.couldNotGetLocation);
                }
            }
        );
    });

    /* ════════════════════════════════════════
       Address/City input — geocode to lat/lng
       (Nominatim/OpenStreetMap — free, no API key)
    ════════════════════════════════════════ */
    var geocodeTimer = null;

    $(document).on('input', '#location-address-input', function () {
        var address = $(this).val().trim();
        clearTimeout(geocodeTimer);

        if (address.length < 3) return;

        geocodeTimer = setTimeout(function () {
            $.ajax({
                url: 'https://nominatim.openstreetmap.org/search',
                method: 'GET',
                data: { q: address, format: 'json', limit: 1 }
            }).done(function (results) {
                if (results && results.length) {
                    var lat = parseFloat(results[0].lat);
                    var lng = parseFloat(results[0].lon);

                    $('#filter-lat').val(lat);
                    $('#filter-lng').val(lng);

                    reloadBookingsList({
                        lat: lat,
                        lng: lng,
                        radius: $('select[name="radius"]').val() || 25
                    });
                }
            });
        }, 600); // debounce — typing rukne ke 600ms baad hi search kare
    });

    /* ════════════════════════════════════════
       Radius change — agar lat/lng already set hain
       to turant naye radius se dobara filter karo
    ════════════════════════════════════════ */
    $(document).on('change', 'select[name="radius"]', function () {
        var lat = $('#filter-lat').val();
        var lng = $('#filter-lng').val();

        if (lat && lng) {
            reloadBookingsList({ lat: lat, lng: lng, radius: $(this).val() });
        }
    });

})(jQuery);
</script>
@endpush