<div class="px-16">
    <div class="border-dashed border-gray-300 rounded-16 p-12 mt-16">

        @if(!empty($deliveryEstimateTime))
            <div class="d-flex align-items-center">
                <div class="d-flex-center size-48 rounded-12 bg-primary-20">
                    <x-iconsax-bul-truck-time class="icons text-primary" width="24px" height="24px"/>
                </div>
                <div class="ml-8">
                    <h5 class="font-14">{{ trans('update.shipping_and_delivery') }}</h5>
                    <p class="mt-4 font-12 text-gray-500">{{ trans('update.cart_order_estimated_delivery_time_hint', ['days' => $deliveryEstimateTime]) }}</p>
                </div>
            </div>
        @endif

        @if(!empty(getStoreSettings('show_address_selection_in_cart')))
            <div id="regionCard" class="js-instructor-location mt-28">

                <div class="d-flex align-items-center flex-wrap gap-16">
                    <div class="flex-1">
                        <div class="form-group ">
                            <label class="form-group-label">{{ trans('update.country') }}</label>

                            <select name="country_id" class="js-ajax-country_id js-country-selection form-control select2" data-regions-parent="js-instructor-location" data-map-zoom="5">
                                <option value="">{{ trans('update.choose_a_country') }}</option>

                                @if(!empty($countries))
                                    @foreach($countries as $country)
                                        <option value="{{ $country->id }}" {{ (!empty($user) and $user->country_id == $country->id) ? 'selected' : '' }}>{{ $country->title }}</option>
                                    @endforeach
                                @endif
                            </select>

                            <div class="invalid-feedback d-block">@error('country_id') {{ $message }} @enderror</div>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="form-group ">
                            <label class="form-group-label">{{ trans('update.state') }}</label>

                            <select
                                name="province_id"
                                class="js-ajax-province_id js-state-selection form-control select2"
                                data-regions-parent="js-instructor-location"
                                data-map-zoom="8"
                                {{ (!empty($user) and $user->country_id) ? '' : 'disabled' }}
                            >
                                <option value="">{{ trans('update.choose_a_state') }}</option>

                                @if(!empty($provinces))
                                    @foreach($provinces as $province)
                                        <option value="{{ $province->id }}" {{ (!empty($user) and $user->province_id == $province->id) ? 'selected' : '' }}>{{ $province->title }}</option>
                                    @endforeach
                                @endif
                            </select>

                            <div class="invalid-feedback d-block">@error('province_id') {{ $message }} @enderror</div>
                        </div>
                    </div>
                </div>

                <div class="d-flex align-items-center flex-wrap gap-16">
                    <div class="flex-1">
                        <div class="form-group ">
                            <label class="form-group-label">{{ trans('update.city') }}</label>

                            <select name="city_id"
                                    class="js-ajax-city_id js-city-selection form-control select2"
                                    data-regions-parent="js-instructor-location"
                                    data-map-zoom="12"
                                {{ (!empty($user) and $user->province_id) ? '' : 'disabled' }}
                            >
                                <option value="">{{ trans('update.choose_a_city') }}</option>

                                @if(!empty($cities))
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" {{ (!empty($user) and $user->city_id == $city->id) ? 'selected' : '' }}>{{ $city->title }}</option>
                                    @endforeach
                                @endif
                            </select>

                            <div class="invalid-feedback d-block">@error('city_id') {{ $message }} @enderror</div>
                        </div>
                    </div>

                    <div class="flex-1">
                        <div class="form-group ">
                            <label class="form-group-label">{{ trans('update.district') }}</label>

                            <select name="district_id"
                                    class="js-ajax-district_id js-district-selection form-control select2"
                                    data-regions-parent="js-instructor-location"
                                    data-map-zoom="15"
                                {{ (!empty($user) and $user->city_id) ? '' : 'disabled' }}
                            >
                                <option value="">{{ trans('update.choose_a_district') }}</option>

                                @if(!empty($districts))
                                    @foreach($districts as $district)
                                        <option value="{{ $district->id }}" {{ (!empty($user) and $user->district_id == $district->id) ? 'selected' : '' }}>{{ $district->title }}</option>
                                    @endforeach
                                @endif
                            </select>

                            <div class="invalid-feedback d-block">@error('district_id') {{ $message }} @enderror</div>
                        </div>
                    </div>

                </div>
            </div>
        @endif

        {{-- ===== DELIVERY ADDRESS SECTION ===== --}}
        <div class="mt-28 border-top pt-20">
            <h5 class="font-14 mb-20 font-weight-600">{{ trans('update.delivery_address') ?? 'Delivery Address' }}</h5>

            {{-- Use Profile Address Button --}}
            <div class="form-group mb-24">
                <button type="button" class="btn btn-sm btn-outline-primary" id="useProfileAddressBtn">
                    <i class="fas fa-user-circle mr-2"></i>
                    {{ trans('update.use_my_profile_address') ?? 'Use My Profile Address' }}
                </button>
            </div>

            {{-- Address Line with Autocomplete --}}
            <div class="form-group">
                <label class="form-group-label">{{ trans('update.address_line') ?? 'Address Line' }}</label>
                <input type="text" name="address_line" id="checkoutAddressLine" placeholder="e.g., 123 Main Street" class="form-control form-control-lg autocomplete-input" value="{{ old('address_line') }}" autocomplete="off">
                <small class="text-gray-500 d-block mt-4">Start typing for address suggestions</small>
                <div id="addressSuggestions" class="list-group position-absolute bg-white rounded-12 mt-2 w-100 shadow-sm" style="display:none; z-index: 1000;"></div>
                <div class="invalid-feedback d-block">@error('address_line') {{ $message }} @enderror</div>
            </div>

            {{-- City, State, Country, Postal Code Row --}}
            <div class="row mt-20">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-group-label">{{ trans('update.city') ?? 'City' }}</label>
                        <input type="text" name="city" id="checkoutCity" class="form-control" value="{{ old('city') }}">
                        <div class="invalid-feedback d-block">@error('city') {{ $message }} @enderror</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-group-label">{{ trans('update.state') ?? 'State/Province' }}</label>
                        <input type="text" name="state" id="checkoutState" class="form-control" value="{{ old('state') }}">
                        <div class="invalid-feedback d-block">@error('state') {{ $message }} @enderror</div>
                    </div>
                </div>
            </div>

            {{-- Country and Postal Code Row --}}
            <div class="row mt-20">
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-group-label">{{ trans('update.country') ?? 'Country' }}</label>
                        <input type="text" name="country" id="checkoutCountry" class="form-control" value="{{ old('country') }}">
                        <div class="invalid-feedback d-block">@error('country') {{ $message }} @enderror</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label class="form-group-label">{{ trans('update.postal_code') ?? 'Postal Code' }}</label>
                        <input type="text" name="postal_code" id="checkoutPostalCode" class="form-control" value="{{ old('postal_code') }}">
                        <div class="invalid-feedback d-block">@error('postal_code') {{ $message }} @enderror</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group {{ (!empty(getStoreSettings('show_address_selection_in_cart'))) ? '' : 'mt-28' }} mt-20">
            <label class="form-group-label">{{ trans('update.address') }}</label>
            <textarea name="address" rows="6" class="js-ajax-address form-control">{{ !empty($user) ? $user->address : '' }}</textarea>
            <div class="invalid-feedback d-block">@error('address') {{ $message }} @enderror</div>
        </div>

        <div class="form-group">
            <label class="form-group-label">{{ trans('update.message_to_seller') }}</label>
            <textarea name="message_to_seller" rows="6" class="js-ajax-message_to_seller form-control"></textarea>
            <div class="invalid-feedback d-block">@error('message_to_seller') {{ $message }} @enderror</div>
        </div>


    </div>
</div>
