<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Product;
use App\Models\Booking;
use App\Models\BookingBundle;
use App\Models\Role;
use App\Models\UpcomingCourse;
use App\Models\Webinar;
use App\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\BookingCategory;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $seoSettings = getSeoMetas('search');
        $pageTitle = !empty($seoSettings['title']) ? $seoSettings['title'] : trans('site.search_page_title');
        $pageDescription = !empty($seoSettings['description']) ? $seoSettings['description'] : trans('site.search_page_title');
        $pageRobot = getPageRobot('search');

        $data = [
            'pageTitle' => $pageTitle,
            'pageDescription' => $pageDescription,
            'pageRobot' => $pageRobot,
            'resultCount' => 0,
        ];

        // load categories for hierarchical filters (courses + bookings)
        $data['categories'] = Category::getCategories();
        $data['bookingCategories'] = BookingCategory::query()->roots()->active()->with('children')->get();
        $search = $request->get('search', null);

        if (!empty($search) and strlen($search) >= 3) {
            $searchData = $this->getSearchData($search, $request);
            $data = array_merge($data, $searchData);
        }

        return view('design_1.web.search.index', $data);
    }

    private function getSearchData($search, Request $request)
    {
        $nearbyIsActive = $request->filled(['lat', 'lng', 'radius_km']);

        $webinarsQuery = Webinar::query()->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
                $query->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => function ($query) {
                    $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                },
                'reviews'
            ]);

        if ($request->filled(['lat', 'lng', 'radius_km'])) {
            $webinarsQuery->nearby((float) $request->lat, (float) $request->lng, (float) $request->radius_km);
        }

        $webinarsCount = deepClone($webinarsQuery)->count();
        if (!$nearbyIsActive) {
            $webinarsQuery->inRandomOrder();
        }

        $webinars = $webinarsQuery->limit(20)->get();

        $bundlesQuery = Bundle::query()->where('status', 'active')
            ->where('private', false)
            ->where('only_for_students', false)
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
                $query->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => function ($query) {
                    $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                },
                'reviews'
            ]);

        $bundlesCount = deepClone($bundlesQuery)->count();
        $bundles = $bundlesQuery
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $upcomingCoursesQuery = UpcomingCourse::query()->where('status', 'active')
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
                $query->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'teacher' => function ($query) {
                    $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                },
            ]);

        $upcomingCoursesCount = deepClone($upcomingCoursesQuery)->count();
        $upcomingCourses = $upcomingCoursesQuery
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $productsQuery = Product::query()->where('status', 'active')
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
                $query->orWhereTranslationLike('summary', "%$search%");
                $query->orWhereTranslationLike('description', "%$search%");
            })
            ->with([
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                }
            ]);

        if ($request->filled(['lat', 'lng', 'radius_km'])) {
            $productsQuery->nearby((float) $request->lat, (float) $request->lng, (float) $request->radius_km);
        }

        $productsCount = deepClone($productsQuery)->count();
        if (!$nearbyIsActive) {
            $productsQuery->inRandomOrder();
        }

        $products = $productsQuery->limit(20)->get();

        $postsQuery = Blog::query()->where('status', 'publish')
            ->where(function (Builder $query) use ($search) {
                $query->whereTranslationLike('title', "%$search%");
                $query->orWhereTranslationLike('description', "%$search%");
                $query->orWhereTranslationLike('content', "%$search%");
            })
            ->with([
                'author' => function ($query) {
                    $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
                }
            ]);

        $postsCount = deepClone($postsQuery)->count();
        $posts = $postsQuery
            ->inRandomOrder()
            ->limit(20)
            ->get();

        $usersQuery = User::query()->where('status', 'active')
            ->where(function (Builder $query) use ($search) {
                $query->where('full_name', 'like', "%$search%");
                $query->orWhere('email', 'like', "%$search%");
                $query->orWhere('mobile', 'like', "%$search%");
            })
            ->where(function (Builder $query) {
                $query->where('role_name', Role::$teacher);
                $query->orWhere('role_name', Role::$organization);
            });

        if ($request->filled(['lat', 'lng', 'radius_km'])) {
            $usersQuery->nearby((float) $request->lat, (float) $request->lng, (float) $request->radius_km);
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

        // Bookings
        $bookingsQuery = Booking::query()->where('status', 'published')
            ->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%$search%");
                $query->orWhere('description', 'like', "%$search%");
            })
            ->with(['creator', 'category']);

        if ($request->filled(['lat', 'lng', 'radius_km'])) {
            $bookingsQuery->nearby((float) $request->lat, (float) $request->lng, (float) $request->radius_km);
        }

        $bookingsCount = deepClone($bookingsQuery)->count();
        if (!$nearbyIsActive) {
            $bookingsQuery->inRandomOrder();
        }
        $bookings = $bookingsQuery->limit(20)->get();

        // Booking Bundles
        $bookingBundlesQuery = BookingBundle::query()
            ->where(function (Builder $query) use ($search) {
                $query->where('title', 'like', "%$search%");
                $query->orWhere('description', 'like', "%$search%");
            })
            ->with(['creator']);

        $bookingBundlesCount = deepClone($bookingBundlesQuery)->count();
        $bookingBundles = $bookingBundlesQuery->inRandomOrder()->limit(20)->get();

        return [
            'resultCount' => $webinarsCount + $bundlesCount + $upcomingCoursesCount + $productsCount + $postsCount + $usersCount + $bookingsCount + $bookingBundlesCount,
            'webinars' => $webinars,
            'bundles' => $bundles,
            'products' => $products,
            'upcomingCourses' => $upcomingCourses,
            'posts' => $posts,
            'instructors' => $instructors,
            'organizations' => $organizations,
            'bookings' => $bookings ?? collect([]),
            'bookingBundles' => $bookingBundles ?? collect([]),
        ];
    }
}
