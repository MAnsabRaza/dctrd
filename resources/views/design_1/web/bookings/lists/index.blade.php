@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("products_lists") }}">
@endpush

@php
    $pageHeroImage    = getThemePageBackgroundSettings('bookings_lists');
    $pageOverlayImage = getThemePageBackgroundSettings('bookings_lists_overlay_image');
@endphp

@section("content")
    <main class="pb-120">
        <section class="products-lists-hero position-relative">
            <div class="products-lists-hero__mask"></div>
            <img src="{{ $pageHeroImage }}" class="img-cover" alt="Bookings"/>
        </section>

        <div class="container">
            <div class="products-lists-header position-relative z-index-2">
                <div class="products-lists-header__mask z-index-5"></div>
                <div class="position-relative d-flex align-items-start bg-white rounded-32 z-index-8">
                    <div class="d-flex flex-column p-32">
                        <div class="d-flex-center size-64 rounded-12 bg-accent-30">
                            <x-iconsax-bul-calendar-2 class="icons text-accent" width="32px" height="32px"/>
                        </div>

                        <div class="d-flex align-items-center mt-16 text-gray-500">
                            <a href="/" class="text-gray-500">{{ getPlatformName() }}</a>
                            <x-iconsax-lin-arrow-right-1 class="mx-4" width="16px" height="16px"/>
                            <span>{{ trans('update.bookings') }}</span>
                        </div>

                        <h1 class="font-24 font-weight-bold mt-12">{{ $pageTitle }}</h1>
                        <div class="font-12 text-gray-500 mt-8">{{ $pageDescription }}</div>
                    </div>

                    @if(!empty($pageOverlayImage))
                        <div class="products-lists-header__overlay-img">
                            <img src="{{ $pageOverlayImage }}" alt="Bookings" class="img-cover">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Top Categories Slider --}}
            @include('design_1.web.bookings.lists.includes.top_categories')
        </div>

        {{-- Featured Bookings --}}
        @include('design_1.web.bookings.lists.includes.featured_bookings')

        <form action="/bookings" class="js-get-view-data-by-timeout-change container mt-24" data-container-id="bookingsListsContainer">

            {{-- Top Filters --}}
            @include("design_1.web.bookings.lists.includes.top_filters")

            <div class="row">
                {{-- Left Filters --}}
                <div class="col-12 col-lg-3 mt-28">
                    @include("design_1.web.bookings.lists.includes.left_filters")
                </div>

                {{-- Bookings Grid --}}
                <div class="col-12 col-lg-9 mt-4">
                    <div id="bookingsListsContainer" data-body=".js-lists-body" data-view-data-path="/bookings">
                        <div class="js-lists-body row">
                            @include("design_1.web.bookings.components.cards.grids.index", [
                                'bookings'          => $bookings,
                                'gridCardClassName' => 'col-12 col-lg-6 mt-24'
                            ])
                        </div>

                        <div id="pagination" class="js-ajax-pagination" data-container-id="bookingsListsContainer" data-container-items=".js-lists-body">
                            {!! $pagination !!}
                        </div>
                    </div>

                    <div class="js-page-bottom-seo-content">
                        @include('design_1.web.bookings.lists.includes.bottom_seo_content', [
                            'seoContent' => $pageBottomSeoContent ?? null
                        ])
                    </div>
                </div>
            </div>
        </form>

        <div class="container">
            {{-- Featured Categories --}}
            @include('design_1.web.bookings.lists.includes.featured_categories')
        </div>
    </main>
@endsection

@push('scripts_bottom')
    <script src="/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ getDesign1ScriptPath("range_slider_helpers") }}"></script>
    <script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>
    <script src="{{ getDesign1ScriptPath("products_lists") }}"></script>
    <script>
        (function ($) {
            'use strict';

            $('body').on('click', '.bookingFavoriteBtn', function (e) {
                e.preventDefault();
                e.stopPropagation();

                var $btn      = $(this);
                var slug      = $btn.data('slug');
                var loginUrl  = $btn.data('login-url');
                var $emptyIco = $btn.find('.js-empty-fav');
                var $fullIco  = $btn.find('.js-full-fav');

                if (loginUrl) { window.location = loginUrl; return; }
                if (!slug) return;

                $.ajax({
                    url: '/bookings/' + slug + '/favorite-toggle',
                    method: 'GET',
                    dataType: 'json'
                }).done(function (res) {
                    if (res && res.status === 'added') {
                        $emptyIco.addClass('d-none');
                        $fullIco.removeClass('d-none');
                    } else {
                        $emptyIco.removeClass('d-none');
                        $fullIco.addClass('d-none');
                    }
                }).fail(function (xhr) {
                    if ([401, 302, 419].includes(xhr.status)) {
                        window.location = '/login';
                    } else {
                        showToast('error', 'Error', 'Could not update favorite');
                    }
                });
            });
        })(jQuery);
    </script>
@endpush