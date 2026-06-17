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
        $seoSettings    = getSeoMetas('search');
        $pageTitle      = !empty($seoSettings['title'])       ? $seoSettings['title']       : trans('site.search_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.search_page_title');
        $pageRobot      = getPageRobot('search');

        $data = [
            'pageTitle'       => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot'       => $pageRobot,
            'resultCount'     => 0,
            // Hierarchical category trees for sidebar
            'categories'         => Category::getCategories(),
            'bookingCategories'  => BookingCategory::query()
                                        ->roots()
                                        ->active()
                                        ->with('children')
                                        ->get(),
        ];

        $search = $request->get('search', null);

        if (!empty($search) && strlen($search) >= 3) {
            $searchData = $this->getSearchData($search, $request);
            $data       = array_merge($data, $searchData);
        }

        return view('design_1.web.search.index', $data);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: live suggestions (called on keyup, debounced in JS)
    // ─────────────────────────────────────────────────────────────────────────

    public function suggestions(Request $request): JsonResponse
    {
        $query = trim($request->get('q', ''));

        if (strlen($query) < 2) {
            return response()->json(['suggestions' => []]);
        }

        $suggestions = $this->searchService->suggestions($query);

        return response()->json(['suggestions' => $suggestions]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // AJAX: category tree for search dropdown
    // ─────────────────────────────────────────────────────────────────────────

    public function categoryTree(): JsonResponse
    {
        $tree = $this->searchService->getCategoryTree();
        return response()->json($tree);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Internal: build all search result data (same logic as before, kept intact
    // so existing view continues to work without change).
    // ─────────────────────────────────────────────────────────────────────────

    private function getSearchData(string $search, Request $request): array
    {
        $nearbyIsActive = $request->filled(['lat', 'lng', 'radius_km']);

        // ── Webinars / Courses ───────────────────────────────────────────────
        $webinarsQuery = Webinar::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%")
                      ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
                'reviews',
            ]);

        // Filter by selected course categories (sidebar checkboxes)
        $selectedCategories = $request->get('categories', []);
        if (!empty($selectedCategories)) {
            $webinarsQuery->whereIn('category_id', $selectedCategories);
        }

        if ($nearbyIsActive && method_exists(Webinar::class, 'scopeNearby')) {
            $webinarsQuery->nearby(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius_km
            );
        }

        $webinarsCount = deepClone($webinarsQuery)->count();
        if (!$nearbyIsActive) {
            $webinarsQuery->inRandomOrder();
        }
        $webinars = $webinarsQuery->limit(20)->get();

        // ── Bundles ──────────────────────────────────────────────────────────
        $bundlesQuery = Bundle::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%")
                      ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
                'reviews',
            ]);

        $bundlesCount = deepClone($bundlesQuery)->count();
        $bundles = $bundlesQuery->inRandomOrder()->limit(20)->get();

        // ── Upcoming Courses ─────────────────────────────────────────────────
        $upcomingCoursesQuery = UpcomingCourse::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%")
                      ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ]);

        $upcomingCoursesCount = deepClone($upcomingCoursesQuery)->count();
        $upcomingCourses = $upcomingCoursesQuery->inRandomOrder()->limit(20)->get();

        // ── Products ─────────────────────────────────────────────────────────
        $productsQuery = Product::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%")
                      ->orWhereTranslationLike('summary', "%$search%")
                      ->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'creator' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ]);

        if ($nearbyIsActive && method_exists(Product::class, 'scopeNearby')) {
            $productsQuery->nearby(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius_km
            );
        }

        $productsCount = deepClone($productsQuery)->count();
        if (!$nearbyIsActive) {
            $productsQuery->inRandomOrder();
        }
        $products = $productsQuery->limit(20)->get();

        // ── Blog Posts ───────────────────────────────────────────────────────
        $postsQuery = Blog::query()
            ->where('status', 'publish')
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%")
                      ->orWhereTranslationLike('description', "%$search%")
                      ->orWhereTranslationLike('content', "%$search%");
            })
            ->with([
                'author' => fn($q) => $q->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ]);

        $postsCount = deepClone($postsQuery)->count();
        $posts = $postsQuery->inRandomOrder()->limit(20)->get();

        // ── Users (instructors + organizations) ──────────────────────────────
        $usersQuery = User::query()
            ->where('status', 'active')
            ->where(function (Builder $query) use ($search) {
                $query->where('full_name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('mobile', 'like', "%$search%");
            })
            ->where(function (Builder $query) {
                $query->where('role_name', Role::$teacher)
                      ->orWhere('role_name', Role::$organization);
            });

        if ($nearbyIsActive && method_exists(User::class, 'scopeNearby')) {
            $usersQuery->nearby(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius_km
            );
        }

        $usersCount = deepClone($usersQuery)->count();

        $instructorsQuery = deepClone($usersQuery)->where('role_name', Role::$teacher);
        if (!$nearbyIsActive) {
            $instructorsQuery->inRandomOrder();
        }
        $instructors = $instructorsQuery->limit(20)->get();

        $organizationsQuery = deepClone($usersQuery)->where('role_name', Role::$organization);
        if (!$nearbyIsActive) {
            $organizationsQuery->inRandomOrder();
        }
        $organizations = $organizationsQuery->limit(20)->get();

        // ── Bookings ─────────────────────────────────────────────────────────
        $bookingsQuery = Booking::query()
            ->where('status', 'published')
            ->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            })
            ->with(['creator', 'category']);

        // Filter by selected booking categories
        $selectedBookingCategories = $request->get('booking_categories', []);
        if (!empty($selectedBookingCategories)) {
            $bookingsQuery->whereIn('category_id', $selectedBookingCategories);
        }

        if ($nearbyIsActive && method_exists(Booking::class, 'scopeNearby')) {
            $bookingsQuery->nearby(
                (float) $request->lat,
                (float) $request->lng,
                (float) $request->radius_km
            );
        }

        $bookingsCount = deepClone($bookingsQuery)->count();
        if (!$nearbyIsActive) {
            $bookingsQuery->inRandomOrder();
        }
        $bookings = $bookingsQuery->limit(20)->get();

        // ── Booking Bundles ──────────────────────────────────────────────────
        $bookingBundlesQuery = BookingBundle::query()
            ->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%$search%")
                      ->orWhere('description', 'like', "%$search%");
            })
            ->with(['creator']);

        $bookingBundlesCount = deepClone($bookingBundlesQuery)->count();
        $bookingBundles = $bookingBundlesQuery->inRandomOrder()->limit(20)->get();

        return [
            'resultCount'      => $webinarsCount + $bundlesCount + $upcomingCoursesCount
                                 + $productsCount + $postsCount + $usersCount
                                 + $bookingsCount + $bookingBundlesCount,
            'webinars'         => $webinars,
            'bundles'          => $bundles,
            'products'         => $products,
            'upcomingCourses'  => $upcomingCourses,
            'posts'            => $posts,
            'instructors'      => $instructors,
            'organizations'    => $organizations,
            'bookings'         => $bookings,
            'bookingBundles'   => $bookingBundles,
        ];
    }
}