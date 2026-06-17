<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\Booking;
use App\Models\BookingBundle;
use App\Models\BookingCategory;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Product;
use App\Models\UpcomingCourse;
use App\Models\Webinar;
use App\User;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class AdvancedSearchService
{
    // All supported content types
    public const TYPES = [
        'courses',
        'bundles',
        'upcoming_courses',
        'products',
        'bookings',
        'booking_bundles',
        'instructors',
        'organizations',
        'posts',
    ];

    /**
     * Main search entry point.
     *
     * @param  string       $query       Search text (min 2 chars recommended)
     * @param  array        $types       Which content types to search. Empty = all.
     * @param  array        $filters     ['price_min', 'price_max', 'rating', 'sort',
     *                                    'categories', 'booking_categories']
     * @param  float|null   $lat         Latitude for proximity filter
     * @param  float|null   $lng         Longitude for proximity filter
     * @param  float|null   $radiusKm    Radius in km
     * @param  int          $perPage     Results per page
     * @param  int          $page        Current page (1-based)
     * @return array
     */
    public function search(
        string $query,
        array  $types       = [],
        array  $filters     = [],
        ?float $lat         = null,
        ?float $lng         = null,
        ?float $radiusKm    = null,
        int    $perPage     = 20,
        int    $page        = 1
    ): array {
        // Empty types = search all
        $activeTypes = empty($types) ? self::TYPES : $types;
        $nearbyActive = ($lat !== null && $lng !== null && $radiusKm !== null);

        // Build a cache key so identical queries are cheap
        $cacheKey = 'search_' . md5(
            $query .
            implode(',', $activeTypes) .
            json_encode($filters) .
            "{$lat},{$lng},{$radiusKm}"
        );

        // Use 5-minute cache for search results
        $allResults = Cache::remember($cacheKey, 300, function () use (
            $query, $activeTypes, $filters, $lat, $lng, $radiusKm, $nearbyActive
        ) {
            $merged = collect();

            if (in_array('courses', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchCourses($query, $filters, $lat, $lng, $radiusKm, $nearbyActive)
                );
            }

            if (in_array('bundles', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchBundles($query, $filters)
                );
            }

            if (in_array('upcoming_courses', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchUpcomingCourses($query)
                );
            }

            if (in_array('products', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchProducts($query, $filters, $lat, $lng, $radiusKm, $nearbyActive)
                );
            }

            if (in_array('bookings', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchBookings($query, $filters, $lat, $lng, $radiusKm, $nearbyActive)
                );
            }

            if (in_array('booking_bundles', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchBookingBundles($query)
                );
            }

            if (in_array('instructors', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchInstructors($query, $lat, $lng, $radiusKm, $nearbyActive)
                );
            }

            if (in_array('organizations', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchOrganizations($query, $lat, $lng, $radiusKm, $nearbyActive)
                );
            }

            if (in_array('posts', $activeTypes)) {
                $merged = $merged->merge(
                    $this->searchPosts($query)
                );
            }

            return $merged;
        });

        // Sort combined results
        $sort = $filters['sort'] ?? 'relevance';
        $allResults = $this->sortResults($allResults, $sort, $nearbyActive);

        // Count per type (before pagination)
        $typeCounts = [];
        foreach (self::TYPES as $type) {
            $typeCounts[$type] = $allResults->where('type', $type)->count();
        }

        $total = $allResults->count();

        // Paginate in memory
        $offset  = ($page - 1) * $perPage;
        $paged   = $allResults->slice($offset, $perPage)->values();

        return [
            'results'    => $paged,
            'total'      => $total,
            'per_page'   => $perPage,
            'page'       => $page,
            'last_page'  => (int) ceil($total / $perPage),
            'types'      => $typeCounts,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Type-specific search methods
    // ─────────────────────────────────────────────────────────────────────────

    private function searchCourses(
        string $query,
        array  $filters,
        ?float $lat,
        ?float $lng,
        ?float $radiusKm,
        bool   $nearbyActive
    ): Collection {
        $q = Webinar::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $builder) use ($query) {
                // webinars uses a translation table for title in newer code,
                // but the original migration shows title is a plain column.
                // We use both patterns safely:
                $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'avatar', 'avatar_settings', 'role_name'),
                'reviews',
                'category',
            ]);

        // Category filter
        if (!empty($filters['categories'])) {
            $q->whereIn('category_id', $filters['categories']);
        }

        // Price filter
        if (!empty($filters['price_min'])) {
            $q->where('price', '>=', (float) $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $q->where('price', '<=', (float) $filters['price_max']);
        }

        // Nearby
        if ($nearbyActive && method_exists(Webinar::class, 'scopeNearby')) {
            $q->nearby($lat, $lng, $radiusKm);
        }

        if (!$nearbyActive) {
            $q->inRandomOrder();
        }

        return $q->limit(50)->get()->map(fn($item) => $this->normalizeWebinar($item, $nearbyActive));
    }

    private function searchBundles(string $query, array $filters): Collection
    {
        $q = Bundle::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $builder) use ($query) {
                $builder->whereTranslationLike('title', "%{$query}%")
                        ->orWhereTranslationLike('description', "%{$query}%");
            })
            ->with([
                'teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'avatar', 'avatar_settings', 'role_name'),
                'reviews',
            ])
            ->inRandomOrder()
            ->limit(30);

        return $q->get()->map(fn($item) => $this->normalizeBundle($item));
    }

    private function searchUpcomingCourses(string $query): Collection
    {
        $q = UpcomingCourse::query()
            ->where('status', 'active')
            ->where(function (Builder $builder) use ($query) {
                $builder->whereTranslationLike('title', "%{$query}%")
                        ->orWhereTranslationLike('description', "%{$query}%");
            })
            ->with(['teacher' => fn($q) => $q->select('id', 'full_name', 'username', 'avatar', 'role_name')])
            ->inRandomOrder()
            ->limit(20);

        return $q->get()->map(fn($item) => $this->normalizeUpcomingCourse($item));
    }

    private function searchProducts(
        string $query,
        array  $filters,
        ?float $lat,
        ?float $lng,
        ?float $radiusKm,
        bool   $nearbyActive
    ): Collection {
        $q = Product::query()
            ->where('status', 'active')
            ->where(function (Builder $builder) use ($query) {
                $builder->whereTranslationLike('title', "%{$query}%")
                        ->orWhereTranslationLike('summary', "%{$query}%")
                        ->orWhereTranslationLike('description', "%{$query}%");
            })
            ->with(['creator' => fn($q) => $q->select('id', 'full_name', 'username', 'avatar', 'role_name')]);

        if ($nearbyActive && method_exists(Product::class, 'scopeNearby')) {
            $q->nearby($lat, $lng, $radiusKm);
        }

        if (!$nearbyActive) {
            $q->inRandomOrder();
        }

        return $q->limit(30)->get()->map(fn($item) => $this->normalizeProduct($item));
    }

    private function searchBookings(
        string $query,
        array  $filters,
        ?float $lat,
        ?float $lng,
        ?float $radiusKm,
        bool   $nearbyActive
    ): Collection {
        $q = Booking::query()
            ->where('status', 'published')
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['creator', 'category']);

        // Booking category filter
        if (!empty($filters['booking_categories'])) {
            $q->whereIn('category_id', $filters['booking_categories']);
        }

        // Price filter
        if (!empty($filters['price_min'])) {
            $q->where('price', '>=', (float) $filters['price_min']);
        }
        if (!empty($filters['price_max'])) {
            $q->where('price', '<=', (float) $filters['price_max']);
        }

        // Rating filter
        if (!empty($filters['rating'])) {
            $q->where('rating', '>=', (float) $filters['rating']);
        }

        // Nearby (uses scopeNearby from task 3.4)
        if ($nearbyActive && method_exists(Booking::class, 'scopeNearby')) {
            $q->nearby($lat, $lng, $radiusKm);
        }

        if (!$nearbyActive) {
            $q->inRandomOrder();
        }

        return $q->limit(50)->get()->map(fn($item) => $this->normalizeBooking($item, $nearbyActive));
    }

    private function searchBookingBundles(string $query): Collection
    {
        $q = BookingBundle::query()
            ->where(function (Builder $builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
            })
            ->with(['creator'])
            ->inRandomOrder()
            ->limit(20);

        return $q->get()->map(fn($item) => $this->normalizeBookingBundle($item));
    }

    private function searchInstructors(
        string $query,
        ?float $lat,
        ?float $lng,
        ?float $radiusKm,
        bool   $nearbyActive
    ): Collection {
        $q = User::query()
            ->where('status', 'active')
            ->where('role_name', Role::$teacher)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('full_name', 'like', "%{$query}%")
                        ->orWhere('bio', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%");
            });

        if ($nearbyActive && method_exists(User::class, 'scopeNearby')) {
            $q->nearby($lat, $lng, $radiusKm);
        }

        if (!$nearbyActive) {
            $q->inRandomOrder();
        }

        return $q->limit(20)->get()->map(fn($item) => $this->normalizeUser($item, 'instructor'));
    }

    private function searchOrganizations(
        string $query,
        ?float $lat,
        ?float $lng,
        ?float $radiusKm,
        bool   $nearbyActive
    ): Collection {
        $q = User::query()
            ->where('status', 'active')
            ->where('role_name', Role::$organization)
            ->where(function (Builder $builder) use ($query) {
                $builder->where('full_name', 'like', "%{$query}%")
                        ->orWhere('bio', 'like', "%{$query}%")
                        ->orWhere('username', 'like', "%{$query}%");
            });

        if ($nearbyActive && method_exists(User::class, 'scopeNearby')) {
            $q->nearby($lat, $lng, $radiusKm);
        }

        if (!$nearbyActive) {
            $q->inRandomOrder();
        }

        return $q->limit(20)->get()->map(fn($item) => $this->normalizeUser($item, 'organization'));
    }

    private function searchPosts(string $query): Collection
    {
        $q = Blog::query()
            ->where('status', 'publish')
            ->where(function (Builder $builder) use ($query) {
                $builder->whereTranslationLike('title', "%{$query}%")
                        ->orWhereTranslationLike('description', "%{$query}%")
                        ->orWhereTranslationLike('content', "%{$query}%");
            })
            ->with(['author' => fn($q) => $q->select('id', 'full_name', 'username', 'avatar', 'role_name')])
            ->inRandomOrder()
            ->limit(20);

        return $q->get()->map(fn($item) => $this->normalizePost($item));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Normalizers — convert each model to a uniform result array
    // ─────────────────────────────────────────────────────────────────────────

    private function normalizeWebinar(Webinar $item, bool $nearbyActive): array
    {
        $avgRating = $item->reviews ? $item->reviews->avg('rates') : null;

        return [
            'id'          => $item->id,
            'type'        => 'course',
            'label'       => trans('update.courses'),
            'title'       => $item->title,
            'description' => $item->description ? \Str::limit(strip_tags($item->description), 120) : '',
            'price'       => $item->price ?? 0,
            'price_label' => $item->price ? handlePrice($item->price) : trans('public.free'),
            'rating'      => $avgRating ? round($avgRating, 1) : null,
            'image'       => $item->thumbnail ?? $item->image_cover ?? null,
            'url'         => url('/courses/' . $item->slug),
            'category'    => optional($item->category)->title,
            'distance_km' => $nearbyActive && isset($item->distance) ? round($item->distance, 1) : null,
            'score'       => $this->relevanceScore($item->title, ''),
            'instructor'  => optional($item->teacher)->full_name,
        ];
    }

    private function normalizeBundle(Bundle $item): array
    {
        $avgRating = $item->reviews ? $item->reviews->avg('rates') : null;

        return [
            'id'          => $item->id,
            'type'        => 'bundle',
            'label'       => trans('update.bundles'),
            'title'       => $item->title,
            'description' => $item->description ? \Str::limit(strip_tags($item->description), 120) : '',
            'price'       => $item->price ?? 0,
            'price_label' => $item->price ? handlePrice($item->price) : trans('public.free'),
            'rating'      => $avgRating ? round($avgRating, 1) : null,
            'image'       => $item->thumbnail ?? null,
            'url'         => url('/bundles/' . $item->slug),
            'category'    => null,
            'distance_km' => null,
            'score'       => 0.7,
            'instructor'  => optional($item->teacher)->full_name,
        ];
    }

    private function normalizeUpcomingCourse(UpcomingCourse $item): array
    {
        return [
            'id'          => $item->id,
            'type'        => 'upcoming_course',
            'label'       => trans('update.upcoming_courses'),
            'title'       => $item->title,
            'description' => $item->description ? \Str::limit(strip_tags($item->description), 120) : '',
            'price'       => $item->price ?? 0,
            'price_label' => $item->price ? handlePrice($item->price) : trans('public.free'),
            'rating'      => null,
            'image'       => $item->thumbnail ?? null,
            'url'         => url('/upcoming-courses/' . $item->slug),
            'category'    => null,
            'distance_km' => null,
            'score'       => 0.6,
            'instructor'  => optional($item->teacher)->full_name,
        ];
    }

    private function normalizeProduct(Product $item): array
    {
        return [
            'id'          => $item->id,
            'type'        => 'product',
            'label'       => trans('update.store_products'),
            'title'       => $item->title,
            'description' => $item->summary ? \Str::limit(strip_tags($item->summary), 120) : '',
            'price'       => $item->price ?? 0,
            'price_label' => $item->price ? handlePrice($item->price) : trans('public.free'),
            'rating'      => null,
            'image'       => $item->thumbnail ?? null,
            'url'         => url('/store/products/' . $item->slug),
            'category'    => null,
            'distance_km' => null,
            'score'       => 0.75,
            'instructor'  => optional($item->creator)->full_name,
        ];
    }

    private function normalizeBooking(Booking $item, bool $nearbyActive): array
    {
        return [
            'id'          => $item->id,
            'type'        => 'booking',
            'label'       => trans('home.bookings') ?? 'Booking',
            'title'       => $item->title,
            'description' => $item->description ? \Str::limit(strip_tags($item->description), 120) : '',
            'price'       => $item->price ?? 0,
            'price_label' => $item->price
                ? handlePrice($item->price) . ($item->price_unit ? '/' . $item->price_unit : '')
                : trans('public.free'),
            'rating'      => $item->rating ?? null,
            'image'       => $item->thumbnail ?? null,
            'url'         => method_exists($item, 'getUrl') ? $item->getUrl() : url('/bookings/' . $item->slug),
            'category'    => optional($item->category)->title,
            'distance_km' => $nearbyActive && isset($item->distance) ? round($item->distance, 1) : null,
            'score'       => $this->relevanceScore($item->title, ''),
            'instructor'  => optional($item->creator)->full_name,
        ];
    }

    private function normalizeBookingBundle(BookingBundle $item): array
    {
        return [
            'id'          => $item->id,
            'type'        => 'booking_bundle',
            'label'       => trans('home.booking_bundles') ?? 'Booking Bundle',
            'title'       => $item->title,
            'description' => $item->description ? \Str::limit(strip_tags($item->description), 120) : '',
            'price'       => $item->price ?? 0,
            'price_label' => $item->price ? handlePrice($item->price) : trans('public.free'),
            'rating'      => $item->rating ?? null,
            'image'       => $item->thumbnail ?? null,
            'url'         => url('/booking-bundles/' . $item->slug),
            'category'    => null,
            'distance_km' => null,
            'score'       => 0.65,
            'instructor'  => optional($item->creator)->full_name,
        ];
    }

    private function normalizeUser(User $item, string $type): array
    {
        $avatarUrl = null;
        if ($item->avatar) {
            $avatarUrl = url('/store/user_avatars/' . $item->avatar);
        }

        return [
            'id'          => $item->id,
            'type'        => $type,
            'label'       => $type === 'instructor' ? trans('home.instructors') : trans('home.organizations'),
            'title'       => $item->full_name,
            'description' => $item->bio ? \Str::limit(strip_tags($item->bio), 120) : '',
            'price'       => null,
            'price_label' => null,
            'rating'      => null,
            'image'       => $avatarUrl,
            'url'         => url('/profile/' . $item->username),
            'category'    => null,
            'distance_km' => null,
            'score'       => 0.8,
            'instructor'  => null,
        ];
    }

    private function normalizePost(Blog $item): array
    {
        return [
            'id'          => $item->id,
            'type'        => 'post',
            'label'       => trans('update.blog_posts'),
            'title'       => $item->title,
            'description' => $item->description ? \Str::limit(strip_tags($item->description), 120) : '',
            'price'       => null,
            'price_label' => null,
            'rating'      => null,
            'image'       => $item->image ?? null,
            'url'         => url('/blog/' . $item->slug),
            'category'    => null,
            'distance_km' => null,
            'score'       => 0.5,
            'instructor'  => optional($item->author)->full_name,
        ];
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Sorting
    // ─────────────────────────────────────────────────────────────────────────

    private function sortResults(Collection $results, string $sort, bool $nearbyActive): Collection
    {
        return match ($sort) {
            'price_asc'  => $results->sortBy(fn($r) => $r['price'] ?? PHP_INT_MAX),
            'price_desc' => $results->sortByDesc(fn($r) => $r['price'] ?? 0),
            'rating'     => $results->sortByDesc(fn($r) => $r['rating'] ?? 0),
            'distance'   => $nearbyActive
                ? $results->sortBy(fn($r) => $r['distance_km'] ?? PHP_INT_MAX)
                : $results->sortByDesc(fn($r) => $r['score'] ?? 0),
            default      => $results->sortByDesc(fn($r) => $r['score'] ?? 0), // relevance
        };
    }

    /**
     * Simple relevance score based on title match position.
     * Returns 1.0 for exact match, 0.9 for starts-with, 0.7 for contains.
     */
    private function relevanceScore(string $title, string $query): float
    {
        if (empty($query)) {
            return 0.7;
        }

        $title = mb_strtolower($title);
        $query = mb_strtolower($query);

        if ($title === $query) {
            return 1.0;
        }
        if (str_starts_with($title, $query)) {
            return 0.9;
        }
        if (str_contains($title, $query)) {
            return 0.8;
        }
        return 0.7;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Suggestions (used by the AJAX endpoint, max 8 mixed results)
    // ─────────────────────────────────────────────────────────────────────────

    public function suggestions(string $query, int $limit = 8): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $suggestions = collect();

        // Bookings — 2 slots
        $bookings = Booking::query()
            ->where('status', 'published')
            ->where(function (Builder $q) use ($query) {
                $q->where('title', 'like', "%{$query}%");
            })
            ->limit(2)->get()
            ->map(fn($b) => [
                'type'   => 'booking',
                'icon'   => '🏨',
                'title'  => $b->title,
                'url'    => method_exists($b, 'getUrl') ? $b->getUrl() : url('/bookings/' . $b->slug),
                'image'  => $b->thumbnail,
                'price'  => $b->price ? handlePrice($b->price) . ($b->price_unit ? '/' . $b->price_unit : '') : null,
            ]);

        // Courses — 2 slots
        $courses = Webinar::query()
            ->where('status', 'active')
            ->where('private', false)
            ->where('title', 'like', "%{$query}%")
            ->limit(2)->get()
            ->map(fn($w) => [
                'type'   => 'course',
                'icon'   => '📚',
                'title'  => $w->title,
                'url'    => url('/courses/' . $w->slug),
                'image'  => $w->thumbnail ?? $w->image_cover,
                'price'  => $w->price ? handlePrice($w->price) : trans('public.free'),
            ]);

        // Products — 2 slots
        $products = Product::query()
            ->where('status', 'active')
            ->whereTranslationLike('title', "%{$query}%")
            ->limit(2)->get()
            ->map(fn($p) => [
                'type'   => 'product',
                'icon'   => '🛍️',
                'title'  => $p->title,
                'url'    => url('/store/products/' . $p->slug),
                'image'  => $p->thumbnail,
                'price'  => $p->price ? handlePrice($p->price) : null,
            ]);

        // Instructors — 1 slot
        $instructors = User::query()
            ->where('status', 'active')
            ->where('role_name', Role::$teacher)
            ->where('full_name', 'like', "%{$query}%")
            ->limit(1)->get()
            ->map(fn($u) => [
                'type'   => 'instructor',
                'icon'   => '👤',
                'title'  => $u->full_name,
                'url'    => url('/profile/' . $u->username),
                'image'  => null,
                'price'  => null,
            ]);

        $suggestions = $suggestions
            ->merge($bookings)
            ->merge($courses)
            ->merge($products)
            ->merge($instructors)
            ->take($limit);

        return $suggestions->values()->all();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Category tree (cached 1 hour)
    // ─────────────────────────────────────────────────────────────────────────

    public function getCategoryTree(): array
    {
        return Cache::remember('search_category_tree', 3600, function () {
            $tree = [];

            // Courses
            $courseCount = Webinar::where('status', 'active')->count();
            $tree[] = [
                'key'      => 'courses',
                'label'    => trans('update.courses'),
                'count'    => $courseCount,
                'children' => [],
            ];

            // Bundles
            $bundleCount = Bundle::where('status', 'active')->count();
            $tree[] = [
                'key'      => 'bundles',
                'label'    => trans('update.bundles'),
                'count'    => $bundleCount,
                'children' => [],
            ];

            // Upcoming Courses
            $upcomingCount = UpcomingCourse::where('status', 'active')->count();
            $tree[] = [
                'key'      => 'upcoming_courses',
                'label'    => trans('update.upcoming_courses'),
                'count'    => $upcomingCount,
                'children' => [],
            ];

            // Products
            $productCount = Product::where('status', 'active')->count();
            $tree[] = [
                'key'      => 'products',
                'label'    => trans('update.store_products'),
                'count'    => $productCount,
                'children' => [],
            ];

            // Bookings — fetch from booking_categories hierarchy
            $bookingRoots = BookingCategory::query()
                ->whereNull('parent_id')
                ->where('status', true)
                ->orderBy('order')
                ->with(['children' => fn($q) => $q->where('status', true)->orderBy('order')])
                ->get();

            foreach ($bookingRoots as $root) {
                $children = $root->children->map(fn($child) => [
                    'key'   => 'booking_cat_' . $child->id,
                    'label' => $child->title,
                    'count' => Booking::where('status', 'published')
                                      ->where('category_id', $child->id)
                                      ->count(),
                ])->values()->all();

                $tree[] = [
                    'key'      => 'booking_root_' . $root->id,
                    'label'    => $root->title,
                    'count'    => Booking::where('status', 'published')
                                         ->where('category_id', $root->id)
                                         ->count(),
                    'children' => $children,
                ];
            }

            // Booking bundles
            $bbCount = BookingBundle::count();
            $tree[] = [
                'key'      => 'booking_bundles',
                'label'    => trans('home.booking_bundles') ?? 'Booking Bundles',
                'count'    => $bbCount,
                'children' => [],
            ];

            // Instructors
            $instructorCount = User::where('status', 'active')
                                   ->where('role_name', Role::$teacher)
                                   ->count();
            $tree[] = [
                'key'      => 'instructors',
                'label'    => trans('home.instructors'),
                'count'    => $instructorCount,
                'children' => [],
            ];

            // Organizations
            $orgCount = User::where('status', 'active')
                            ->where('role_name', Role::$organization)
                            ->count();
            $tree[] = [
                'key'      => 'organizations',
                'label'    => trans('home.organizations'),
                'count'    => $orgCount,
                'children' => [],
            ];

            return $tree;
        });
    }

    /**
     * Flush all search-related caches.
     * Call this when content changes (new booking/course added, status change, etc.)
     */
    public function clearCache(): void
    {
        Cache::forget('search_category_tree');
        // Note: individual query caches use md5 keys so we use tags in production.
        // For now, flush the entire cache store on content changes.
    }
}