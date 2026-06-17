@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("search") }}">
    <style>
        /* ── Section headings ─────────────────────────────── */
        .result-section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }
        .result-section-heading h3 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
            color: #1a1a2e;
        }
        .result-section-heading .badge {
            font-size: 11px;
            padding: 3px 8px;
            border-radius: 20px;
        }

        /* ── Type tab pills ───────────────────────────────── */
        .adv-type-tabs { flex-wrap: wrap; gap: 6px; }
        .adv-type-tab {
            border-radius: 20px !important;
            font-size: 12px;
            padding: 4px 12px;
            font-weight: 500;
        }
        .adv-type-tab.btn-primary { box-shadow: 0 2px 6px rgba(37,99,235,.3); }

        /* ── Sidebar ──────────────────────────────────────── */
        .sidebar-section-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .05em;
            text-transform: uppercase;
            color: #6b7280;
            margin-bottom: 8px;
            display: block;
        }
        .sidebar-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 16px;
        }

        /* ── Booking / Bundle result card ─────────────────── */
        .booking-card {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            overflow: hidden;
            height: 100%;
            display: flex;
            flex-direction: column;
            transition: box-shadow .2s;
        }
        .booking-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.10); }
        .booking-card__img  { height: 150px; object-fit: cover; width: 100%; background: #f3f4f6; }
        .booking-card__body { padding: 12px; display: flex; flex-direction: column; flex: 1; }
        .booking-card__title { font-size: 14px; font-weight: 600; margin-bottom: 4px; }
        .booking-card__desc  { font-size: 12px; color: #6b7280; flex: 1; margin-bottom: 0; }
        .booking-card__footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid #f3f4f6;
        }

        /* ── Rating stars ─────────────────────────────────── */
        .js-rating-btn.active,
        .js-rating-btn.btn-warning { font-size: 11px; }

        .cursor-pointer { cursor: pointer; }
    </style>
@endpush

@section("content")
<main class="pb-80">

    {{-- ═══════════════════════════════════════════════════════════
         HERO / SEARCH BAR
    ═══════════════════════════════════════════════════════════ --}}
    <section class="search-hero position-relative">
        <img src="{{ getThemePageBackgroundSettings('search') }}"
             class="img-cover" alt="Search"/>
        <div class="search-hero__mask"></div>

        <div class="container position-relative d-flex-center flex-column z-index-3">
            <h1 class="font-24 font-weight-bold text-white">Search Results</h1>

            @if(!empty(request()->get('search')))
                <div class="mt-8 font-12 text-white opacity-75">
                    <strong>{{ $resultCount }}</strong> results found for
                    "<strong>{{ request()->get('search') }}</strong>"
                </div>
            @endif

            <div class="row justify-content-center w-100 mt-20">
                <div class="col-12 col-lg-8">
                    @include('partials._search_bar')
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════════════════ --}}
    <div class="container mt-48">
        <div class="row">

            {{-- ═══════════════════════════════════════════════════
                 LEFT SIDEBAR
            ═══════════════════════════════════════════════════ --}}
            <div class="col-12 col-lg-3 mb-24 mb-lg-0">
                <div class="sidebar-card">

                    {{-- Header + Quick controls --}}
                    <div class="d-flex align-items-center justify-content-between mb-14">
                        <span class="sidebar-section-label mb-0">Categories</span>
                        <div class="d-flex" style="gap:4px;">
                            <button type="button" class="btn btn-xs btn-outline-secondary js-select-all-cats"
                                    title="Select All">✓</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary js-clear-all-cats"
                                    title="Clear All">✗</button>
                            <button type="button" class="btn btn-xs btn-outline-secondary js-toggle-all-cats"
                                    title="Expand / Collapse">⊕</button>
                        </div>
                    </div>

                    {{-- ─────────────────────────────────────────────
                         FILTER FORM
                         ALL filter inputs must be inside this form
                    ───────────────────────────────────────────── --}}
                    <form id="filterForm" method="get" action="{{ route('search') }}">

                        {{-- Always carry the search keyword --}}
                        <input type="hidden" name="search" value="{{ request()->get('search', '') }}">

                        {{-- Carry current sort --}}
                        @if(request()->filled('sort'))
                            <input type="hidden" name="sort" value="{{ request()->get('sort') }}">
                        @endif

                        {{-- ── SCROLLABLE CATEGORY AREA ──────────────── --}}
                        <div style="max-height:55vh; overflow-y:auto; padding-right:4px;">

                            {{-- Course / webinar categories --}}
                            <div class="mb-14">
                                <span class="sidebar-section-label">📚 Courses</span>

                                @forelse($categories as $category)
                                    <div class="mb-6">
                                        <div class="d-flex align-items-center">
                                            <input type="checkbox"
                                                   name="categories[]"
                                                   id="scat{{ $category->id }}"
                                                   value="{{ $category->id }}"
                                                   @if(in_array($category->id, (array) request()->get('categories', []))) checked @endif
                                                   class="js-cat-checkbox mr-8">
                                            <label for="scat{{ $category->id }}"
                                                   class="mb-0 cursor-pointer"
                                                   style="font-size:13px;">
                                                {{ $category->title }}
                                            </label>
                                        </div>

                                        @if(!empty($category->subCategories) && count($category->subCategories))
                                            <div class="ml-20 mt-4 subcats-list">
                                                @foreach($category->subCategories as $sub)
                                                    <div class="d-flex align-items-center mb-3">
                                                        <input type="checkbox"
                                                               name="categories[]"
                                                               id="scat{{ $sub->id }}"
                                                               value="{{ $sub->id }}"
                                                               @if(in_array($sub->id, (array) request()->get('categories', []))) checked @endif
                                                               class="js-cat-checkbox mr-8">
                                                        <label for="scat{{ $sub->id }}"
                                                               class="mb-0 cursor-pointer text-muted"
                                                               style="font-size:12px;">
                                                            {{ $sub->title }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-muted" style="font-size:12px;">No categories</div>
                                @endforelse
                            </div>

                            {{-- Booking categories --}}
                            @if(!empty($bookingCategories) && $bookingCategories->isNotEmpty())
                                <div class="border-top pt-12 mt-4">
                                    <span class="sidebar-section-label">🏨 Bookings</span>

                                    @foreach($bookingCategories as $bcat)
                                        <div class="mb-6">
                                            <div class="d-flex align-items-center">
                                                <input type="checkbox"
                                                       name="booking_categories[]"
                                                       id="sbcat{{ $bcat->id }}"
                                                       value="{{ $bcat->id }}"
                                                       @if(in_array($bcat->id, (array) request()->get('booking_categories', []))) checked @endif
                                                       class="js-bcat-checkbox mr-8">
                                                <label for="sbcat{{ $bcat->id }}"
                                                       class="mb-0 cursor-pointer"
                                                       style="font-size:13px;">
                                                    {{ $bcat->title }}
                                                </label>
                                            </div>

                                            @if(!empty($bcat->children) && $bcat->children->isNotEmpty())
                                                <div class="ml-20 mt-4 subcats-list">
                                                    @foreach($bcat->children as $bsub)
                                                        <div class="d-flex align-items-center mb-3">
                                                            <input type="checkbox"
                                                                   name="booking_categories[]"
                                                                   id="sbcat{{ $bsub->id }}"
                                                                   value="{{ $bsub->id }}"
                                                                   @if(in_array($bsub->id, (array) request()->get('booking_categories', []))) checked @endif
                                                                   class="js-bcat-checkbox mr-8">
                                                            <label for="sbcat{{ $bsub->id }}"
                                                                   class="mb-0 cursor-pointer text-muted"
                                                                   style="font-size:12px;">
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

                        </div>{{-- /scrollable --}}

                        {{-- ── PRICE RANGE ──────────────────────────────── --}}
                        <div class="border-top pt-12 mt-12">
                            <span class="sidebar-section-label">Price Range</span>
                            <div class="d-flex align-items-center" style="gap:8px;">
                                <input type="number"
                                       name="price_min"
                                       id="filterPriceMin"
                                       value="{{ request()->get('price_min', '') }}"
                                       min="0"
                                       class="form-control form-control-sm text-center"
                                       placeholder="Min"
                                       style="width:72px;">
                                <span class="text-muted">—</span>
                                <input type="number"
                                       name="price_max"
                                       id="filterPriceMax"
                                       value="{{ request()->get('price_max', '') }}"
                                       min="0"
                                       class="form-control form-control-sm text-center"
                                       placeholder="Max"
                                       style="width:72px;">
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-primary mt-8 btn-block">
                                Apply Price
                            </button>
                        </div>

                        {{-- ── RATING FILTER ────────────────────────────── --}}
                        <!-- <div class="border-top pt-12 mt-12">
                            <span class="sidebar-section-label">Rating</span>
                            {{-- Hidden field — JS sets this then submits --}}
                            <input type="hidden" name="rating" id="filterRatingValue"
                                   value="{{ request()->get('rating', '') }}">
                            <div class="d-flex flex-wrap" style="gap:4px;">
                                @foreach([4, 3, 2, 1] as $stars)
                                    <button type="button"
                                            class="btn btn-xs js-rating-btn {{ request()->get('rating') == $stars ? 'btn-warning' : 'btn-outline-secondary' }}"
                                            data-rating="{{ $stars }}">
                                        {{ str_repeat('★', $stars) }}{{ str_repeat('☆', 5 - $stars) }}
                                    </button>
                                @endforeach
                                @if(request()->filled('rating'))
                                    <button type="button"
                                            class="btn btn-xs btn-link text-danger js-rating-clear"
                                            title="Clear rating">✕</button>
                                @endif
                            </div>
                        </div> -->

                        {{-- ── NEARBY FILTER ────────────────────────────── --}}
                        <div class="border-top pt-12 mt-12">
                            <span class="sidebar-section-label">📍 Find Nearby</span>

                            <div class="form-group mb-8">
                                <label class="text-muted mb-2" style="font-size:12px;">Within (km)</label>
                                <input type="number"
                                       name="radius_km"
                                       id="filterRadiusKm"
                                       value="{{ request()->get('radius_km', 50) }}"
                                       min="1" max="500"
                                       class="form-control form-control-sm">
                            </div>

                            <div class="form-group mb-8">
                                <label class="text-muted mb-2" style="font-size:12px;">From city</label>
                                <div class="input-group input-group-sm">
                                    <input type="text"
                                           id="sidebarCityInput"
                                           class="form-control"
                                           placeholder="Enter city..."
                                           value="{{ request()->get('city', '') }}"
                                           autocomplete="off">
                                    @if(request()->filled('city'))
                                        <div class="input-group-append">
                                            <button type="button" id="sidebarClearCity"
                                                    class="btn btn-outline-secondary btn-sm">×</button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- CRITICAL: lat/lng/city hidden fields inside the form --}}
                            <input type="hidden" name="lat"  id="filterLat"      value="{{ request()->get('lat', '') }}">
                            <input type="hidden" name="lng"  id="filterLng"      value="{{ request()->get('lng', '') }}">
                            <input type="hidden" name="city" id="filterCityName" value="{{ request()->get('city', '') }}">

                            <button type="submit" class="btn btn-sm btn-primary mt-4 btn-block">
                                📍 Find Nearby
                            </button>

                            @if(request()->filled('lat'))
                                <a href="{{ request()->fullUrlWithQuery(['lat' => '', 'lng' => '', 'city' => '', 'radius_km' => '']) }}"
                                   class="btn btn-sm btn-link btn-block text-muted mt-4"
                                   style="font-size:12px;">
                                    ✕ Clear location
                                </a>
                            @endif
                        </div>

                    </form>{{-- /filterForm --}}
                </div>{{-- /sidebar-card --}}
            </div>{{-- /sidebar col --}}

            {{-- ═══════════════════════════════════════════════════
                 RIGHT RESULTS AREA
            ═══════════════════════════════════════════════════ --}}
            <div class="col-12 col-lg-9">

                @if(!empty(request()->get('search')))

                    {{-- Sort + Count Header --}}
                    <div class="d-flex align-items-center justify-content-between mb-16 flex-wrap" style="gap:8px;">
                        <div style="font-size:14px; color:#6b7280;">
                            <strong>{{ $resultCount }}</strong> results found for
                            <em>"{{ request()->get('search') }}"</em>
                        </div>
                        <div class="d-flex align-items-center" style="gap:8px;">
                            <span style="font-size:13px; color:#6b7280;">Sort by:</span>
                            <select id="advSortSelect" class="form-control form-control-sm" style="width:auto;">
                                <option value="relevance" {{ request()->get('sort', 'relevance') === 'relevance' ? 'selected' : '' }}>
                                    Relevance
                                </option>
                                <option value="price_asc" {{ request()->get('sort') === 'price_asc' ? 'selected' : '' }}>
                                    Price: Low → High
                                </option>
                                <option value="price_desc" {{ request()->get('sort') === 'price_desc' ? 'selected' : '' }}>
                                    Price: High → Low
                                </option>
                                <option value="rating" {{ request()->get('sort') === 'rating' ? 'selected' : '' }}>
                                    Top Rated
                                </option>
                                @if(request()->filled('lat'))
                                    <option value="distance" {{ request()->get('sort') === 'distance' ? 'selected' : '' }}>
                                        Nearest First
                                    </option>
                                @endif
                            </select>
                        </div>
                    </div>

                    {{-- Type Tabs --}}
                    @php
                        $tabDefs = [
                            'bookings'        => ['label' => 'Bookings',        'var' => 'bookings'],
                            'webinars'        => ['label' => 'Courses',         'var' => 'webinars'],
                            'products'        => ['label' => 'Store Products',  'var' => 'products'],
                            'bundles'         => ['label' => 'Course Bundles',  'var' => 'bundles'],
                            'booking_bundles' => ['label' => 'Booking Bundles', 'var' => 'bookingBundles'],
                            'instructors'     => ['label' => 'Instructors',     'var' => 'instructors'],
                            'organizations'   => ['label' => 'Organizations',   'var' => 'organizations'],
                            'posts'           => ['label' => 'Blog Posts',      'var' => 'posts'],
                        ];
                    @endphp

                    <div class="adv-type-tabs d-flex mb-24">
                        <button class="btn btn-sm adv-type-tab {{ !request()->get('tab') ? 'btn-primary' : 'btn-outline-secondary' }}"
                                data-type="all">
                            All
                            <span class="badge badge-light ml-4">{{ $resultCount }}</span>
                        </button>

                        @foreach($tabDefs as $tabKey => $tabDef)
                            @php $tabItems = ${$tabDef['var']} ?? null; @endphp
                            @if(!empty($tabItems) && is_countable($tabItems) && count($tabItems) > 0)
                                <button class="btn btn-sm adv-type-tab btn-outline-secondary"
                                        data-type="{{ $tabKey }}">
                                    {{ $tabDef['label'] }}
                                    <span class="badge badge-secondary ml-4">{{ count($tabItems) }}</span>
                                </button>
                            @endif
                        @endforeach
                    </div>

                @endif

                {{-- ════════════════════════════════════════════════
                     RESULT SECTIONS
                ════════════════════════════════════════════════ --}}

                {{-- ── BOOKINGS ───────────────────────────────── --}}
                @if(!empty($bookings) && $bookings->isNotEmpty())
                    <section id="section-bookings" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">🏨</span>
                            <h3>Bookings</h3>
                            <span class="badge badge-primary">{{ count($bookings) }}</span>
                        </div>
                        <div class="row">
                            @foreach($bookings as $booking)
                                <div class="col-12 col-md-6 col-lg-4 mb-16 d-flex">
                                    <div class="booking-card w-100">
                                        @if($booking->thumbnail)
                                            <img src="{{ $booking->thumbnail }}"
                                                 class="booking-card__img"
                                                 alt="{{ $booking->title }}">
                                        @else
                                            <div class="booking-card__img d-flex-center bg-light"
                                                 style="font-size:36px;">🏨</div>
                                        @endif

                                        <div class="booking-card__body">
                                            <div class="mb-6 d-flex flex-wrap" style="gap:4px;">
                                                <span class="badge badge-info" style="font-size:10px;">Booking</span>
                                                @if($booking->category)
                                                    <span class="badge badge-light" style="font-size:10px;">
                                                        {{ $booking->category->title }}
                                                    </span>
                                                @endif
                                                @if(request()->filled('lat') && isset($booking->distance))
                                                    <span class="badge badge-success" style="font-size:10px;">
                                                        📍 {{ round($booking->distance, 1) }} km
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="booking-card__title">
                                                {{ Str::limit($booking->title, 50) }}
                                            </div>
                                            <p class="booking-card__desc">
                                                {{ Str::limit($booking->description, 80) }}
                                            </p>

                                            <div class="booking-card__footer">
                                                <div>
                                                    @if($booking->price)
                                                        <span style="font-size:14px; font-weight:700; color:#2563eb;">
                                                            {{ handlePrice($booking->price) }}
                                                        </span>
                                                        @if($booking->price_unit)
                                                            <span class="text-muted" style="font-size:11px;">
                                                                /{{ $booking->price_unit }}
                                                            </span>
                                                        @endif
                                                    @else
                                                        <span class="text-success" style="font-size:13px;">Free</span>
                                                    @endif
                                                    @if(!empty($booking->rating) && $booking->rating > 0)
                                                        <div class="text-warning" style="font-size:11px;">
                                                            {{ str_repeat('★', floor($booking->rating)) }}
                                                            <span class="text-muted">({{ $booking->rating }})</span>
                                                        </div>
                                                    @endif
                                                </div>
                                                <a href="{{ method_exists($booking, 'getUrl') ? $booking->getUrl() : url('/bookings/' . $booking->slug) }}"
                                                   class="btn btn-sm btn-primary">Book</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── BOOKING BUNDLES ────────────────────────── --}}
                @if(!empty($bookingBundles) && $bookingBundles->isNotEmpty())
                    <section id="section-booking_bundles" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">🎁</span>
                            <h3>Booking Bundles</h3>
                            <span class="badge badge-primary">{{ count($bookingBundles) }}</span>
                        </div>
                        <div class="row">
                            @foreach($bookingBundles as $bundle)
                                <div class="col-12 col-md-6 col-lg-4 mb-16 d-flex">
                                    <div class="booking-card w-100">
                                        @if($bundle->thumbnail)
                                            <img src="{{ $bundle->thumbnail }}"
                                                 class="booking-card__img"
                                                 alt="{{ $bundle->title }}">
                                        @else
                                            <div class="booking-card__img d-flex-center bg-light"
                                                 style="font-size:36px;">🎁</div>
                                        @endif
                                        <div class="booking-card__body">
                                            <span class="badge badge-secondary mb-6 align-self-start"
                                                  style="font-size:10px;">Bundle</span>
                                            <div class="booking-card__title">{{ Str::limit($bundle->title, 50) }}</div>
                                            <p class="booking-card__desc">{{ Str::limit($bundle->description, 80) }}</p>
                                            <div class="booking-card__footer">
                                                <span style="font-size:14px; font-weight:700; color:#2563eb;">
                                                    {{ $bundle->price ? handlePrice($bundle->price) : 'Free' }}
                                                </span>
                                                <a href="{{ url('/booking-bundles/' . $bundle->slug) }}"
                                                   class="btn btn-sm btn-primary">View</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── COURSES ─────────────────────────────────── --}}
                @if(!empty($webinars) && $webinars->isNotEmpty())
                    <section id="section-webinars" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">📚</span>
                            <h3>Courses</h3>
                            <span class="badge badge-primary">{{ count($webinars) }}</span>
                        </div>
                        <div class="row">
                            @include('design_1.web.courses.components.cards.grids.index', [
                                'courses'           => $webinars,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── COURSE BUNDLES ──────────────────────────── --}}
                @if(!empty($bundles) && $bundles->isNotEmpty())
                    <section id="section-bundles" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">📦</span>
                            <h3>Course Bundles</h3>
                            <span class="badge badge-primary">{{ count($bundles) }}</span>
                        </div>
                        <div class="row">
                            @include('design_1.web.bundles.components.cards.grids.index', [
                                'bundles'           => $bundles,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── STORE PRODUCTS ──────────────────────────── --}}
                @if(!empty($products) && $products->isNotEmpty())
                    <section id="section-products" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">🛍️</span>
                            <h3>Store Products</h3>
                            <span class="badge badge-primary">{{ count($products) }}</span>
                        </div>
                        <div class="row">
                            @include('design_1.web.products.components.cards.grids.index', [
                                'products'          => $products,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── UPCOMING COURSES ────────────────────────── --}}
                @if(!empty($upcomingCourses) && $upcomingCourses->isNotEmpty())
                    <section id="section-upcoming_courses" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">🗓️</span>
                            <h3>Upcoming Courses</h3>
                            <span class="badge badge-primary">{{ count($upcomingCourses) }}</span>
                        </div>
                        <div class="row">
                            @include('design_1.web.upcoming_courses.components.cards.grids.index', [
                                'upcomingCourses'   => $upcomingCourses,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── BLOG POSTS ──────────────────────────────── --}}
                @if(!empty($posts) && $posts->isNotEmpty())
                    <section id="section-posts" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">📰</span>
                            <h3>Blog Posts</h3>
                            <span class="badge badge-primary">{{ count($posts) }}</span>
                        </div>
                        <div class="row">
                            @include('design_1.web.blog.components.cards.grids.index', [
                                'posts'             => $posts,
                                'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mb-16',
                            ])
                        </div>
                    </section>
                @endif

                {{-- ── ORGANIZATIONS ───────────────────────────── --}}
                @if(!empty($organizations) && count($organizations))
                    <section id="section-organizations" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">🏢</span>
                            <h3>Organizations</h3>
                            <span class="badge badge-primary">{{ count($organizations) }}</span>
                        </div>
                        <div class="row">
                            @foreach($organizations as $organ)
                                @include('design_1.web.search.includes.user_card', ['userCard' => $organ])
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── INSTRUCTORS ─────────────────────────────── --}}
                @if(!empty($instructors) && count($instructors))
                    <section id="section-instructors" class="adv-result-section mb-48">
                        <div class="result-section-heading">
                            <span style="font-size:22px;">👨‍🏫</span>
                            <h3>Instructors</h3>
                            <span class="badge badge-primary">{{ count($instructors) }}</span>
                        </div>
                        <div class="row">
                            @foreach($instructors as $instructor)
                                @include('design_1.web.search.includes.user_card', ['userCard' => $instructor])
                            @endforeach
                        </div>
                    </section>
                @endif

                {{-- ── EMPTY STATE ─────────────────────────────── --}}
                @php
                    $hasAnyResult = (!empty($bookings)       && $bookings->isNotEmpty())
                                || (!empty($bookingBundles)  && $bookingBundles->isNotEmpty())
                                || (!empty($webinars)        && $webinars->isNotEmpty())
                                || (!empty($bundles)         && $bundles->isNotEmpty())
                                || (!empty($products)        && $products->isNotEmpty())
                                || (!empty($upcomingCourses) && $upcomingCourses->isNotEmpty())
                                || (!empty($posts)           && $posts->isNotEmpty())
                                || (!empty($organizations)   && count($organizations))
                                || (!empty($instructors)     && count($instructors));
                @endphp

                @if(!$hasAnyResult)
                    <div class="text-center py-80">
                        <div style="font-size:56px; margin-bottom:16px;">🔍</div>
                        @if(!empty(request()->get('search')))
                            <h4 style="font-size:18px; font-weight:700; margin-bottom:8px;">No results found</h4>
                            <p class="text-muted" style="font-size:14px;">
                                Try different keywords or broaden your filters.
                            </p>
                        @else
                            <h4 style="font-size:18px; font-weight:700; margin-bottom:8px;">Start searching</h4>
                            <p class="text-muted" style="font-size:14px;">
                                Type in the search bar above to find courses, bookings, products and more.
                            </p>
                        @endif
                    </div>
                @endif

            </div>{{-- /right col --}}
        </div>{{-- /row --}}
    </div>{{-- /container --}}

    {{-- ═══════════════════════════════════════════════════════════
         PAGE SCRIPTS
    ═══════════════════════════════════════════════════════════ --}}
    <script>
    (function () {

        // ── Elements ──────────────────────────────────────────────────────────
        var filterForm   = document.getElementById('filterForm');
        var cityInput    = document.getElementById('sidebarCityInput');
        var clearCityBtn = document.getElementById('sidebarClearCity');
        var latField     = document.getElementById('filterLat');
        var lngField     = document.getElementById('filterLng');
        var cityNameFld  = document.getElementById('filterCityName');
        var ratingVal    = document.getElementById('filterRatingValue');
        var sortSelect   = document.getElementById('advSortSelect');
        var geoTimer;

        // ── Sort dropdown → reload with new sort param ────────────────────────
        if (sortSelect) {
            sortSelect.addEventListener('change', function () {
                var url = new URL(window.location.href);
                url.searchParams.set('sort', this.value);
                window.location.href = url.toString();
            });
        }

        // ── Type tabs (All / Courses / Bookings / …) ──────────────────────────
        document.querySelectorAll('.adv-type-tab').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var type = this.dataset.type;

                // Update active style
                document.querySelectorAll('.adv-type-tab').forEach(function (b) {
                    b.classList.remove('btn-primary');
                    b.classList.add('btn-outline-secondary');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary');

                if (type === 'all') {
                    document.querySelectorAll('.adv-result-section').forEach(function (s) {
                        s.style.display = '';
                    });
                } else {
                    document.querySelectorAll('.adv-result-section').forEach(function (s) {
                        s.style.display = 'none';
                    });
                    var target = document.getElementById('section-' + type);
                    if (target) target.style.display = '';
                }
            });
        });

        // ── Rating buttons: set hidden field + submit ─────────────────────────
        document.querySelectorAll('.js-rating-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (ratingVal) ratingVal.value = this.dataset.rating;
                if (filterForm) filterForm.submit();
            });
        });

        var clearRatingBtn = document.querySelector('.js-rating-clear');
        if (clearRatingBtn) {
            clearRatingBtn.addEventListener('click', function () {
                if (ratingVal) ratingVal.value = '';
                if (filterForm) filterForm.submit();
            });
        }

        // ── Category checkboxes: auto-submit on change ────────────────────────
        document.querySelectorAll('.js-cat-checkbox, .js-bcat-checkbox').forEach(function (cb) {
            cb.addEventListener('change', function () {
                if (filterForm) filterForm.submit();
            });
        });

        // ── Select All / Clear All / Toggle subcategories ─────────────────────
        var selectAllBtn = document.querySelector('.js-select-all-cats');
        var clearAllBtn  = document.querySelector('.js-clear-all-cats');
        var toggleBtn    = document.querySelector('.js-toggle-all-cats');

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.js-cat-checkbox, .js-bcat-checkbox').forEach(function (cb) {
                    cb.checked = true;
                });
                if (filterForm) filterForm.submit();
            });
        }

        if (clearAllBtn) {
            clearAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.js-cat-checkbox, .js-bcat-checkbox').forEach(function (cb) {
                    cb.checked = false;
                });
                if (filterForm) filterForm.submit();
            });
        }

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                document.querySelectorAll('.subcats-list').forEach(function (el) {
                    var isHidden = el.style.display === 'none' ||
                                   getComputedStyle(el).display === 'none';
                    el.style.display = isHidden ? 'block' : 'none';
                });
            });
        }

        // ── Auto-detect city from IP (only when no lat already set) ───────────
        @if(!request()->filled('lat'))
        fetch('https://ip-api.com/json/?fields=city,lat,lon')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.city && cityInput && !cityInput.value) {
                    cityInput.value     = data.city;
                    if (latField)    latField.value    = data.lat;
                    if (lngField)    lngField.value    = data.lon;
                    if (cityNameFld) cityNameFld.value = data.city;
                }
            })
            .catch(function () {});
        @endif

        // ── Geocode as user types city name (Nominatim) ───────────────────────
        if (cityInput) {
            cityInput.addEventListener('input', function () {
                var city = this.value.trim();
                clearTimeout(geoTimer);

                if (city.length < 2) {
                    if (latField)    latField.value    = '';
                    if (lngField)    lngField.value    = '';
                    if (cityNameFld) cityNameFld.value = '';
                    return;
                }

                geoTimer = setTimeout(function () {
                    fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q='
                          + encodeURIComponent(city))
                        .then(function (r) { return r.json(); })
                        .then(function (results) {
                            if (results && results.length > 0) {
                                if (latField)    latField.value    = results[0].lat;
                                if (lngField)    lngField.value    = results[0].lon;
                                if (cityNameFld) cityNameFld.value = city;
                            }
                        })
                        .catch(function () {});
                }, 500);
            });
        }

        // ── Clear city button ─────────────────────────────────────────────────
        if (clearCityBtn) {
            clearCityBtn.addEventListener('click', function () {
                if (cityInput)   cityInput.value    = '';
                if (latField)    latField.value     = '';
                if (lngField)    lngField.value     = '';
                if (cityNameFld) cityNameFld.value  = '';
            });
        }

    })();
    </script>

</main>
@endsection

@push('scripts_bottom')
    <script src="{{ getDesign1ScriptPath("search") }}"></script>
    <script src="{{ asset('js/advanced_search.js') }}"></script>
@endpush