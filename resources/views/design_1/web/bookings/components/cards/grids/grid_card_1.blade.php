<a href="{{ $booking->getUrl() }}" class="text-decoration-none d-block">
    <div class="product-card position-relative">
        <div class="product-card__mask"></div>

        <div class="position-absolute" style="top: 16px; right: 16px; z-index: 10;">
                <div class="bookingFavoriteBtn d-flex align-items-center justify-content-center rounded-circle bg-white border border-gray-200" style="width: 38px; height: 38px; cursor: pointer;" data-slug="{{ $booking->slug }}">
                <x-iconsax-bol-heart class="icons js-full-fav text-danger {{ !empty($booking->isFavorited) ? '' : 'd-none' }}" width="20px" height="20px"/>
            </div>
        </div>

        <div class="position-relative d-flex p-12 rounded-16 bg-white z-index-2">
            <div class="product-card__image rounded-16">
                <img src="{{ $booking->thumbnail_url }}" class="img-cover rounded-16" alt="{{ $booking->title }}">
            </div>

            <div class="product-card__content ml-12 flex-1 d-flex flex-column">
                <h3 class="product-card__title font-weight-bold font-16 text-dark">{{ clean($booking->title, 'title') }}</h3>

                <div class="d-flex align-items-center my-16" onclick="event.stopPropagation()">
                    @if(!empty($booking->creator))
                        <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank" class="size-32 rounded-circle" onclick="event.stopPropagation()">
                            <img src="{{ $booking->creator->getAvatar(32) }}" class="img-cover rounded-circle" alt="{{ $booking->creator->full_name }}">
                        </a>
                    @endif

                    <div class="d-flex flex-column ml-4">
                        @if(!empty($booking->creator))
                            <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank" class="font-14 font-weight-bold text-dark" onclick="event.stopPropagation()">{{ $booking->creator->full_name }}</a>
                        @endif

                        @if(!empty($booking->category))
                            <div class="d-inline-flex align-items-center gap-4 mt-2 font-14 text-gray-500">
                                <span>{{ trans('public.in') }}</span>
                                <span class="font-14 text-gray-500 text-ellipsis">{{ $booking->category->title }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-auto d-flex align-items-center justify-content-between">
                    <div>
                        @include('design_1.web.components.rate', [
                            'rate' => round($booking->getRate(), 1),
                            'rateCount' => $booking->getRateCount(),
                            'rateClassName' => '',
                        ])
                    </div>

                    <div class="d-flex align-items-center font-16 font-weight-bold text-primary">
                        <span>{{ $booking->price_label }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</a>
