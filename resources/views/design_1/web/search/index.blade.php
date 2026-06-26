@extends("design_1.web.layouts.app")

@push("scripts_top")
    {{--
        ROOT CAUSE (confirmed via app.blade.php layout):
        Layout's <body> bottom loads app.js, jquery.toast.min.js, and
        main.min.js — all of which need jQuery ($) to already exist.
        Previously, jQuery was being supplied by wrunner-jquery.js, which
        we removed because it was crashing ("$ is not defined"). Removing
        it without replacing it broke jQuery site-wide, not just on this
        page — app.js / jquery.toast.min.js / search.min.js / main.min.js
        all started throwing "jQuery is not defined".

        FIX: load a clean jQuery CDN here, inside scripts_top, which the
        layout renders in <head> — i.e. BEFORE app.js, jquery.toast.min.js,
        and every other script in <body>. This guarantees jQuery exists
        before anything that needs it runs.
    --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
@endpush

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
         PAGE SCRIPTS (sort, type tabs, sidebar geocoding for THIS page)
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

        // ── Geocode as user types city name (Nominatim) ───────────────────────
        // NOTE: IP-based auto-detect via ip-api.com removed — that endpoint is
        // HTTPS-blocked on the free plan (403 Forbidden) and was failing silently.
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
    {{--
        FIX: Theme ki apni built-in script (getDesign1ScriptPath("search"),
        compiled se /assets/design_1/js/parts/search.js) jQuery REQUIRE karti
        hai — uske andar (function ($) {...})(jQuery) hai. Yeh hum edit nahi
        kar sakte (compiled/minified asset hai).

        jQuery ab @push("scripts_top") mein, layout ke <head> mein, sabse
        pehle load ho rahi hai (oopar dekhein) — isi liye yahan dobara load
        karne ki zaroorat nahi.

        Pehle humne wrunner-jquery.js hata di thi kyunke wo crash kar rahi thi
        ("$ is not defined"), lekin uski jaga koi jQuery load nahi ki thi —
        is liye theme ki search.js bhi crash ho rahi thi ("jQuery is not
        defined"), jo aage page ka baqi JS execution bhi tor deti thi.
    --}}
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>

    <script src="{{ getDesign1ScriptPath("search") }}"></script>

    {{-- ═══════════════════════════════════════════════════════════
         advanced_search.js INLINE — vanilla JS, no jQuery required.
         Separate file 404 de raha tha, is liye seedha yahan daal diya.
         Pure JavaScript hone ki wajah se ab is page ko kisi jQuery
         library ke load hone ka intezar nahi karna — koi dependency
         crash nahi karegi.
    ═══════════════════════════════════════════════════════════ --}}
    <script>
    (function () {
        'use strict';

        /* ═══════════════════════════════════════════════════════════
           1. ELEMENTS + HELPERS
        ═══════════════════════════════════════════════════════════ */

        var wrapper        = document.getElementById('advanced-search-wrapper');
        var categoryPanel  = document.getElementById('advCategoryPanel');
        var suggestionsBox = document.getElementById('advSearchSuggestions');
        var input          = document.getElementById('advSearchInput');
        var toggle         = document.getElementById('advSearchCategoryToggle');
        var form           = document.getElementById('advancedSearchForm');

        function openCategoryPanel() {
            if (!categoryPanel) return;
            categoryPanel.classList.remove('d-none');
            if (suggestionsBox) suggestionsBox.classList.add('d-none');
            var caret = toggle ? toggle.querySelector('.category-caret') : null;
            if (caret) caret.textContent = '▴';
        }

        function closeCategoryPanel() {
            if (!categoryPanel) return;
            categoryPanel.classList.add('d-none');
            var caret = toggle ? toggle.querySelector('.category-caret') : null;
            if (caret) caret.textContent = '▾';
        }

        function hideSuggestions() {
            if (!suggestionsBox) return;
            suggestionsBox.classList.add('d-none');
            suggestionsBox.innerHTML = '';
            activeIdx = -1;
        }

        function escHtml(str) {
            return String(str || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');
        }

        /* ═══════════════════════════════════════════════════════════
           2. DROPDOWN OPEN / CLOSE
        ═══════════════════════════════════════════════════════════ */

        if (toggle) {
            toggle.addEventListener('click', function (e) {
                e.stopPropagation();
                if (categoryPanel && categoryPanel.classList.contains('d-none')) {
                    openCategoryPanel();
                } else {
                    closeCategoryPanel();
                }
            });
        }

        if (input) {
            input.addEventListener('focus', function () {
                if (this.value.trim().length < 2) {
                    openCategoryPanel();
                }
            });
        }

        document.addEventListener('click', function (e) {
            if (!wrapper || !wrapper.contains(e.target)) {
                closeCategoryPanel();
                hideSuggestions();
            }
        });

        if (categoryPanel) {
            categoryPanel.addEventListener('click', function (e) { e.stopPropagation(); });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeCategoryPanel();
                hideSuggestions();
            }
        });

        /* ═══════════════════════════════════════════════════════════
           3. HIERARCHICAL CHECKBOXES
        ═══════════════════════════════════════════════════════════ */

        function syncParentState(parentEl) {
            if (!parentEl) return;
            var groupId  = parentEl.dataset.group;
            var children = document.querySelectorAll('.adv-child-checkbox[data-parent="' + groupId + '"]');
            if (!children.length) return;

            var total   = children.length;
            var checked = 0;
            children.forEach(function (c) { if (c.checked) checked++; });

            if (checked === 0) {
                parentEl.checked = false;
                parentEl.indeterminate = false;
            } else if (checked === total) {
                parentEl.checked = true;
                parentEl.indeterminate = false;
            } else {
                parentEl.checked = false;
                parentEl.indeterminate = true;
            }
            updateCategoryCount();
        }

        document.addEventListener('change', function (e) {
            if (e.target.matches('.adv-parent-checkbox')) {
                var groupId   = e.target.dataset.group;
                var isChecked = e.target.checked;
                document.querySelectorAll('.adv-child-checkbox[data-parent="' + groupId + '"]')
                    .forEach(function (c) {
                        c.checked = isChecked;
                        c.indeterminate = false;
                    });
                updateCategoryCount();
            }

            if (e.target.matches('.adv-child-checkbox')) {
                var parentEl = document.querySelector('.adv-parent-checkbox[data-group="' + e.target.dataset.parent + '"]');
                syncParentState(parentEl);
            }

            if (e.target.matches('.adv-top-checkbox')) {
                updateCategoryCount();
            }
        });

        function updateCategoryCount() {
            var all     = document.querySelectorAll('.adv-cat-checkbox');
            var total   = all.length;
            var checked = document.querySelectorAll('.adv-cat-checkbox:checked').length;
            var badge   = document.getElementById('advCategoryCount');
            if (!badge) return;

            if (!checked || checked === total) {
                badge.textContent = '';
            } else {
                badge.textContent = checked + '/' + total;
            }
        }

        document.addEventListener('click', function (e) {
            var toggleBtn = e.target.closest('.adv-toggle-btn');
            if (!toggleBtn) return;
            e.preventDefault();

            var target = document.getElementById(toggleBtn.dataset.target);
            var icon   = toggleBtn.querySelector('.adv-expand-icon');
            if (!target) return;

            var isVisible = target.style.display !== 'none' &&
                             getComputedStyle(target).display !== 'none';

            if (isVisible) {
                target.style.display = 'none';
                if (icon) icon.classList.remove('open');
            } else {
                target.style.display = 'block';
                if (icon) icon.classList.add('open');
            }
        });

        /* ═══════════════════════════════════════════════════════════
           4. SELECT ALL / NONE / EXPAND ALL / COLLAPSE ALL
        ═══════════════════════════════════════════════════════════ */

        var selectAllBtn   = document.getElementById('advSelectAll');
        var selectNoneBtn  = document.getElementById('advSelectNone');
        var expandAllBtn   = document.getElementById('advExpandAll');
        var collapseAllBtn = document.getElementById('advCollapseAll');

        if (selectAllBtn) {
            selectAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.adv-cat-checkbox').forEach(function (cb) {
                    cb.checked = true;
                    cb.indeterminate = false;
                });
                updateCategoryCount();
            });
        }

        if (selectNoneBtn) {
            selectNoneBtn.addEventListener('click', function () {
                document.querySelectorAll('.adv-cat-checkbox').forEach(function (cb) {
                    cb.checked = false;
                    cb.indeterminate = false;
                });
                updateCategoryCount();
            });
        }

        if (expandAllBtn) {
            expandAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.adv-children-panel').forEach(function (el) {
                    el.style.display = 'block';
                });
                document.querySelectorAll('.adv-expand-icon').forEach(function (el) {
                    el.classList.add('open');
                });
            });
        }

        if (collapseAllBtn) {
            collapseAllBtn.addEventListener('click', function () {
                document.querySelectorAll('.adv-children-panel').forEach(function (el) {
                    el.style.display = 'none';
                });
                document.querySelectorAll('.adv-expand-icon').forEach(function (el) {
                    el.classList.remove('open');
                });
            });
        }

        updateCategoryCount();

        /* ═══════════════════════════════════════════════════════════
           5. LIVE SUGGESTIONS  (fetch, 300ms debounce, keyboard nav)
        ═══════════════════════════════════════════════════════════ */

        var suggestTimer;
        var activeIdx = -1;

        var TYPE_ICONS = {
            booking:         '🏨',
            course:          '📚',
            webinar:         '📚',
            bundle:          '📦',
            product:         '🛍️',
            upcoming_course: '🗓️',
            booking_bundle:  '🎁',
            instructor:      '👨‍🏫',
            organization:    '🏢',
            post:            '📰',
        };

        if (input) {
            input.addEventListener('keyup', function (e) {
                if (['ArrowUp', 'ArrowDown', 'Enter', 'Escape', 'Tab'].indexOf(e.key) !== -1) return;

                var query = this.value.trim();
                closeCategoryPanel();
                clearTimeout(suggestTimer);

                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }

                suggestTimer = setTimeout(function () {
                    fetch('/search/suggestions?q=' + encodeURIComponent(query))
                        .then(function (r) {
                            if (!r.ok) throw new Error('Request failed');
                            return r.json();
                        })
                        .then(function (response) {
                            renderSuggestions(response.suggestions || [], query);
                        })
                        .catch(function () {
                            hideSuggestions();
                        });
                }, 300);
            });
        }

        function renderSuggestions(suggestions, query) {
            if (!suggestionsBox) return;
            suggestionsBox.innerHTML = '';
            activeIdx = -1;

            if (!suggestions.length) {
                hideSuggestions();
                return;
            }

            suggestions.forEach(function (s) {
                var icon  = TYPE_ICONS[s.type] || '🔍';
                var price = s.price
                    ? '<span class="adv-suggestion-price">' + escHtml(s.price) + '</span>'
                    : '';

                var a = document.createElement('a');
                a.href = s.url || '#';
                a.className = 'adv-suggestion-row text-decoration-none text-dark';
                a.innerHTML =
                    '<span class="adv-suggestion-icon">' + icon + '</span>' +
                    '<span class="adv-suggestion-body">' +
                        '<div class="adv-suggestion-title">' + escHtml(s.title || '') + '</div>' +
                        '<div class="adv-suggestion-meta">' + escHtml(s.type || '') + '</div>' +
                    '</span>' + price;

                suggestionsBox.appendChild(a);
            });

            var seeAll = document.createElement('a');
            seeAll.href = '/search?search=' + encodeURIComponent(query);
            seeAll.className = 'adv-see-all';
            seeAll.innerHTML = 'See all results for "<strong>' + escHtml(query) + '</strong>" →';
            suggestionsBox.appendChild(seeAll);

            suggestionsBox.classList.remove('d-none');
        }

        if (input) {
            input.addEventListener('keydown', function (e) {
                if (!suggestionsBox) return;
                var rows = suggestionsBox.querySelectorAll('.adv-suggestion-row');
                if (!rows.length) return;

                var rowsArr = Array.prototype.slice.call(rows);
                var activeRow = suggestionsBox.querySelector('.adv-suggestion-row.adv-active');
                var activeIndex = activeRow ? rowsArr.indexOf(activeRow) : -1;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (activeRow) activeRow.classList.remove('adv-active');
                    var nextIndex = (activeIndex + 1 < rowsArr.length) ? activeIndex + 1 : 0;
                    rowsArr[nextIndex].classList.add('adv-active');
                    activeIdx = nextIndex;
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (activeRow) activeRow.classList.remove('adv-active');
                    var prevIndex = (activeIndex - 1 >= 0) ? activeIndex - 1 : rowsArr.length - 1;
                    rowsArr[prevIndex].classList.add('adv-active');
                    activeIdx = prevIndex;
                } else if (e.key === 'Enter') {
                    if (activeRow) {
                        e.preventDefault();
                        window.location.href = activeRow.getAttribute('href');
                    }
                }
            });
        }

        /* ═══════════════════════════════════════════════════════════
           6. NEARBY / CITY GEOCODING + GPS
           NOTE: ip-api.com auto-detect REMOVED — that's an HTTP-only
           free-tier endpoint and returns 403 Forbidden on HTTPS pages.
           City detection now relies on the GPS button (reverse geocode
           via Nominatim) or manual typing (forward geocode via Nominatim).
        ═══════════════════════════════════════════════════════════ */

        var cityInput  = document.getElementById('advNearbyCity');
        var clearCity  = document.getElementById('advClearCity');
        var latField   = document.getElementById('advLat');
        var lngField   = document.getElementById('advLng');
        var cityHidden = document.getElementById('advCity');
        var geocodeTimer;

        function setLatLng(lat, lng, cityName) {
            if (latField) latField.value = parseFloat(lat).toFixed(6);
            if (lngField) lngField.value = parseFloat(lng).toFixed(6);
            if (cityName && cityHidden) cityHidden.value = cityName;
            if (clearCity) clearCity.style.display = 'block';
        }

        function clearLatLng() {
            if (latField) latField.value = '';
            if (lngField) lngField.value = '';
            if (cityHidden) cityHidden.value = '';
            if (clearCity) clearCity.style.display = 'none';
        }

        var useMyLocationBtn = document.getElementById('advUseMyLocation');
        if (useMyLocationBtn) {
            useMyLocationBtn.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Geolocation is not supported by your browser.');
                    return;
                }
                var btn = this;
                btn.disabled = true;
                btn.textContent = 'Detecting…';

                navigator.geolocation.getCurrentPosition(
                    function (pos) {
                        var lat = pos.coords.latitude;
                        var lng = pos.coords.longitude;

                        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json')
                            .then(function (r) {
                                if (!r.ok) throw new Error('Reverse geocode failed');
                                return r.json();
                            })
                            .then(function (data) {
                                var city = (data && data.address)
                                    ? (data.address.city || data.address.town || data.address.village || '')
                                    : '';
                                if (city) {
                                    if (cityInput) cityInput.value = city;
                                    setLatLng(lat, lng, city);
                                } else {
                                    setLatLng(lat, lng, '');
                                }
                                btn.disabled = false;
                                btn.innerHTML = '✓ Location detected';
                            })
                            .catch(function () {
                                setLatLng(lat, lng, '');
                                btn.disabled = false;
                                btn.innerHTML = '✓ Location detected';
                            });
                    },
                    function (err) {
                        btn.disabled = false;
                        btn.innerHTML = '📍 Use my location';
                        console.warn('Geolocation error:', err.message);
                    }
                );
            });
        }

        if (cityInput) {
            cityInput.addEventListener('input', function () {
                var city = this.value.trim();
                clearTimeout(geocodeTimer);
                if (clearCity) clearCity.style.display = city.length > 0 ? 'block' : 'none';
                if (cityHidden) cityHidden.value = city;

                if (city.length < 2) {
                    clearLatLng();
                    return;
                }

                geocodeTimer = setTimeout(function () {
                    fetch('https://nominatim.openstreetmap.org/search?format=json&limit=1&q=' + encodeURIComponent(city))
                        .then(function (r) { return r.json(); })
                        .then(function (results) {
                            if (results && results[0]) {
                                setLatLng(results[0].lat, results[0].lon, city);
                            }
                        })
                        .catch(function () {});
                }, 500);
            });
        }

        if (clearCity) {
            clearCity.addEventListener('click', function () {
                if (cityInput) cityInput.value = '';
                clearLatLng();
            });
        }

        if (latField && latField.value) {
            if (clearCity) clearCity.style.display = 'block';
        }

        /* ═══════════════════════════════════════════════════════════
           7. FORM SUBMIT — strip empty lat/lng for clean URL
        ═══════════════════════════════════════════════════════════ */

        if (form) {
            form.addEventListener('submit', function () {
                var allTypes = form.querySelectorAll('.adv-top-checkbox');
                var checkedTypes = Array.prototype.filter.call(allTypes, function (c) { return c.checked; });

                if (checkedTypes.length === 0 || checkedTypes.length === allTypes.length) {
                    allTypes.forEach(function (c) { c.disabled = true; });
                }

                var allBCats = form.querySelectorAll('.adv-cat-checkbox:not(.adv-top-checkbox)');
                var checkedBCats = Array.prototype.filter.call(allBCats, function (c) { return c.checked; });
                if (checkedBCats.length === 0 || checkedBCats.length === allBCats.length) {
                    allBCats.forEach(function (c) { c.disabled = true; });
                }

                if (latField && !latField.value) latField.disabled = true;
                if (lngField && !lngField.value) lngField.disabled = true;
                if (cityHidden && !cityHidden.value) cityHidden.disabled = true;
            });
        }

        window.addEventListener('pageshow', function () {
            var formInputs = document.querySelectorAll('#advancedSearchForm input');
            formInputs.forEach(function (el) { el.disabled = false; });
        });

        /* ═══════════════════════════════════════════════════════════
           8. RESULTS PAGE INTERACTIONS
           NOTE: sort / type-tab / price / rating / category-checkbox /
           sidebar-city handlers for THIS results page already live in
           the inline <script> block inside @section('content') above.
           They are not duplicated here to avoid double-binding the same
           elements twice.
        ═══════════════════════════════════════════════════════════ */

    })();
    </script>
@endpush