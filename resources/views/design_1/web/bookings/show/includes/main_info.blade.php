<div class="card-with-mask">
    <div class="mask-8-white"></div>

    <div class="position-relative bg-white p-16 rounded-24 z-index-2">

        {{-- Special Offer --}}
        @include('design_1.web.bookings.show.includes.special_offer')

        {{-- Favourite button (top-right) --}}
        <div class="position-absolute" style="top:24px; right:24px; z-index:10;">
            <div class="bookingFavoriteBtn d-flex align-items-center justify-content-center rounded-circle bg-white border border-gray-200"
                 style="width:42px; height:42px; cursor:pointer;"
                 data-slug="{{ $booking->slug }}"
                 @if(auth()->guest()) data-login-url="/login" @endif>
                <x-iconsax-lin-heart class="icons js-empty-fav text-gray-500 {{ !empty($isFavorited) ? 'd-none' : '' }}" width="22px" height="22px"/>
                <x-iconsax-bol-heart class="icons js-full-fav text-danger  {{ !empty($isFavorited) ? ''       : 'd-none' }}" width="22px" height="22px"/>
            </div>
        </div>

        {{-- Breadcrumb --}}
        <div class="breadcrumb d-flex align-items-center">
            <a href="/" class="breadcrumb-item font-14 text-gray-500">{{ getPlatformName() }}</a>
            <x-iconsax-lin-arrow-right-1 class="icons text-gray-500 mx-8" width="14px" height="14px"/>
            <a href="/bookings" class="breadcrumb-item font-14 text-gray-500">{{ trans('update.bookings') }}</a>
            @if(!empty($booking->category))
                <x-iconsax-lin-arrow-right-1 class="icons text-gray-500 mx-8" width="14px" height="14px"/>
                <span class="breadcrumb-item font-14 text-gray-500">{{ $booking->category->title }}</span>
            @endif
        </div>

        {{-- Title + Badges --}}
        <div class="d-flex align-items-center flex-wrap gap-12 mt-12">
            <h1 class="course-hero__title font-24 font-weight-bold text-dark text-ellipsis">
                {{ $booking->title }}
            </h1>

            @if(!empty($booking->featured))
                <div class="d-flex-center gap-4 p-4 pr-8 rounded-32 bg-warning text-white">
                    <x-iconsax-bol-star-1 class="icons text-white" width="18px" height="18px"/>
                    <span class="font-12">{{ trans('update.featured') }}</span>
                </div>
            @endif

            @if(!empty($booking->instant_booking))
                <div class="d-flex-center gap-4 p-4 pr-8 rounded-32 bg-success text-white">
                    <x-iconsax-bol-flash class="icons text-white" width="18px" height="18px"/>
                    <span class="font-12">{{ trans('update.instant_booking') }}</span>
                </div>
            @endif
        </div>

        {{-- Meta row --}}
        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between mt-12">
            <div class="d-flex align-items-center flex-wrap gap-24">

                @include('design_1.web.components.rate', [
                    'rate'          => $booking->getRate(),
                    'rateCount'     => $booking->getRateCount(),
                    'rateClassName' => '',
                    'rateCountFont' => 'font-12',
                ])

                @if(!empty($booking->creator))
                    <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank"
                       class="d-flex align-items-center text-gray-500">
                        <x-iconsax-lin-profile class="icons text-gray-500" width="16px" height="16px"/>
                        <span class="ml-4 font-12 font-weight-bold">{{ truncate($booking->creator->full_name, 18) }}</span>
                    </a>
                @endif

                @if(!empty($booking->sales_count) or !empty($booking->orders_count))
                    <div class="d-flex align-items-center text-gray-500">
                        <x-iconsax-lin-money-2 class="icons text-gray-500" width="16px" height="16px"/>
                        <span class="mx-4 font-12 font-weight-bold">{{ $booking->sales_count ?? $booking->orders_count ?? 0 }}</span>
                        <span class="font-12">{{ trans('panel.sales') }}</span>
                    </div>
                @endif

                @if($booking->location_enabled and !empty($booking->city))
                    <div class="d-flex align-items-center text-gray-500">
                        <x-iconsax-lin-location class="icons text-gray-500" width="16px" height="16px"/>
                        <span class="ml-4 font-12 font-weight-bold">{{ $booking->city }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Price --}}
        <div class="d-flex align-items-center font-24 font-weight-bold text-primary mt-24">
            <span>{{ $booking->price_label }}</span>
            @if(!empty($booking->price_unit))
                <span class="font-14 font-weight-400 text-gray-500 ml-8">/ {{ $booking->price_unit }}</span>
            @endif
        </div>

        {{-- Capacity info --}}
        @if(!empty($booking->capacity) or !empty($booking->min_persons))
            <div class="d-flex align-items-center mt-8 text-gray-500">
                <x-iconsax-lin-people class="icons text-gray-500" width="16px" height="16px"/>
                @if(!empty($booking->min_persons) and !empty($booking->max_persons))
                    <span class="ml-4 font-12">{{ $booking->min_persons }} - {{ $booking->max_persons }} {{ trans('update.persons') }}</span>
                @elseif(!empty($booking->capacity))
                    <span class="ml-4 font-12">{{ trans('update.capacity') }}: {{ $booking->capacity }}</span>
                @endif
            </div>
        @endif

        {{-- Duration --}}
        @if(!empty($booking->duration_minutes))
            <div class="d-flex align-items-center mt-8 text-gray-500">
                <x-iconsax-lin-timer class="icons text-gray-500" width="16px" height="16px"/>
                <span class="ml-4 font-12">{{ $booking->duration_minutes }} {{ trans('update.minutes') }}</span>
            </div>
        @endif

        {{-- Action buttons --}}
        <div class="d-flex align-items-center gap-12 flex-wrap mt-16">
            <button type="button" id="bookingAddToCartBtn" class="btn btn-primary btn-lg">
                <x-iconsax-lin-calendar-2 class="icons text-white" width="24px" height="24px"/>
                <span class="ml-4 text-white">{{ trans('update.book_now') }}</span>
            </button>

            <button id="bookingFavoriteBtn" type="button"
                    class="btn btn-outline-secondary btn-lg d-flex align-items-center"
                    data-slug="{{ $booking->slug }}"
                    @if(auth()->guest()) data-login-url="/login" @endif>
                <x-iconsax-lin-heart class="js-empty-fav icons text-gray-500 mr-2 {{ !empty($isFavorited) ? 'd-none' : '' }}" width="20px" height="20px"/>
                <x-iconsax-bol-heart class="js-full-fav icons text-danger mr-2 {{ !empty($isFavorited) ? '' : 'd-none' }}" width="20px" height="20px"/>
                <span class="font-14">{{ !empty($isFavorited) ? trans('update.favorited') : trans('update.add_to_favorites') }}</span>
            </button>
        </div>

        {{-- Selected slot summary badge --}}
        <div id="selectedSlotSummary" style="display:none;" class="mt-12">
            <div class="slot-selected-badge">
                <x-iconsax-lin-calendar-2 class="icons" width="14px" height="14px"/>
                <span id="selectedSlotText" class="ml-6"></span>
            </div>
        </div>

    </div>
</div>