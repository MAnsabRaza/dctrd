@if(!empty($booking->creator))
    <div class="product-seller-card position-relative mt-100">
        <div class="product-seller-card__mask"></div>

        <div class="position-relative d-flex flex-column flex-lg-row align-items-start gap-24 bg-white px-16 rounded-24 z-index-3">
            <div class="product-seller-card__details flex-1 py-16">
                <div class="d-flex align-items-center">
                    <div class="d-flex-center size-80 rounded-12 bg-gray-200">
                        <img src="{{ $booking->creator->getAvatar(80) }}"
                             alt="{{ $booking->creator->full_name }}"
                             class="img-cover rounded-12">
                    </div>

                    <div class="ml-12 flex-1">
                        <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank">
                            <h6 class="font-14 font-weight-bold text-dark">{{ $booking->creator->full_name }}</h6>
                        </a>

                        @php $providerRates = $booking->creator->rates(true); @endphp
                        @include('design_1.web.components.rate', [
                            'rate'          => $providerRates['rate'],
                            'rateCount'     => $providerRates['count'],
                            'rateClassName' => 'mt-4',
                        ])

                        <div class="d-flex align-items-center gap-12 mt-8">
                            <div class="d-flex align-items-center p-8 rounded-16 border-gray-200 bg-gray-100 text-gray-500 font-12">
                                <x-iconsax-lin-calendar-2 class="icons text-gray-400" width="16px" height="16px"/>
                                <span class="mx-4 font-weight-bold">{{ $booking->creator->bookings_count ?? 0 }}</span>
                                <span>{{ trans('update.bookings') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if(!empty($booking->creator->about))
                    <div class="product-seller-card__details-about my-16 text-gray-500">
                        {!! $booking->creator->about !!}
                    </div>
                @endif

                {{-- Provider's other bookings --}}
                @if(!empty($provider->someRandomBookings) and count($provider->someRandomBookings))
                    <div class="mt-auto">
                        <h5 class="font-14">{{ trans('update.more_from_user', ['user' => $booking->creator->full_name]) }}</h5>
                        <div class="d-grid grid-columns-auto grid-lg-columns-4 gap-16 gap-lg-32 mt-8">
                            @foreach($provider->someRandomBookings as $randomBooking)
                                <div class="d-flex align-items-center">
                                    <div class="size-48 rounded-8 bg-gray-200">
                                        <img src="{{ $randomBooking->thumbnail_url }}"
                                             alt="{{ $randomBooking->title }}"
                                             class="img-cover rounded-8">
                                    </div>
                                    <div class="ml-8">
                                        <a href="{{ $randomBooking->getUrl() }}">
                                            <h3 class="font-14 text-dark">{{ truncate($randomBooking->title, 33) }}</h3>
                                        </a>
                                        @if(!empty($randomBooking->category))
                                            <span class="mt-8 font-12 text-gray-500">{{ $randomBooking->category->title }}</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="product-seller-card__secondary-img position-relative mt-16 mt-0">
                <img src="{{ $booking->creator->getProfileSecondaryImage() }}"
                     alt="{{ $booking->creator->full_name }}"
                     class="img-cover">

                <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank"
                   class="product-seller-card__profile-btn d-flex-center">
                    <x-iconsax-lin-export-3 class="icons text-white" width="24px" height="24px"/>
                    <span class="ml-8 text-white">{{ trans('update.view_vendor_profile') }}</span>
                </a>
            </div>
        </div>
    </div>
@else
    <div class="bg-white p-16 rounded-24 text-gray-500">
        {{ trans('update.provider_info_not_available') }}
    </div>
@endif