@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("search") }}">
@endpush

@section("content")
<main class="pb-80">

    {{-- ── Hero / Search Bar ─────────────────────────────────────────────── --}}
    <section class="search-hero position-relative">
        <img src="{{ getThemePageBackgroundSettings('search') }}"
             class="img-cover"
             alt="{{ trans('public.search') }}"/>
        <div class="search-hero__mask"></div>

        <div class="container position-relative d-flex-center flex-column z-index-3">
            <h1 class="font-24 font-weight-bold text-white">{{ trans('update.search_results') }}</h1>

            @if(!empty(request()->get('search')))
                <div class="mt-8 font-12 text-white opacity-75">
                    {{ trans('update.n_results_found_for_search', [
                        'count'  => $resultCount,
                        'search' => request()->get('search')
                    ]) }}
                </div>
            @endif

            <div class="row justify-content-center w-100 mt-20">
                <div class="col-12 col-lg-8">
                    {{-- Advanced search bar partial (includes dropdown + suggestions) --}}
                    @include('partials._search_bar')
                </div>
            </div>
        </div>
    </section>

    {{-- ── Main Content Area ──────────────────────────────────────────────── --}}
    <div class="container mt-48">
        <div class="row">

            {{-- ── LEFT SIDEBAR ─────────────────────────────────────────── --}}
            <div class="col-12 col-lg-3 mb-24 mb-lg-0">
                <div class="bg-white rounded border p-16">

                    {{-- Sidebar category controls --}}
                    <div class="d-flex align-items-center justify-content-between mb-12">
                        <div class="font-14 font-weight-bold">{{ trans('update.categories') }}</div>
                        <div class="d-flex" style="gap:4px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary js-select-all-cats"
                                    title="{{ trans('public.all') }}">✓</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary js-clear-all-cats"
                                    title="{{ trans('public.clear') ?? 'Clear' }}">✗</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary js-toggle-all-cats"
                                    title="{{ trans('update.toggle') ?? 'Toggle' }}">⊕</button>
                        </div>
                    </div>

                    <form id="filterForm" method="get" action="{{ route('search') }}">
                        {{-- Keep the search query --}}
                        <input type="hidden" name="search" value="{{ request()->get('search', '') }}">
                        @if(request()->filled('lat'))   <input type="hidden" name="lat"       value="{{ request()->get('lat') }}"> @endif
                        @if(request()->filled('lng'))   <input type="hidden" name="lng"       value="{{ request()->get('lng') }}"> @endif
                        @if(request()->filled('radius_km')) <input type="hidden" name="radius_km" value="{{ request()->get('radius_km') }}"> @endif
                        @if(request()->filled('sort'))  <input type="hidden" name="sort"      value="{{ request()->get('sort') }}"> @endif

                        <div style="max-height: 55vh; overflow-y: auto;">

                            {{-- Course categories --}}
                            <div class="mb-12">
                                <div class="font-11 font-weight-600 mb-6 text-uppercase opacity-60">
                                    📚 {{ trans('update.courses') }}
                                </div>
                                @forelse($categories as $category)
                                    <div class="mb-6">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox"
                                                   name="categories[]"
                                                   id="scat{{ $category->id }}"
                                                   value="{{ $category->id }}"
                                                   @if(in_array($category->id, request()->get('categories', []))) checked @endif
                                                   class="js-cat-checkbox mr-8">
                                            <label for="scat{{ $category->id }}" class="mb-0 cursor-pointer font-13">
                                                {{ $category->title }}
                                            </label>
                                        </div>
                                        @if(!empty($category->subCategories) && count($category->subCategories))
                                            <div class="ml-20 mt-4 subcats-list">
                                                @foreach($category->subCategories as $sub)
                                                    <div class="d-flex align-items-center mb-2">
                                                        <input type="checkbox"
                                                               name="categories[]"
                                                               id="scat{{ $sub->id }}"
                                                               value="{{ $sub->id }}"
                                                               @if(in_array($sub->id, request()->get('categories', []))) checked @endif
                                                               class="js-cat-checkbox mr-8">
                                                        <label for="scat{{ $sub->id }}" class="mb-0 cursor-pointer font-12 text-muted">
                                                            {{ $sub->title }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="font-12 text-muted">{{ trans('update.no_category') ?? 'No categories' }}</div>
                                @endforelse
                            </div>

                            {{-- Booking categories --}}
                            @if(!empty($bookingCategories) && $bookingCategories->isNotEmpty())
                                <div class="border-top pt-10 mt-4">
                                    <div class="font-11 font-weight-600 mb-6 text-uppercase opacity-60">
                                        🏨 {{ trans('home.bookings') ?? 'Services' }}
                                    </div>
                                    @foreach($bookingCategories as $bcat)
                                        <div class="mb-6">
                                            <div class="d-flex align-items-center">
                                                <input type="checkbox"
                                                       name="booking_categories[]"
                                                       id="sbcat{{ $bcat->id }}"
                                                       value="{{ $bcat->id }}"
                                                       @if(in_array($bcat->id, request()->get('booking_categories', []))) checked @endif
                                                       class="js-bcat-checkbox mr-8">
                                                <label for="sbcat{{ $bcat->id }}" class="mb-0 cursor-pointer font-13">
                                                    {{ $bcat->title }}
                                                </label>
                                            </div>
                                            @if(!empty($bcat->children) && $bcat->children->isNotEmpty())
                                                <div class="ml-20 mt-4 subcats-list">
                                                    @foreach($bcat->children as $bsub)
                                                        <div class="d-flex align-items-center mb-2">
                                                            <input type="checkbox"
                                                                   name="booking_categories[]"
                                                                   id="sbcat{{ $bsub->id }}"
                                                                   value="{{ $bsub->id }}"
                                                                   @if(in_array($bsub->id, request()->get('booking_categories', []))) checked @endif
                                                                   class="js-bcat-checkbox mr-8">
                                                            <label for="sbcat{{ $bsub->id }}" class="mb-0 cursor-pointer font-12 text-muted">
                                                                {{ $bsub->title }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>{{-- scrollable area --}}

                        {{-- ── Price range ──────────────────────────────── --}}
                        <div class="border-top pt-12 mt-12">
                            <div class="font-13 font-weight-600 mb-8">{{ trans('update.price_range') ?? 'Price Range' }}</div>
                            <div class="d-flex align-items-center gap-8" style="gap:8px;">
                                <input type="number"
                                       name="price_min"
                                       id="advPriceMin"
                                       value="{{ request()->get('price_min', 0) }}"
                                       min="0"
                                       class="form-control form-control-sm text-center"
                                       placeholder="Min"
                                       style="width:70px;">
                                <span class="text-muted">—</span>
                                <input type="number"
                                       name="price_max"
                                       id="advPriceMax"
                                       value="{{ request()->get('price_max', '') }}"
                                       min="0"
                                       class="form-control form-control-sm text-center"
                                       placeholder="Max"
                                       style="width:70px;">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-8 btn-block">
                                {{ trans('public.apply') ?? 'Apply' }}
                            </button>
                        </div>

                        {{-- ── Rating filter ────────────────────────────── --}}
                        <div class="border-top pt-12 mt-12">
                            <div class="font-13 font-weight-600 mb-8">{{ trans('update.min_rating') ?? 'Rating' }}</div>
                            <div class="d-flex flex-wrap" style="gap:4px;">
                                @foreach([4, 3, 2, 1] as $stars)
                                    <a href="{{ request()->fullUrlWithQuery(['rating' => $stars]) }}"
                                       class="btn btn-xs {{ request()->get('rating') == $stars ? 'btn-warning' : 'btn-outline-secondary' }}">
                                        {{ str_repeat('★', $stars) }}{{ str_repeat('☆', 5 - $stars) }}
                                    </a>
                                @endforeach
                                @if(request()->filled('rating'))
                                    <a href="{{ request()->fullUrlWithQuery(['rating' => '']) }}"
                                       class="btn btn-xs btn-link text-muted">
                                        {{ trans('public.clear') ?? 'Clear' }}
                                    </a>
                                @endif
                            </div>
                        </div>

                        {{-- ── Nearby filter ────────────────────────────── --}}
                        <div class="border-top pt-12 mt-12">
                            <div class="font-13 font-weight-600 mb-8">{{ trans('update.find_nearby') ?? 'Find Nearby' }}</div>
                            <div class="form-group mb-8">
                                <label class="font-12 text-muted mb-4">{{ trans('update.within_km') ?? 'Within (km)' }}</label>
                                <input type="number"
                                       name="radius_km"
                                       value="{{ request()->get('radius_km', 50) }}"
                                       min="1"
                                       max="500"
                                       class="form-control form-control-sm">
                            </div>
                            <div class="form-group mb-0">
                                <label class="font-12 text-muted mb-4">{{ trans('update.from_city') ?? 'From city' }}</label>
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           id="sidebarCityInput"
                                           class="form-control"
                                           placeholder="{{ trans('update.enter_city') ?? 'Enter city...' }}"
                                           value="{{ request()->get('city', '') }}"
                                           autocomplete="off">
                                    @if(request()->filled('lat'))
                                        <div class="input-group-append">
                                            <a href="{{ request()->fullUrlWithQuery(['lat' => '', 'lng' => '', 'city' => '']) }}"
                                               class="btn btn-outline-secondary btn-sm">×</a>
                                        </div>
                                    @endif
                                </div>
                                <input type="hidden" name="lat" value="{{ request()->get('lat', '') }}">
                                <input type="hidden" name="lng" value="{{ request()->get('lng', '') }}">
                                <input type="hidden" name="city" id="sidebarCityName" value="{{ request()->get('city', '') }}">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-8 btn-block">
                                📍 {{ trans('update.find_nearby') ?? 'Find Nearby' }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>{{-- /sidebar --}}

            {{-- ── RIGHT CONTENT ─────────────────────────────────────────── --}}
            <div class="col-12 col-lg-9">

                {{-- Sort + result count header --}}
                @if(!empty(request()->get('search')))
                    <div class="d-flex align-items-center justify-content-between mb-20 flex-wrap" style="gap:8px;">
                        <div class="font-14 text-muted">
                            {!! trans('update.n_results_found_for_search', [
                                'count'  => '<strong>' . $resultCount . '</strong>',
                                'search' => '<em>' . e(request()->get('search')) . '</em>',
                            ]) !!}
                        </div>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <label class="font-13 mb-0 text-muted">{{ trans('update.sort_by') ?? 'Sort' }}:</label>
                            <select id="advSortSelect" class="form-control form-control-sm" style="width:auto;">
                                <option value="relevance" {{ request()->get('sort','relevance') === 'relevance' ? 'selected' : '' }}>
                                    {{ trans('update.relevance') ?? 'Relevance' }}
                                </option>
                                <option value="price_asc" {{ request()->get('sort') === 'price_asc' ? 'selected' : '' }}>
                                    {{ trans('update.price_asc') ?? 'Price: Low → High' }}
                                </option>
                                <option value="price_desc" {{ request()->get('sort') === 'price_desc' ? 'selected' : '' }}>
                                    {{ trans('update.price_desc') ?? 'Price: High → Low' }}
                                </option>
                                <option value="rating" {{ request()->get('sort') === 'rating' ? 'selected' : '' }}>
                                    {{ trans('update.top_rated') ?? 'Top Rated' }}
                                </option>
                                @if(request()->filled('lat'))
                                    <option value="distance" {{ request()->get('sort') === 'distance' ? 'selected' : '' }}>
                                        {{ trans('update.nearest') ?? 'Nearest First' }}
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Type tabs --}}
                    <div class="adv-type-tabs d-flex flex-wrap mb-24" style="gap:6px;">
                        <button class="btn btn-sm adv-type-tab {{ !request()->get('tab') ? 'btn-primary' : 'btn-outline-secondary' }}"
                                data-type="all">
                            {{ trans('public.all') }}
                            <span class="badge badge-light ml-4">{{ $resultCount }}</span>
                        </button>

                        @php
                        $tabDefs = [
                            'bookings'       => ['label' => trans('home.bookings') ?? 'Bookings',        'var' => 'bookings'],
                            'webinars'       => ['label' => trans('update.courses'),                      'var' => 'webinars'],
                            'products'       => ['label' => trans('update.store_products'),               'var' => 'products'],
                            'bundles'        => ['label' => trans('update.bundles'),                      'var' => 'bundles'],
                            'booking_bundles'=> ['label' => trans('home.booking_bundles') ?? 'Booking Bundles', 'var' => 'bookingBundles'],
                            'instructors'    => ['label' => trans('home.instructors'),                    'var' => 'instructors'],
                            'organizations'  => ['label' => trans('home.organizations'),                  'var' => 'organizations'],
                            'posts'          => ['label' => trans('update.blog_posts'),                   'var' => 'posts'],
                        ];
                        @endphp

                     @foreach($tabDefs as $tabKey => $tabDef)

    @php
        $items = ${$tabDef['var']} ?? null;
    @endphp

    @if(!empty($items) && is_countable($items) && count($items) > 0)
        <button class="btn btn-sm adv-type-tab btn-outline-secondary"
                data-type="{{ $tabKey }}">
            {{ $tabDef['label'] }}
            <span class="badge badge-secondary ml-4">{{ count($items) }}</span>
        </button>
    @endif

@endforeach
                    </div>
                @endif

                {{-- ── BOOKINGS ──────────────────────────────────────────── --}}
                @if(!empty($bookings) && $bookings->isNotEmpty())
                    <section id="section-bookings" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                🏨 {{ trans('home.bookings') ?? 'Bookings' }}
                                <span class="badge badge-primary ml-8">{{ count($bookings) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @foreach($bookings as $booking)
                                <div class="col-12 col-md-6 col-lg-4 mb-16">
                                    <div class="card h-100 border shadow-sm">
                                        @if($booking->thumbnail)
                                            <img src="{{ $booking->thumbnail }}"
                                                 class="card-img-top"
                                                 alt="{{ $booking->title }}"
                                                 style="height:150px; object-fit:cover;">
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            {{-- Type badge --}}
                                            <div class="mb-6">
                                                <span class="badge badge-info font-10">{{ trans('home.bookings') ?? 'Booking' }}</span>
                                                @if($booking->category)
                                                    <span class="badge badge-light font-10">{{ $booking->category->title }}</span>
                                                @endif
                                                @if(request()->filled('lat') && isset($booking->distance))
                                                    <span class="badge badge-success font-10">
                                                        📍 {{ round($booking->distance, 1) }} km
                                                    </span>
                                                @endif
                                            </div>

                                            <h5 class="card-title font-15 font-weight-bold mb-6">
                                                {{ Str::limit($booking->title, 50) }}
                                            </h5>
                                            <p class="card-text font-12 text-muted flex-fill">
                                                {{ Str::limit($booking->description, 80) }}
                                            </p>

                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-8">
                                                <div>
                                                    @if($booking->price)
                                                        <span class="font-14 font-weight-bold text-primary">
                                                            {{ handlePrice($booking->price) }}
                                                            @if($booking->price_unit)
                                                                <span class="font-11 text-muted">/{{ $booking->price_unit }}</span>
                                                            @endif
                                                        </span>
                                                    @else
                                                        <span class="font-13 text-success">{{ trans('public.free') }}</span>
                                                    @endif
                                                    @if($booking->rating > 0)
                                                        <div class="font-11 text-warning">
                                                            {{ str_repeat('★', floor($booking->rating)) }}
                                                            <span class="text-muted">({{ $booking->rating }})</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <a href="{{ method_exists($booking, 'getUrl') ? $booking->getUrl() : url('/bookings/' . $booking->slug) }}"
                                                   class="btn btn-sm btn-primary">
                                                    {{ trans('public.book') ?? 'Book' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── BOOKING BUNDLES ───────────────────────────────────── --}}
                @if(!empty($bookingBundles) && $bookingBundles->isNotEmpty())
                    <section id="section-booking_bundles" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                🎁 {{ trans('home.booking_bundles') ?? 'Booking Bundles' }}
                                <span class="badge badge-primary ml-8">{{ count($bookingBundles) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @foreach($bookingBundles as $bundle)
                                <div class="col-12 col-md-6 col-lg-4 mb-16">
                                    <div class="card h-100 border shadow-sm">
                                        @if($bundle->thumbnail)
                                            <img src="{{ $bundle->thumbnail }}" class="card-img-top"
                                                 alt="{{ $bundle->title }}" style="height:150px; object-fit:cover;">
                                        @endif
                                        <div class="card-body d-flex flex-column">
                                            <span class="badge badge-secondary font-10 mb-6 align-self-start">
                                                {{ trans('home.booking_bundles') ?? 'Bundle' }}
                                            </span>
                                            <h5 class="card-title font-15 font-weight-bold mb-6">
                                                {{ Str::limit($bundle->title, 50) }}
                                            </h5>
                                            <p class="card-text font-12 text-muted flex-fill">
                                                {{ Str::limit($bundle->description, 80) }}
                                            </p>
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-8">
                                                <span class="font-14 font-weight-bold text-primary">
                                                    {{ $bundle->price ? handlePrice($bundle->price) : trans('public.free') }}
                                                </span>
                                                <a href="{{ url('/booking-bundles/' . $bundle->slug) }}"
                                                   class="btn btn-sm btn-primary">
                                                    {{ trans('public.view') ?? 'View' }}
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── COURSES ───────────────────────────────────────────── --}}
                @if(!empty($webinars) && $webinars->isNotEmpty())
                    <section id="section-webinars" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                📚 {{ trans('update.courses') }}
                                <span class="badge badge-primary ml-8">{{ count($webinars) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @include('design_1.web.courses.components.cards.grids.index', [
                                'courses'          => $webinars,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── BUNDLES ────────────────────────────────────────────── --}}
                @if(!empty($bundles) && $bundles->isNotEmpty())
                    <section id="section-bundles" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                📦 {{ trans('update.bundles') }}
                                <span class="badge badge-primary ml-8">{{ count($bundles) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @include('design_1.web.bundles.components.cards.grids.index', [
                                'bundles'          => $bundles,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── PRODUCTS ───────────────────────────────────────────── --}}
                @if(!empty($products) && $products->isNotEmpty())
                    <section id="section-products" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                🛍️ {{ trans('update.store_products') }}
                                <span class="badge badge-primary ml-8">{{ count($products) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @include('design_1.web.products.components.cards.grids.index', [
                                'products'         => $products,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── UPCOMING COURSES ──────────────────────────────────── --}}
                @if(!empty($upcomingCourses) && $upcomingCourses->isNotEmpty())
                    <section id="section-upcoming_courses" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                🗓️ {{ trans('update.upcoming_courses') }}
                                <span class="badge badge-primary ml-8">{{ count($upcomingCourses) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @include('design_1.web.upcoming_courses.components.cards.grids.index', [
                                'upcomingCourses'  => $upcomingCourses,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── BLOG POSTS ─────────────────────────────────────────── --}}
                @if(!empty($posts) && $posts->isNotEmpty())
                    <section id="section-posts" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                📰 {{ trans('update.blog_posts') }}
                                <span class="badge badge-primary ml-8">{{ count($posts) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @include('design_1.web.blog.components.cards.grids.index', [
                                'posts'            => $posts,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── ORGANIZATIONS ─────────────────────────────────────── --}}
                @if(!empty($organizations) && count($organizations))
                    <section id="section-organizations" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                🏢 {{ trans('home.organizations') }}
                                <span class="badge badge-primary ml-8">{{ count($organizations) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @foreach($organizations as $organ)
                                @include('design_1.web.search.includes.user_card', ['userCard' => $organ])
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── INSTRUCTORS ───────────────────────────────────────── --}}
                @if(!empty($instructors) && count($instructors))
                    <section id="section-instructors" class="adv-result-section mb-48">
                        <div class="d-flex align-items-center mb-16">
                            <h3 class="font-20 font-weight-bold mb-0">
                                👨‍🏫 {{ trans('home.instructors') }}
                                <span class="badge badge-primary ml-8">{{ count($instructors) }}</span>
                            </h3>
                        </div>
                        <div class="row">
                            @foreach($instructors as $instructor)
                                @include('design_1.web.search.includes.user_card', ['userCard' => $instructor])
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── EMPTY STATE ───────────────────────────────────────── --}}
                @if(
                    empty($webinars) && empty($bundles) && empty($products) &&
                    empty($upcomingCourses) && empty($posts) && empty($instructors) &&
                    empty($organizations) && empty($bookings) && empty($bookingBundles)
                )
                    <div class="text-center py-80">
                        <div style="font-size:48px; margin-bottom:16px;">🔍</div>
                        @if(!empty(request()->get('search')))
                            <h4 class="font-18 font-weight-bold mb-8">
                                {{ trans('update.no_results_found') ?? 'No results found' }}
                            </h4>
                            <p class="text-muted font-14">
                                {{ trans('update.try_different_keywords') ?? 'Try different keywords or broaden your filters.' }}
                            </p>
                        @else
                            <h4 class="font-18 font-weight-bold mb-8">
                                {{ trans('update.start_searching') ?? 'Start searching' }}
                            </h4>
                            <p class="text-muted font-14">
                                {{ trans('update.search_hint') ?? 'Type in the search bar above to find courses, bookings, products and more.' }}
                            </p>
                        @endif
                    </div>
                @endif

            </div>{{-- /col right --}}
        </div>{{-- /row --}}
    </div>{{-- /container --}}

    {{-- Sidebar city geocoding (small inline script) --}}
    <script>
    (function () {
        var $cityInput = document.getElementById('sidebarCityInput');
        var $latField  = document.querySelector('#filterForm input[name="lat"]');
        var $lngField  = document.querySelector('#filterForm input[name="lng"]');
        var $cityName  = document.getElementById('sidebarCityName');
        var geocodeTimeout;

        if (!$cityInput) return;

        // Auto-detect if no coords present
        @if(!request()->filled('lat'))
        fetch('https://ip-api.com/json/?fields=city,lat,lon')
            .then(r => r.json())
            .then(function (data) {
                if (data && data.city && $cityInput) {
                    $cityInput.value = data.city;
                    if ($latField) $latField.value = data.lat;
                    if ($lngField) $lngField.value = data.lon;
                    if ($cityName) $cityName.value = data.city;
                }
            })
            .catch(function () {});
        @endif

        $cityInput.addEventListener('input', function () {
            var city = this.value.trim();
            clearTimeout(geocodeTimeout);
            if (city.length < 2) {
                if ($latField) $latField.value = '';
                if ($lngField) $lngField.value = '';
                return;
            }
            geocodeTimeout = setTimeout(function () {
                fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(city))
                    .then(r => r.json())
                    .then(function (results) {
                        if (results && results.length > 0) {
                            if ($latField) $latField.value = results[0].lat;
                            if ($lngField) $lngField.value = results[0].lon;
                            if ($cityName) $cityName.value = city;
                        }
                    })
                    .catch(function () {});
            }, 500);
        });
    })();
    </script>

</main>
@endsection

@push('scripts_bottom')
    <script src="{{ getDesign1ScriptPath("search") }}"></script>
    <script src="{{ asset('js/advanced_search.js') }}"></script>
@endpush