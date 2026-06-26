{{--
    FILE: resources/views/partials/_search_bar.blade.php  —  FIXED VERSION

    FIXES APPLIED:
    ──────────────────────────────────────────────────────────────────────────
    FIX-I  (TP6/TP8): advCity hidden field city value sync — ab form submit par
           advNearbyCity aur advCity dono sync hote hain properly.

    FIX-II (TP4): types[] checkboxes ab form ke andar hain aur SearchController
           tak properly pohanchte hain.

    FIX-III (TP5): AJAX suggestions URL correct — /search/suggestions — aur
            debounce properly work karta hai.

    FIX-IV (TP6): GPS button se detect hone ke baad advCity (hidden) bhi update
           hota hai not just advNearbyCity (visible text).
    ──────────────────────────────────────────────────────────────────────────

    Usage:  @include('partials._search_bar')

    Requires:
      • jQuery (already in project)
      • public/js/advanced_search.js  (loaded at bottom of layout)
      • Bootstrap 4 (already in project)

    $bookingCategories must be available in view.
    Add to AppServiceProvider boot() if needed:
      View::composer('partials._search_bar', fn($v) =>
          $v->with('bookingCategories', BookingCategory::whereNull('parent_id')
              ->where('status', true)
              ->with(['children' => fn($q) => $q->where('status', true)])
              ->get())
      );
--}}

<div id="advanced-search-wrapper" class="advanced-search-wrapper position-relative">

    <form action="{{ route('search') }}" method="get" id="advancedSearchForm" autocomplete="off">

        {{-- ══════════════════════════════════════════════════════
             MAIN INPUT ROW
        ══════════════════════════════════════════════════════ --}}
        <div class="search-input-group d-flex align-items-center bg-white rounded-12 border px-12 py-8"
             style="border-color:#e5e7eb !important;">

            <span class="mr-10 text-muted" style="font-size:18px;line-height:1;flex-shrink:0;user-select:none;">
                🔍
            </span>

            <input
                type="text"
                id="advSearchInput"
                name="search"
                class="form-control border-0 p-0 font-14"
                placeholder="{{ trans('home.slider_search_placeholder') }}"
                value="{{ request()->get('search', '') }}"
                style="box-shadow:none;min-width:0;flex:1;"
            >

            {{-- Category toggle --}}
            <button type="button"
                    id="advSearchCategoryToggle"
                    class="btn btn-link text-muted px-8 py-0 border-left ml-8"
                    style="font-size:12px;white-space:nowrap;flex-shrink:0;text-decoration:none;"
                    title="{{ trans('update.categories') }}">
                <span id="advCategoryCount"
                      class="mr-4"
                      style="font-size:11px;background:#e8eaf6;color:#3f51b5;border-radius:10px;padding:1px 6px;"></span>
                <span class="category-caret">▾</span>
            </button>

            <button type="submit"
                    class="btn btn-primary ml-8 px-20 py-8 font-14"
                    style="flex-shrink:0;border-radius:8px;">
                {{ trans('public.search') }}
            </button>
        </div>

        {{-- ══════════════════════════════════════════════════════
             LIVE SUGGESTIONS  (TP5)
        ══════════════════════════════════════════════════════ --}}
        <div id="advSearchSuggestions"
             class="adv-suggestions bg-white border rounded-8 shadow-sm position-absolute d-none"
             style="top:calc(100% + 4px);left:0;right:0;z-index:1060;">
        </div>

        {{-- ══════════════════════════════════════════════════════
             CATEGORY + NEARBY PANEL  (TP4, TP6)
        ══════════════════════════════════════════════════════ --}}
        <div id="advCategoryPanel"
             class="adv-category-panel bg-white border rounded-8 shadow-sm position-absolute d-none"
             style="top:calc(100% + 4px);left:0;right:0;z-index:1055;max-height:70vh;overflow-y:auto;">

            <div class="p-16">

                {{-- Panel header --}}
                <div class="d-flex align-items-center justify-content-between mb-12 flex-wrap"
                     style="gap:6px;">
                    <span class="font-13 font-weight-700 text-dark">{{ trans('update.categories') }}</span>
                    <div class="d-flex" style="gap:4px;">
                        <button type="button" id="advSelectAll"
                                class="btn btn-xs btn-outline-secondary">
                            ✓ {{ trans('public.all') }}
                        </button>
                        <button type="button" id="advSelectNone"
                                class="btn btn-xs btn-outline-secondary">
                            ✗ {{ trans('public.none') ?? 'None' }}
                        </button>
                        <button type="button" id="advExpandAll"
                                class="btn btn-xs btn-outline-secondary">
                            ⊕ {{ trans('update.expand_all') ?? 'Expand' }}
                        </button>
                        <button type="button" id="advCollapseAll"
                                class="btn btn-xs btn-outline-secondary">
                            ⊖ {{ trans('update.collapse_all') ?? 'Collapse' }}
                        </button>
                    </div>
                </div>

                {{-- ── FIX-II: Content type checkboxes — name="types[]" properly in form ── --}}
                <div class="mb-8">
                    <div class="adv-section-label">{{ trans('update.content_types') ?? 'Content Types' }}</div>

                    @foreach([
                        ['value' => 'courses',          'icon' => '📚', 'key' => 'update.courses'],
                        ['value' => 'bundles',          'icon' => '📦', 'key' => 'update.bundles'],
                        ['value' => 'upcoming_courses', 'icon' => '🗓️', 'key' => 'update.upcoming_courses'],
                        ['value' => 'products',         'icon' => '🛍️', 'key' => 'update.store_products'],
                        ['value' => 'bookings',         'icon' => '🏨', 'key' => 'home.bookings'],
                        ['value' => 'booking_bundles',  'icon' => '🎁', 'key' => 'home.booking_bundles'],
                        ['value' => 'instructors',      'icon' => '👨‍🏫','key' => 'home.instructors'],
                        ['value' => 'organizations',    'icon' => '🏢', 'key' => 'home.organizations'],
                        ['value' => 'posts',            'icon' => '📰', 'key' => 'update.blog_posts'],
                    ] as $ct)
                        <label class="adv-cat-item d-flex align-items-center py-4 cursor-pointer mb-0"
                               style="gap:8px;">
                            <input type="checkbox"
                                   name="types[]"
                                   value="{{ $ct['value'] }}"
                                   class="adv-cat-checkbox adv-top-checkbox"
                                   @if(empty(request()->get('types')) || in_array($ct['value'], (array) request()->get('types', []))) checked @endif>
                            <span class="font-13">
                                {{ $ct['icon'] }} {{ trans($ct['key']) ?? $ct['value'] }}
                            </span>
                        </label>
                    @endforeach
                </div>

                {{-- ── Booking categories (hierarchical from DB) ─────────── --}}
                @if(!empty($bookingCategories) && $bookingCategories->isNotEmpty())
                    <div class="border-top pt-12 mt-4">
                        <div class="adv-section-label">
                            {{ trans('home.bookings') ?? 'Bookings & Services' }}
                        </div>

                        @foreach($bookingCategories as $rootCat)
                            <div class="adv-group mb-4" data-group-id="{{ $rootCat->id }}">

                                <div class="d-flex align-items-center" style="gap:4px;">

                                    @if($rootCat->children && $rootCat->children->isNotEmpty())
                                        <button type="button"
                                                class="btn btn-link p-0 adv-toggle-btn"
                                                data-target="adv-children-{{ $rootCat->id }}"
                                                style="font-size:10px;line-height:1;color:inherit;
                                                       text-decoration:none;width:18px;flex-shrink:0;">
                                            <span class="adv-expand-icon">▶</span>
                                        </button>
                                    @else
                                        <span style="width:22px;display:inline-block;"></span>
                                    @endif

                                    <label class="adv-cat-item d-flex align-items-center py-4 cursor-pointer mb-0 flex-1"
                                           style="gap:8px;">
                                        <input type="checkbox"
                                               name="booking_categories[]"
                                               value="{{ $rootCat->id }}"
                                               class="adv-cat-checkbox adv-parent-checkbox"
                                               data-group="{{ $rootCat->id }}"
                                               checked>
                                        <span class="font-13">{{ $rootCat->title }}</span>
                                        @if($rootCat->children && $rootCat->children->isNotEmpty())
                                            <span class="badge badge-light ml-auto"
                                                  style="font-size:10px;">
                                                {{ $rootCat->children->count() }}
                                            </span>
                                        @endif
                                    </label>
                                </div>

                                @if($rootCat->children && $rootCat->children->isNotEmpty())
                                    <div id="adv-children-{{ $rootCat->id }}"
                                         class="adv-children-panel pl-32"
                                         style="display:none;">
                                        @foreach($rootCat->children as $child)
                                            <label class="adv-cat-item d-flex align-items-center py-3 cursor-pointer mb-0"
                                                   style="gap:8px;">
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

                {{-- ── Nearby / Location  (TP6, TP8) ───────────────────── --}}
                <div class="border-top pt-12 mt-8">
                    <div class="adv-section-label">📍 {{ trans('update.find_nearby') ?? 'Find Nearby' }}</div>

                    {{-- GPS button --}}
                    <button type="button"
                            id="advUseMyLocation"
                            class="btn btn-sm btn-outline-secondary btn-block mb-10"
                            style="font-size:12px;">
                        📍 {{ trans('update.use_my_location') ?? 'Use my location' }}
                    </button>

                    <div class="d-flex align-items-start mb-8" style="gap:8px;">

                        {{-- Radius --}}
                        <div style="width:100px;flex-shrink:0;">
                            <input type="number"
                                   id="advRadiusKm"
                                   name="radius_km"
                                   value="{{ request()->get('radius_km', 50) }}"
                                   min="1" max="500"
                                   class="form-control form-control-sm text-center"
                                   placeholder="50">
                            <div class="text-center text-muted mt-1" style="font-size:10px;">km radius</div>
                        </div>

                        {{-- City --}}
                        <div class="flex-1 position-relative" style="min-width:0;">
                            <input type="text"
                                   id="advNearbyCity"
                                   class="form-control form-control-sm"
                                   placeholder="{{ trans('update.from_city') ?? 'City or address…' }}"
                                   value="{{ request()->get('city', '') }}"
                                   autocomplete="off"
                                   style="padding-right:26px;">
                            <button type="button"
                                    id="advClearCity"
                                    class="btn btn-link p-0 position-absolute"
                                    style="right:6px;top:50%;transform:translateY(-50%);
                                           font-size:16px;color:#9ca3af;line-height:1;
                                           display:{{ request()->filled('lat') ? 'block' : 'none' }};"
                                    title="{{ trans('public.clear') }}">×</button>
                        </div>
                    </div>

                    {{--
                        FIX-I / FIX-IV: lat/lng/city hidden fields
                        advCity syncs with advNearbyCity on every change via JS
                        so form submission always carries city name properly.
                    --}}
                    <input type="hidden" id="advLat"  name="lat"  value="{{ request()->get('lat', '') }}">
                    <input type="hidden" id="advLng"  name="lng"  value="{{ request()->get('lng', '') }}">
                    <input type="hidden" id="advCity" name="city" value="{{ request()->get('city', '') }}">

                    @if(request()->filled('lat'))
                        <a href="{{ request()->fullUrlWithQuery(['lat'=>'','lng'=>'','city'=>'','radius_km'=>'']) }}"
                           class="btn btn-sm btn-link btn-block text-muted mt-4"
                           style="font-size:11px;">
                            ✕ {{ trans('update.clear_location') ?? 'Clear location' }}
                        </a>
                    @endif
                </div>

                {{-- Panel submit --}}
                <div class="mt-16">
                    <button type="submit" class="btn btn-primary btn-block font-14" style="border-radius:8px;">
                        🔍 {{ trans('public.search') }}
                    </button>
                </div>

            </div>{{-- /p-16 --}}
        </div>{{-- /adv-category-panel --}}

    </form>
</div>{{-- /advanced-search-wrapper --}}

{{-- Scoped styles --}}
<style>
.adv-suggestions,
.adv-category-panel { z-index: 1050; }

.adv-suggestion-row {
    display: flex; align-items: center;
    padding: 8px 14px; cursor: pointer; gap: 10px;
    border-bottom: 1px solid rgba(0,0,0,.05); transition: background .15s;
}
.adv-suggestion-row:hover,
.adv-suggestion-row.adv-active { background: #f0f4ff; }
.adv-suggestion-row:last-child { border-bottom: none; }

.adv-suggestion-icon  { font-size: 18px; flex-shrink: 0; width: 28px; text-align: center; }
.adv-suggestion-body  { flex: 1; min-width: 0; }
.adv-suggestion-title { font-size: 13px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.adv-suggestion-meta  { font-size: 11px; color: #9ca3af; }
.adv-suggestion-price { font-size: 12px; font-weight: 600; color: #2563eb; flex-shrink: 0; }

.adv-see-all {
    display: block; padding: 10px 14px; text-align: center;
    font-size: 13px; color: #2563eb; text-decoration: none;
    border-top: 1px solid rgba(0,0,0,.08); transition: background .15s;
}
.adv-see-all:hover { background: #f0f4ff; text-decoration: none; }

.adv-section-label {
    font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: .05em; color: #9ca3af; margin-bottom: 8px;
}

.adv-cat-item { user-select: none; line-height: 1.4; }
.adv-cat-item:hover span { color: #2563eb; }

.adv-expand-icon { display: inline-block; transition: transform .2s; font-size: 10px; }
.adv-expand-icon.open { transform: rotate(90deg); }

.adv-parent-checkbox:indeterminate + span { opacity: .65; }

#advCategoryCount {
    font-size: 11px; background: #e8eaf6; color: #3f51b5;
    border-radius: 10px; padding: 1px 6px;
}

.cursor-pointer { cursor: pointer; }
.rounded-8  { border-radius: 8px  !important; }
.rounded-12 { border-radius: 12px !important; }
</style>