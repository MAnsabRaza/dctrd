@if(!empty($featuredBookings) and count($featuredBookings))
    @php
        $featuredBookingsSettings = getStoreFeaturedBookingsSettings();
        $featuredBg               = $featuredBookingsSettings['background_image'] ?? null;
        $featuredOverlayImage     = $featuredBookingsSettings['overlay_image']    ?? null;
    @endphp

    <div class="products-lists-featured position-relative mt-48 bg-gray-200"
         @if(!empty($featuredBg)) style="background-image: url('{{ $featuredBg }}')" @endif>
        <div class="container position-relative h-100 pt-48 pb-56">

            @if(!empty($featuredOverlayImage))
                <div class="products-lists-featured__overlay-image d-flex-center">
                    <img src="{{ $featuredOverlayImage }}" alt="{{ trans('update.overlay_image') }}" class="img-fluid">
                </div>
            @endif

            <div class="row w-100 h-100">
                <div class="col-12 col-lg-3">
                    <div class="d-flex justify-content-center flex-column text-left w-100 h-100">
                        <div class="products-lists-featured__arrow">
                            <img src="/assets/design_1/img/courses/featured_courses_arrow.svg"
                                 alt="featured_arrow" class="">
                        </div>
                        <h3 class="font-24 font-weight-bold text-white">{{ trans('update.featured_bookings') }}</h3>
                        <div class="mt-8 text-white">{{ trans('update.explore_hand_picked_bookings') }}</div>
                    </div>
                </div>

                <div class="col-12 col-lg-9 mt-20 mt-lg-0">
                    <div class="position-relative">
                        <div class="swiper-container js-make-swiper top-featured-bookings pb-0"
                             data-item="top-featured-bookings"
                             data-autoplay="true"
                             data-loop="true"
                             data-autoplay-delay="4000"
                             data-breakpoints="1440:2,769:2,320:1">
                            <div class="swiper-wrapper py-0">
                                @foreach($featuredBookings as $booking)
                                    <div class="swiper-slide">
                                        @include('design_1.web.bookings.components.cards.grids.grid_card_1', ['booking' => $booking])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif