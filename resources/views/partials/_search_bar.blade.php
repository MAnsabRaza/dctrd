{{--
    partials/_search_bar.blade.php
    ─────────────────────────────────────────────────────────────────────────
    Drop-in replacement for the simple search input.
    Includes:
      • Search text input with 🔍 icon
      • Collapsible category dropdown (courses + hierarchical booking categories)
      • Live suggestions panel (populated via AJAX)
      • Nearby / location fields (integrates with task 3.4 location picker)

    Usage:
      @include('partials._search_bar')

    Depends on:
      • public/js/advanced_search.js   (loaded at bottom of layout)
      • Bootstrap 4/5 (already in project)
      • jQuery (already in project)
--}}

<div id="advanced-search-wrapper" class="advanced-search-wrapper position-relative">

    <form action="{{ route('search') }}" method="get" id="advancedSearchForm" autocomplete="off">

        {{-- ── Main input row ─────────────────────────────────────────────── --}}
        <div class="search-input-group d-flex align-items-center bg-white rounded-12 border px-12 py-8">

            {{-- Search icon --}}
            <span class="search-icon mr-10 text-muted" style="font-size:18px; line-height:1; flex-shrink:0;">
                🔍
            </span>

            {{-- Text input --}}
            <input
                type="text"
                id="advSearchInput"
                name="search"
                class="form-control border-0 p-0 font-14"
                placeholder="{{ trans('home.slider_search_placeholder') }}"
                value="{{ request()->get('search', '') }}"
                style="box-shadow:none; min-width:0; flex:1;"
            />

            {{-- Toggle category dropdown --}}
            <button
                type="button"
                id="advSearchCategoryToggle"
                class="btn btn-link text-muted px-8 py-0 border-left ml-8"
                style="font-size:12px; white-space:nowrap; flex-shrink:0;"
                title="{{ trans('update.categories') }}"
            >
                <span id="advCategoryCount" class="mr-4"></span>
                <span class="category-caret">▾</span>
            </button>

            {{-- Submit --}}
            <button type="submit" class="btn btn-primary ml-8 px-20 py-8 font-14" style="flex-shrink:0;">
                {{ trans('public.search') }}
            </button>
        </div>

        {{-- ── Live suggestions dropdown ──────────────────────────────────── --}}
        <div id="advSearchSuggestions"
             class="adv-suggestions bg-white border rounded-8 shadow-sm position-absolute w-100 z-index-999 d-none"
             style="top:calc(100% + 4px); left:0; right:0;">
            {{-- Populated by JS --}}
        </div>

        {{-- ── Category dropdown panel ────────────────────────────────────── --}}
        <div id="advCategoryPanel"
             class="adv-category-panel bg-white border rounded-8 shadow-sm position-absolute w-100 z-index-998 d-none"
             style="top:calc(100% + 4px); left:0; right:0; max-height:70vh; overflow-y:auto;">

            <div class="p-16">

                {{-- Top controls --}}
                <div class="d-flex align-items-center justify-content-between mb-12 flex-wrap gap-8">
                    <span class="font-13 font-weight-600 text-dark">{{ trans('update.categories') }}</span>
                    <div class="d-flex gap-8" style="gap:6px;">
                        <button type="button" id="advSelectAll"   class="btn btn-xs btn-outline-secondary">✓ {{ trans('public.all') }}</button>
                        <button type="button" id="advSelectNone"  class="btn btn-xs btn-outline-secondary">✗ {{ trans('public.none') ?? 'None' }}</button>
                        <button type="button" id="advExpandAll"   class="btn btn-xs btn-outline-secondary">⊕ {{ trans('update.expand_all')   ?? 'Expand' }}</button>
                        <button type="button" id="advCollapseAll" class="btn btn-xs btn-outline-secondary">⊖ {{ trans('update.collapse_all') ?? 'Collapse' }}</button>
                    </div>
                </div>

                {{-- ── Content types (non-booking) ──────────────────────── --}}
                <div class="mb-4">
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="courses" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">📚 {{ trans('update.courses') }}</span>
                    </label>
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="bundles" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">📦 {{ trans('update.bundles') }}</span>
                    </label>
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="upcoming_courses" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">🗓️ {{ trans('update.upcoming_courses') }}</span>
                    </label>
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="products" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">🛍️ {{ trans('update.store_products') }}</span>
                    </label>
                </div>

                {{-- ── Booking categories (hierarchical from DB) ─────────── --}}
                @if(!empty($bookingCategories) && $bookingCategories->isNotEmpty())
                    <div class="border-top pt-12 mt-8">
                        <div class="font-12 font-weight-600 mb-8 text-uppercase opacity-60">
                            {{ trans('home.bookings') ?? 'Bookings & Services' }}
                        </div>

                        @foreach($bookingCategories as $rootCat)
                            <div class="adv-group mb-4" data-group-id="{{ $rootCat->id }}">
                                {{-- Parent row --}}
                                <div class="d-flex align-items-center">
                                    {{-- Expand toggle (only if has children) --}}
                                    @if($rootCat->children && $rootCat->children->isNotEmpty())
                                        <button type="button"
                                                class="btn btn-link p-0 mr-6 adv-toggle-btn"
                                                data-target="adv-children-{{ $rootCat->id }}"
                                                style="font-size:11px; line-height:1; color:inherit; text-decoration:none; width:16px;">
                                            <span class="adv-expand-icon">▶</span>
                                        </button>
                                    @else
                                        <span style="width:22px; display:inline-block;"></span>
                                    @endif

                                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer mb-0 flex-1">
                                        <input type="checkbox"
                                               name="booking_categories[]"
                                               value="{{ $rootCat->id }}"
                                               class="adv-cat-checkbox adv-parent-checkbox"
                                               data-group="{{ $rootCat->id }}"
                                               checked>
                                        <span class="font-13">{{ $rootCat->title }}</span>
                                        @if($rootCat->children && $rootCat->children->isNotEmpty())
                                            <span class="badge badge-light ml-auto font-10">{{ $rootCat->children->count() }}</span>
                                        @endif
                                    </label>
                                </div>

                                {{-- Children (collapsed by default) --}}
                                @if($rootCat->children && $rootCat->children->isNotEmpty())
                                    <div id="adv-children-{{ $rootCat->id }}"
                                         class="adv-children-panel pl-32"
                                         style="display:none;">
                                        @foreach($rootCat->children as $child)
                                            <label class="adv-cat-item d-flex align-items-center gap-8 py-3 cursor-pointer mb-0">
                                                <input type="checkbox"
                                                       name="booking_categories[]"
                                                       value="{{ $child->id }}"
                                                       class="adv-cat-checkbox adv-child-checkbox"
                                                       data-parent="{{ $rootCat->id }}"
                                                       checked>
                                                <span class="font-12 text-muted">{{ $child->title }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif

                {{-- ── Booking bundles, Instructors, Organizations ────────── --}}
                <div class="border-top pt-12 mt-8">
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="booking_bundles" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">🎁 {{ trans('home.booking_bundles') ?? 'Booking Bundles' }}</span>
                    </label>
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="instructors" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">👨‍🏫 {{ trans('home.instructors') }}</span>
                    </label>
                    <label class="adv-cat-item d-flex align-items-center gap-8 py-4 cursor-pointer">
                        <input type="checkbox" name="types[]" value="organizations" class="adv-cat-checkbox adv-top-checkbox" checked>
                        <span class="font-13">🏢 {{ trans('home.organizations') }}</span>
                    </label>
                </div>

                {{-- ── Nearby filter (task 3.4 integration) ──────────────── --}}
                <div class="border-top pt-12 mt-8">
                    <div class="font-12 font-weight-600 mb-8 text-uppercase opacity-60">
                        {{ trans('update.find_nearby') ?? 'Find Nearby' }}
                    </div>
                    <div class="row g-8">
                        <div class="col-5">
                            <input type="number"
                                   id="advRadiusKm"
                                   name="radius_km"
                                   value="{{ request()->get('radius_km', 50) }}"
                                   class="form-control form-control-sm"
                                   min="1"
                                   max="500"
                                   placeholder="{{ trans('update.radius_km') ?? 'Radius (km)' }}">
                        </div>
                        <div class="col-7 position-relative">
                            <input type="text"
                                   id="advNearbyCity"
                                   class="form-control form-control-sm"
                                   placeholder="{{ trans('update.from_city') ?? 'From city...' }}"
                                   autocomplete="off">
                            <button type="button" id="advClearCity" class="btn btn-link p-0 position-absolute"
                                    style="right:10px; top:50%; transform:translateY(-50%); display:none; font-size:14px;"
                                    title="{{ trans('public.clear') }}">×</button>
                        </div>
                    </div>
                    {{-- Hidden lat/lng fields populated by geocoding --}}
                    <input type="hidden" id="advLat" name="lat" value="{{ request()->get('lat', '') }}">
                    <input type="hidden" id="advLng" name="lng" value="{{ request()->get('lng', '') }}">
                </div>

                {{-- ── Search button (inside panel too for convenience) ──── --}}
                <div class="mt-16">
                    <button type="submit" class="btn btn-primary btn-block font-14">
                        {{ trans('public.search') }}
                    </button>
                </div>
            </div>{{-- /p-16 --}}
        </div>{{-- /adv-category-panel --}}

    </form>
</div>{{-- /advanced-search-wrapper --}}

{{-- Inline micro-styles for the search bar (no extra CSS file needed) --}}
<style>
.advanced-search-wrapper { position: relative; }

.adv-suggestions,
.adv-category-panel {
    z-index: 1050;
}

/* Suggestion rows */
.adv-suggestion-row {
    display: flex;
    align-items: center;
    padding: 8px 14px;
    cursor: pointer;
    gap: 10px;
    border-bottom: 1px solid rgba(0,0,0,.05);
    transition: background .15s;
}
.adv-suggestion-row:hover { background: #f8f9ff; }
.adv-suggestion-row:last-child { border-bottom: none; }

.adv-suggestion-icon { font-size: 18px; flex-shrink: 0; width: 28px; text-align: center; }
.adv-suggestion-body { flex: 1; min-width: 0; }
.adv-suggestion-title { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.adv-suggestion-meta  { font-size: 11px; color: #888; }
.adv-suggestion-price { font-size: 12px; font-weight: 600; color: #2563eb; flex-shrink: 0; }

.adv-see-all {
    display: block;
    padding: 10px 14px;
    text-align: center;
    font-size: 13px;
    color: #2563eb;
    text-decoration: none;
    border-top: 1px solid rgba(0,0,0,.08);
}
.adv-see-all:hover { background: #f0f4ff; }

/* Category panel */
.adv-cat-item { user-select: none; line-height: 1.3; }
.adv-cat-item:hover { color: #2563eb; }
.adv-parent-checkbox:indeterminate + span { opacity: .7; }
.adv-expand-icon { display: inline-block; transition: transform .2s; font-size: 10px; }
.adv-expand-icon.open { transform: rotate(90deg); }

/* Count badge in toggle button */
#advCategoryCount { font-size: 11px; background: #e8eaf6; color: #3f51b5; border-radius: 10px; padding: 1px 6px; }

.cursor-pointer { cursor: pointer; }
</style>