<div class="position-relative products-lists-filters">
    <div class="products-lists-filters__mask"></div>

    <div id="bookingLeftFiltersAccordion" class="position-relative card-before-line card-before-line__4-12 bg-white py-16 rounded-24 z-index-2">
        <div class="font-14 font-weight-bold text-dark px-16">Booking Categories</div>

        @foreach($bookingCategories as $bookingCategory)
            @if(!empty($bookingCategory->children) and count($bookingCategory->children))
                <div class="accordion border-bottom-gray-100 mt-16 px-16 pb-16">
                    <div class="accordion__title d-flex align-items-center justify-content-between">
                        <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#bookingLeftFiltersCategory{{ $bookingCategory->id }}" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                            {{ $bookingCategory->title }}
                        </div>

                        <span class="collapse-arrow-icon d-flex cursor-pointer" href="#bookingLeftFiltersCategory{{ $bookingCategory->id }}" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                            <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                        </span>
                    </div>

                    <div id="bookingLeftFiltersCategory{{ $bookingCategory->id }}" class="accordion__collapse pt-0 mt-0 border-0 {{ $loop->first ? 'show' : '' }}" role="tabpanel">
                        <div class="pl-8">
                            @foreach($bookingCategory->children as $subCategory)
                                <div class="js-get-view-data-by-tab mt-16 cursor-pointer {{ (request()->get('category_id') == $subCategory->id) ? 'active' : '' }}" data-filter-name="category_id" data-filter-value="{{ $subCategory->id }}" data-container-id="listsContainer">{{ $subCategory->title }}</div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="js-get-view-data-by-tab mt-16 px-16 cursor-pointer font-weight-bold {{ (request()->get('category_id') == $bookingCategory->id) ? 'active' : '' }}" data-filter-name="category_id" data-filter-value="{{ $bookingCategory->id }}" data-container-id="listsContainer">{{ $bookingCategory->title }}</div>
            @endif
        @endforeach
    </div>
</div>

<div class="position-relative products-lists-filters mt-28">
    <div class="products-lists-filters__mask"></div>

    <div class="position-relative bg-white py-16 rounded-24 z-index-2">
        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#bookingTypesFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    Booking Type
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#bookingTypesFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="bookingTypesFilters" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                @foreach(['tour', 'activity', 'rental', 'event', 'service', 'accommodation'] as $typeOption)
                    <div class="custom-control custom-checkbox {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                        <input type="checkbox" name="booking_type[]" value="{{ $typeOption }}" id="filter_booking_type_{{ $typeOption }}" class="custom-control-input">
                        <label class="custom-control__label cursor-pointer" for="filter_booking_type_{{ $typeOption }}">{{ ucfirst($typeOption) }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="accordion card-before-line card-before-line__4-12 pb-16 px-16 mt-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#bookingMoreOptionsFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('site.more_options') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#bookingMoreOptionsFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="bookingMoreOptionsFilters" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                @foreach(['featured' => 'Featured', 'instant_booking' => 'Instant booking', 'location_enabled' => 'Has location'] as $moreOption => $label)
                    <div class="custom-control custom-checkbox {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                        <input type="checkbox" name="options[]" value="{{ $moreOption }}" id="filter_booking_option_{{ $moreOption }}" class="custom-control-input">
                        <label class="custom-control__label cursor-pointer" for="filter_booking_option_{{ $moreOption }}">{{ $label }}</label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="accordion card-before-line card-before-line__4-12 p-16 border-bottom-gray-100">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#bookingPriceFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('public.price') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#bookingPriceFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="bookingPriceFilters" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                <div class="d-flex align-items-center mt-16">
                    <div class="form-group mb-0">
                        <input type="tel" readonly value="{{ trans('update.free') }}" class="js-filters-min-price form-control input-xs bg-white text-center text-gray-500">
                    </div>
                    <div class="mx-4"></div>
                    <div class="form-group mb-0">
                        <input type="tel" readonly value="{{ handlePrice($filterMaxPrice) }}" class="js-filters-max-price form-control input-xs bg-white text-center text-gray-500">
                    </div>
                </div>

                <div class="course-list-price-range range wrunner-value-bottom no-bottom-value-note mt-8" id="priceRange" data-minLimit="0" data-maxLimit="{{ $filterMaxPrice }}" data-step="{{ ($filterMaxPrice < 100) ? 2 : (($filterMaxPrice < 500) ? 50 : 100) }}">
                    <input type="hidden" name="min_price" value="" class="js-range-input-view-data">
                    <input type="hidden" name="max_price" value="" class="js-range-input-view-data">
                </div>
            </div>
        </div>

        <div class="accordion card-before-line card-before-line__4-12 p-16">
            <div class="accordion__title d-flex align-items-center justify-content-between">
                <div class="font-14 font-weight-bold text-dark cursor-pointer" href="#bookingProviderFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    {{ trans('update.provider') }}
                </div>

                <span class="collapse-arrow-icon d-flex cursor-pointer" href="#bookingProviderFilters" data-parent="#bookingLeftFiltersAccordion" role="button" data-toggle="collapse">
                    <x-iconsax-lin-arrow-up-1 class="icons text-gray-500" width="16"/>
                </span>
            </div>

            <div id="bookingProviderFilters" class="accordion__collapse show pt-0 mt-0 border-0" role="tabpanel">
                <div class="form-group mb-0 mt-24">
                    <label class="form-group-label">{{ trans('update.provider') }}</label>
                    <select name="provider" class="form-control searchable-select bg-white" data-allow-clear="true" data-placeholder="{{ trans('update.search_and_select_instructor') }}" data-api-path="/users/search" data-item-column-name="full_name" data-option="just_teachers"></select>
                </div>
            </div>
        </div>
    </div>
</div>
