<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Category;

/**
 * Controller to list booking sellers and their bookings in admin panel
 */
class BookingSellerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_sellers');

        $query = User::query()->whereHas('bookings');

        $query = $this->handleFilters($request, $query);

        $users = $query
            ->withCount('bookings')
            ->with(['bookings' => function ($q) {
                $q->with(['category' => function ($c) {
                    $c->with('parent');
                }]);
            }])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $userGroups = Group::where('status', 'active')
            ->orderBy('created_at', 'desc')
            ->get();

        // Fetch all top-level (parent) booking categories for dynamic columns
        // Adjust the query below to match your Category model's scope/filter for bookings
        $parentCategories = Category::whereNull('parent_id')
            ->where('status', '1') // Assuming '1' means active
            ->orderBy('id', 'asc')
            ->get();

        // For each user build a per-parent-category booking count map
        foreach ($users as $user) {
            $user->total_bookings = $user->bookings->count();

            $categoryMap = [];
            foreach ($parentCategories as $parent) {
                $count = $user->bookings->filter(function ($booking) use ($parent) {
                    $cat = $booking->category;
                    if (!$cat) return false;
                    // Direct match: booking category IS the parent
                    if ($cat->id === $parent->id) return true;
                    // Child match: booking category's parent is this parent
                    if ($cat->parent_id === $parent->id) return true;
                    return false;
                })->count();

                $categoryMap[$parent->id] = $count;
            }

            $user->category_booking_counts = $categoryMap;
        }

        $data = [
            'pageTitle'        => trans('update.booking_sellers'),
            'users'            => $users,
            'userGroups'       => $userGroups,
            'parentCategories' => $parentCategories,
        ];

        return view('admin.booking.seller', $data);
    }

    private function handleFilters(Request $request, $query)
    {
        $full_name = $request->get('full_name');
        $group_id  = $request->get('group_id');
        $role_name = $request->get('role_name');

        if (!empty($full_name)) {
            $query->where('full_name', 'like', "%$full_name%");
        }

        if (!empty($group_id)) {
            $userIds = GroupUser::where('group_id', $group_id)->pluck('user_id')->toArray();
            $query->whereIn('id', $userIds);
        }

        if (!empty($role_name)) {
            $query->where('role_name', $role_name);
        }

        return $query;
    }
}