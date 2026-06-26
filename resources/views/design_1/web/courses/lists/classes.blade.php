@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/vendors/wrunner-html-range-slider-with-2-handles/css/wrunner-default-theme.css">
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("courses_lists") }}">
@endpush

@section("content")
    <main class="pb-120">

        @php
            $pageHeroImage = getThemePageBackgroundSettings('classes_lists');
            $pageOverlayImage = getThemePageBackgroundSettings('classes_lists_overlay_image');
        @endphp

        <section class="courses-lists-hero position-relative">
            <div class="courses-lists-hero__mask"></div>
            <img src="{{ $pageHeroImage }}" class="img-cover" alt="{{ trans('update.search_categories') }}"/>
        </section>


        {{-- Header --}}
        <div class="container">
            <div class="courses-lists-header position-relative">
                <div class="courses-lists-header__mask"></div>
                <div class="position-relative d-flex align-items-start bg-white rounded-32 z-index-2">
                    <div class="d-flex flex-column p-32">
                        <div class="d-flex-center size-64 rounded-12 bg-primary-30">
                            <x-iconsax-bul-video-play class="icons text-primary" width="32px" height="32px"/>
                        </div>

                        <div class="d-flex align-items-center mt-16 text-gray-500">
                            <a href="/" class="text-gray-500">{{ getPlatformName() }}</a>
                            <x-iconsax-lin-arrow-right-1 class="mx-4" width="16px" height="16px"/>
                            <span class="">{{ trans('update.courses') }}</span>
                        </div>

                        <h1 class="font-24 font-weight-bold mt-12">{{ $pageTitle }}</h1>
                        <div class="font-12 text-gray-500 mt-8">{{ trans('update.all_thing_about') }}</div>
                    </div>

                    @if(!empty($pageOverlayImage))
                        <div class="courses-lists-header__overlay-img">
                            <img src="{{ $pageOverlayImage }}" alt="{{ $pageTitle }}" class="img-cover">
                        </div>
                    @endif
                </div>
            </div>
        </div>


        <form action="{{ $pageBasePath }}" class="js-get-view-data-by-timeout-change container mt-24" data-container-id="listsContainer">
            {{-- Top Filters --}}
            @include("design_1.web.courses.lists.includes.top_filters")

            <div class="row">
                {{-- Left Filters --}}
                <div class="col-12 col-lg-3 mt-28">
                    @include("design_1.web.courses.lists.includes.left_filters")
                </div>

                {{-- Courses Lists --}}
                <div class="col-12 col-lg-9 mt-4">
                    <div id="listsContainer" class="" data-body=".js-lists-body" data-view-data-path="{{ $pageBasePath }}">
                        <div class="js-lists-body row">
                            @if(request()->get('card') == "list")
                                @include('design_1.web.courses.components.cards.rows.index',['courses' => $courses, 'rowCardClassName' => "col-12 mt-24"])
                            @else
                                @include('design_1.web.courses.components.cards.grids.index',['courses' => $courses, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-24"])
                            @endif
                        </div>

                        {{-- Pagination --}}
                        <div id="pagination" class="js-ajax-pagination" data-container-id="listsContainer" data-container-items=".js-lists-body">
                            {!! $pagination !!}
                        </div>
                    </div>


                    {{-- Seo Content --}}
                    @if(!empty($category->bottom_seo_title) and !empty($category->bottom_seo_content))
                        <section class="bg-gray-100 p-16 rounded-24 border-gray-200 mt-48">
                            <h3 class="font-14">{{ $category->bottom_seo_title }}</h3>
                            <div class="mt-12 text-gray-500">{!! nl2br($category->bottom_seo_content) !!}</div>
                        </section>
                    @endif
                </div>
            </div>
        </form>
    </main>
@endsection


@push('scripts_bottom')
    <script src="/assets/vendors/wrunner-html-range-slider-with-2-handles/js/wrunner-jquery.js"></script>
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    <script src="{{ getDesign1ScriptPath("range_slider_helpers") }}"></script>
    <script src="{{ getDesign1ScriptPath("courses_lists") }}"></script>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    var btnGeo    = document.getElementById('btn-course-use-my-location');
    var inputLat  = document.getElementById('course-filter-lat');
    var inputLng  = document.getElementById('course-filter-lng');
    var addrInput = document.getElementById('course-location-address-input');

    if (btnGeo) {
        btnGeo.addEventListener('click', function () {
            if (!navigator.geolocation) {
                alert('{{ trans("update.geolocation_not_supported") }}');
                return;
            }
            btnGeo.disabled = true;
            btnGeo.textContent = '{{ trans("update.detecting") }}...';

            navigator.geolocation.getCurrentPosition(
                function (pos) {
                    inputLat.value = pos.coords.latitude.toFixed(6);
                    inputLng.value = pos.coords.longitude.toFixed(6);
                    btnGeo.disabled = false;
                    btnGeo.innerHTML = '<i class="fa fa-check mr-1"></i> {{ trans("update.location_detected") }}';
                    inputLat.dispatchEvent(new Event('change', { bubbles: true }));
                },
                function () {
                    btnGeo.disabled = false;
                    btnGeo.innerHTML = '<i class="fa fa-crosshairs mr-1"></i> {{ trans("update.use_my_location") }}';
                    alert('{{ trans("update.location_permission_denied") }}');
                }
            );
        });
    }

    if (addrInput) {
        var debounceTimer;
        addrInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var q = addrInput.value.trim();
            if (q.length < 4) return;

            debounceTimer = setTimeout(function () {
                fetch('https://nominatim.openstreetmap.org/search?format=json&q='
                    + encodeURIComponent(q) + '&limit=1', {
                    headers: { 'Accept-Language': 'en' }
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data && data[0]) {
                        inputLat.value = parseFloat(data[0].lat).toFixed(6);
                        inputLng.value = parseFloat(data[0].lon).toFixed(6);
                        inputLat.dispatchEvent(new Event('change', { bubbles: true }));
                    }
                });
            }, 600);
        });
    }
});
</script>
@endpush
