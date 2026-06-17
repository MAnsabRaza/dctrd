<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Bundle;
use App\Models\BookingBundle;
use App\Models\Booking;
use App\Models\Category;
use App\Models\BookingCategory;
use App\Models\Product;
use App\Models\Role;
use App\Models\UpcomingCourse;
use App\Models\Webinar;
use App\Services\AdvancedSearchService;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(
        protected AdvancedSearchService $searchService
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Main search page
    // ─────────────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $seoSettings     = getSeoMetas('search');
        $pageTitle       = !empty($seoSettings['title'])       ? $seoSettings['title']       : trans('site.search_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.search_page_title');
        $pageRobot       = getPageRobot('search');

        $data = [
            'pageTitle'         => $pageTitle,
            'pageDescription'   => $pageDescription,
            'pageRobot'         => $pageRobot,
            'resultCount'       => 0,
            'categories'        => Category::getCategories(),
            'bookingCategories' => BookingCategory::query()
                                        ->roots()
                                        ->active()
                                        ->with('children')
                                        ->get(),
        ];

        $search = $request->get('search', null);

        if (!empty($search) && strlen($search) >= 1) {
            $searchData = $this->getSearchData($search, $request);
            $data       = array_merge($data, $searchData);
        }

        return view('design_1.web.search.index', $data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: live suggestions
    // ─────────────────────────────────────────────────────────────────────────

    public function suggestions(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        return response()->json([
            'suggestions' => $this->searchService->suggestions($query),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: category tree
    // ─────────────────────────────────────────────────────────────────────────

    public function categoryTree(): JsonResponse
    {
        return response()->json($this->searchService->getCategoryTree());
    }

    // =========================================================================
    // FILTER HELPERS
    // =========================================================================

    /**
     * Apply price_min / price_max to any query.
     */
    private function applyPriceFilter(Builder $query, Request $request, string $column = 'price'): void
    {
        $min = $request->get('price_min');
        $max = $request->get('price_max');

        if ($min !== null && $min !== '') {
            $query->where($column, '>=', (float) $min);
        }
        if ($max !== null && $max !== '') {
            $query->where($column, '<=', (float) $max);
        }
    }

    /**
     * Apply minimum rating filter.
     */
    private function applyRatingFilter(Builder $query, Request $request, string $column = 'avg_rating'): void
    {
        $rating = $request->get('rating');
        if ($rating !== null && $rating !== '') {
            $query->where($column, '>=', (float) $rating);
        }
    }

    /**
     * Apply Haversine nearby scope when lat/lng/radius_km are present
     * AND the model actually has scopeNearby defined.
     */
    private function applyNearby(Builder $query, Request $request, string $modelClass): bool
    {
        if (
            $request->filled('lat') &&
            $request->filled('lng') &&
            $request->filled('radius_km') &&
            method_exists($modelClass, 'scopeNearby')
        ) {
            $query->nearby(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius_km
            );
            return true;
        }
        return false;
    }

    /**
     * Apply sort order.
     * $nearbyApplied  — whether scopeNearby already added a `distance` column.
     * $priceCol       — name of price column on this model's table.
     * $ratingCol      — name of rating column on this model's table.
     */
    private function applySort(
        Builder $query,
        Request $request,
        bool    $nearbyApplied,
        string  $priceCol  = 'price',
        string  $ratingCol = 'avg_rating'
    ): void {
        $sort = $request->get('sort', 'relevance');

        switch ($sort) {
            case 'price_asc':
                $query->orderBy($priceCol, 'asc');
                break;

            case 'price_desc':
                $query->orderBy($priceCol, 'desc');
                break;

            case 'rating':
                $query->orderBy($ratingCol, 'desc');
                break;

            case 'distance':
                // Only sort by distance when scopeNearby added that column
                $nearbyApplied
                    ? $query->orderBy('distance', 'asc')
                    : $query->inRandomOrder();
                break;

            case 'relevance':
            default:
                if (!$nearbyApplied) {
                    $query->inRandomOrder();
                }
                break;
        }
    }

    // =========================================================================
    // MAIN SEARCH DATA BUILDER
    // =========================================================================

    private function getSearchData(string $search, Request $request): array
    {
        // ── Shared filter values ─────────────────────────────────────────────
        $selectedCategories    = array_filter((array) $request->get('categories', []));
        $selectedBookingCats   = array_filter((array) $request->get('booking_categories', []));

        // ── Webinars / Courses ───────────────────────────────────────────────
        $webinarsQuery = Webinar::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $q) use ($search) {
                $q->whereTranslationLike('title', "%$search%")
                  ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
                'reviews',
            ]);

        if (!empty($selectedCategories)) {
            $webinarsQuery->whereIn('category_id', $selectedCategories);
        }

        $this->applyPriceFilter($webinarsQuery, $request, 'price');
        $this->applyRatingFilter($webinarsQuery, $request, 'avg_rating');
        $webinarsNearby = $this->applyNearby($webinarsQuery, $request, Webinar::class);
        $this->applySort($webinarsQuery, $request, $webinarsNearby, 'price', 'avg_rating');

        $webinarsCount = (clone $webinarsQuery)->count();
        $webinars      = $webinarsQuery->limit(20)->get();

        // ── Course Bundles ───────────────────────────────────────────────────
        $bundlesQuery = Bundle::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $q) use ($search) {
                $q->whereTranslationLike('title', "%$search%")
                  ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
                'reviews',
            ]);

        $this->applyPriceFilter($bundlesQuery, $request, 'price');
        $this->applySort($bundlesQuery, $request, false, 'price', 'avg_rating');

        $bundlesCount = (clone $bundlesQuery)->count();
        $bundles      = $bundlesQuery->limit(20)->get();

        // ── Upcoming Courses ─────────────────────────────────────────────────
        $upcomingQuery = UpcomingCourse::query()
            ->where('status', 'active')
            ->where(function (Builder $q) use ($search) {
                $q->whereTranslationLike('title', "%$search%")
                  ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ]);

        $this->applyPriceFilter($upcomingQuery, $request, 'price');
        $this->applySort($upcomingQuery, $request, false, 'price', 'avg_rating');

        $upcomingCount   = (clone $upcomingQuery)->count();
        $upcomingCourses = $upcomingQuery->limit(20)->get();

        // ── Products ─────────────────────────────────────────────────────────
        $productsQuery = Product::query()
            ->where('status', 'active')
            ->where(function (Builder $q) use ($search) {
                $q->whereTranslationLike('title', "%$search%")
                  ->orWhereTranslationLike('summary', "%$search%")
                  ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'creator' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ]);

        $this->applyPriceFilter($productsQuery, $request, 'price');
        $productsNearby = $this->applyNearby($productsQuery, $request, Product::class);
        $this->applySort($productsQuery, $request, $productsNearby, 'price', 'avg_rating');

        $productsCount = (clone $productsQuery)->count();
        $products      = $productsQuery->limit(20)->get();

        // ── Blog Posts ───────────────────────────────────────────────────────
        $postsQuery = Blog::query()
            ->where('status', 'publish')
            ->where(function (Builder $q) use ($search) {
                $q->whereTranslationLike('title', "%$search%")
                  ->orWhereTranslationLike('description', "%$search%")
                  ->orWhereTranslationLike('content', "%$search%");
            })
            ->with([
                'author' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ]);

        // Posts have no price/rating/nearby — just sort by created_at for relevance
        $sort = $request->get('sort', 'relevance');
        if (!in_array($sort, ['price_asc', 'price_desc', 'rating', 'distance'])) {
            $postsQuery->inRandomOrder();
        }

        $postsCount = (clone $postsQuery)->count();
        $posts      = $postsQuery->limit(20)->get();

        // ── Users: Instructors + Organizations ───────────────────────────────
        $usersBaseQuery = User::query()
            ->where('status', 'active')
            ->where(function (Builder $q) use ($search) {
                $q->where('full_name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%")
                  ->orWhere('mobile', 'like', "%$search%");
            })
            ->where(function (Builder $q) {
                $q->where('role_name', Role::$teacher)
                  ->orWhere('role_name', Role::$organization);
            });

        // Rating filter on users
        $this->applyRatingFilter($usersBaseQuery, $request, 'avg_rating');
        $usersNearby = $this->applyNearby($usersBaseQuery, $request, User::class);

        $instructorsQuery = (clone $usersBaseQuery)->where('role_name', Role::$teacher);
        $this->applySort($instructorsQuery, $request, $usersNearby, 'price', 'avg_rating');
        $instructors = $instructorsQuery->limit(20)->get();

        $organizationsQuery = (clone $usersBaseQuery)->where('role_name', Role::$organization);
        $this->applySort($organizationsQuery, $request, $usersNearby, 'price', 'avg_rating');
        $organizations = $organizationsQuery->limit(20)->get();

        $usersCount = $instructors->count() + $organizations->count();

        // ── Bookings ─────────────────────────────────────────────────────────
        $bookingsQuery = Booking::query()
            ->where('status', 'published')
            ->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            })
            ->with(['creator', 'category']);

        if (!empty($selectedBookingCats)) {
            $bookingsQuery->whereIn('category_id', $selectedBookingCats);
        }

        $this->applyPriceFilter($bookingsQuery, $request, 'price');
        $this->applyRatingFilter($bookingsQuery, $request, 'rating');
        $bookingsNearby = $this->applyNearby($bookingsQuery, $request, Booking::class);
        $this->applySort($bookingsQuery, $request, $bookingsNearby, 'price', 'rating');

        $bookingsCount = (clone $bookingsQuery)->count();
        $bookings      = $bookingsQuery->limit(20)->get();

        // ── Booking Bundles ──────────────────────────────────────────────────
        $bookingBundlesQuery = BookingBundle::query()
            ->where(function (Builder $q) use ($search) {
                $q->where('title', 'like', "%$search%")
                  ->orWhere('description', 'like', "%$search%");
            })
            ->with(['creator']);

        $this->applyPriceFilter($bookingBundlesQuery, $request, 'price');
        $this->applySort($bookingBundlesQuery, $request, false, 'price', 'avg_rating');

        $bookingBundlesCount = (clone $bookingBundlesQuery)->count();
        $bookingBundles      = $bookingBundlesQuery->limit(20)->get();

        // ── Total count ──────────────────────────────────────────────────────
        $resultCount = $webinarsCount + $bundlesCount + $upcomingCount
                     + $productsCount + $postsCount + $usersCount
                     + $bookingsCount + $bookingBundlesCount;

        return compact(
            'resultCount',
            'webinars',
            'bundles',
            'upcomingCourses',
            'products',
            'posts',
            'instructors',
            'organizations',
            'bookings',
            'bookingBundles'
        );
    }
}