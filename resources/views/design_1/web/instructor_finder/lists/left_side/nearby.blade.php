@once
    @push('styles_top')
        <style>
            .nearby-filter-suggestions { position: absolute; z-index: 1050; width: 100%; max-height: 220px; overflow-y: auto; }
        </style>
    @endpush
@endonce

<div class="accordion py-16 border-bottom-gray-100">
    <div class="accordion__title d-flex align-items-center justify-content-between">
        <div class="instructor-finder__filters-title font-14 font-weight-bold text-dark cursor-pointer" href="#sidebarFiltersNearby" data-parent="#sidebarFiltersAccordion" role="button" data-toggle="collapse">
            Find Nearby
        </div>

        <span class="collapse-arrow-icon d-flex cursor-pointer" href="#sidebarFiltersNearby" data-parent="#sidebarFiltersAccordion" role="button" data-toggle="collapse">
            <x-iconsax-lin-arrow-up-1 class="icons text-gray-400" width="16px" height="16px"/>
        </span>
    </div>

    <div id="sidebarFiltersNearby" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
        <div class="form-group mt-16">
            <label class="form-group-label" for="nearby_radius_km">Within</label>
            <div class="input-group">
                <input type="number" name="radius_km" id="nearby_radius_km" class="form-control" value="{{ request()->get('radius_km') }}" min="1" step="1">
                <div class="input-group-append">
                    <span class="input-group-text">km</span>
                </div>
            </div>
        </div>

        <div class="form-group mb-0 position-relative">
            <label class="form-group-label" for="nearby_from_place">From place</label>
            <input type="text" name="from_place" id="nearby_from_place" class="form-control" value="{{ request()->get('from_place') }}" autocomplete="off">
            <input type="hidden" name="lat" id="nearby_lat" value="{{ request()->get('lat') }}">
            <input type="hidden" name="lng" id="nearby_lng" value="{{ request()->get('lng') }}">
            <div id="nearby_place_suggestions" class="nearby-filter-suggestions dropdown-menu d-none show"></div>
        </div>

        <button type="button" id="clearNearbyFilter" class="btn btn-sm btn-outline-gray-500 btn-block mt-16">Clear Nearby</button>
    </div>
</div>
