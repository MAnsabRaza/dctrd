<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Exports\BookingsExport;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingOrder;
use App\Models\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking');
        removeContentLocale();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $parentCategories = BookingCategory::whereNull('parent_id')
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        $childCategories = BookingCategory::whereNotNull('parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get();

        $bookingTypes = Booking::query()
            ->select('booking_type')
            ->distinct()
            ->orderBy('booking_type')
            ->pluck('booking_type');

        $bookingTypeCategoryMap = [];
        foreach ($parentCategories as $category) {
            $bookingTypeCategoryMap[Str::slug($category->slug)]  = $category->id;
            $bookingTypeCategoryMap[Str::slug($category->title)] = $category->id;
        }

        $allCategories = BookingCategory::orderBy('order')->get();
        $userLanguages = $this->getUserLanguages();
        $instructors   = $this->getInstructors();

        return view('admin.booking.booking', [
            'pageTitle'              => trans('admin/main.create_booking'),
            'bookingPageMode'        => 'form',
            'productCategories'      => $productCategories,
            'parentCategories'       => $parentCategories,
            'childCategories'        => $childCategories,
            'bookingTypes'           => $bookingTypes,
            'bookingTypeCategoryMap' => $bookingTypeCategoryMap,
            'categories'             => $parentCategories,
            'allCategories'          => $allCategories,
            'userLanguages'          => $userLanguages,
            'instructors'            => $instructors,
        ]);
    }

    public function list(Request $request)
    {
        $this->authorize('admin_booking');

        return $this->buildBookingListView($request, false);
    }

    public function inHouseBookings(Request $request)
    {
        $this->authorize('admin_booking_in_house');

        return $this->buildBookingListView($request, true);
    }

    // ── Shared list builder ───────────────────────────────────────────
    private function buildBookingListView(Request $request, bool $inHouseOnly)
    {
        removeContentLocale();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $query = Booking::query();

        if ($inHouseOnly) {
            $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();

            $query->whereHas('creator', function ($q) use ($adminRoleIds) {
                $q->whereIn('role_id', $adminRoleIds);
            });
        }

        $topStatData = $this->getTopPageStats(deepClone($query));

        $query = $this->handleFilters($query, $request)
            ->with([
                'category',
                'creator' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
            ])
            // sales_count = confirmed/completed orders count for this booking
            ->withCount([
                'orders as sales_count' => function ($qu) {
                    $qu->whereIn('status', ['confirmed', 'completed']);
                },
            ])
            // booking_income = sum of quantity * booking price for confirmed/completed orders
            ->withSum([
                'orders as booking_income' => function ($qu) {
                    $qu->whereIn('status', ['confirmed', 'completed']);
                },
            ], 'total_amount');

        $bookings        = $query->paginate(15);
        $categories      = BookingCategory::where('status', 1)->orderBy('order')->get();
        $allCategories   = BookingCategory::orderBy('order')->get();
        $userLanguages   = $this->getUserLanguages();
        $instructors     = $this->getInstructors();
        $selectedSellers = $this->getSelectedSellers($request);

        $data = [
            'pageTitle'         => $inHouseOnly
                                    ? trans('update.in-house-bookings')
                                    : trans('admin/main.booking_list'),
            'bookingPageMode'   => 'list',
            'inHouseBookings'   => $inHouseOnly,
            'bookings'          => $bookings,
            'categories'        => $categories,
            'productCategories' => $productCategories,
            'allCategories'     => $allCategories,
            'userLanguages'     => $userLanguages,
            'instructors'       => $instructors,
            'teachers'          => $selectedSellers,
        ];

        return view('admin.booking.booking', array_merge($data, $topStatData));
    }

    // ── Export Excel ──────────────────────────────────────────────────
    public function exportExcel(Request $request)
    {
        $this->authorize('admin_booking');

        $query = Booking::query();

        if (!empty($request->get('in_house_bookings'))) {
            $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();

            $query->whereHas('creator', function ($q) use ($adminRoleIds) {
                $q->whereIn('role_id', $adminRoleIds);
            });
        }

        $bookings = $this->handleFilters($query, $request)
            ->with([
                'category',
                'creator' => function ($qu) {
                    $qu->select('id', 'full_name');
                },
            ])
            ->withCount([
                'orders as sales_count' => function ($qu) {
                    $qu->whereIn('status', ['confirmed', 'completed']);
                },
            ])
            ->withSum([
                'orders as booking_income' => function ($qu) {
                    $qu->whereIn('status', ['confirmed', 'completed']);
                },
            ], 'total_amount')
            ->get();

        return Excel::download(new BookingsExport($bookings), 'bookings.xlsx');
    }

    // ── Store ─────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('admin_booking_create');

        $this->validate($request, [
            'title'          => 'required|string|max:255',
            'category_id'    => 'nullable|exists:booking_categories,id',
            'language'       => 'nullable|string|max:10',
            'booking_type'   => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'price_per'      => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'slug'           => ['nullable', 'string', 'max:255', Rule::unique('bookings', 'slug')],
            'creator_id'     => 'nullable|exists:users,id',
            'tax'            => 'nullable|numeric|min:0|max:999.99',
            'commission'     => 'nullable|numeric|min:0|max:999.99',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        $nextOrder = (Booking::max('order') ?? 0) + 1;

        $booking = Booking::create([
            'creator_id'       => $request->creator_id ?: auth()->id(),
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'language'         => $request->language ?? app()->getLocale(),
            'slug'             => $request->slug
                                    ? Str::slug($request->slug)
                                    : Str::slug($request->title) . '-' . uniqid(),
            'booking_type'     => $request->booking_type,
            'sub_type'         => $request->sub_type,
            'description'      => $request->description,
            'requirements'     => $request->requirements,
            'thumbnail'        => $request->thumbnail,
            'cover'            => $request->cover,
            'order'            => $request->order ?: $nextOrder,

            // Pricing
            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,
            'price_unit'       => $request->price_unit,
            'discount_price'   => $request->discount_price ?: null,
            'currency'         => $request->currency ?? 'USD',
            'tax'              => $request->tax ?? 0,
            'commission'       => $request->commission ?? 0,
            'deposit_enabled'  => $request->boolean('deposit_enabled'),
            'deposit_amount'   => $request->boolean('deposit_enabled') ? ($request->deposit_amount ?: null) : null,
            'deposit_type'     => $request->boolean('deposit_enabled') ? $request->deposit_type : null,

            // Capacity
            'min_persons'      => $request->min_persons ?? 1,
            'max_persons'      => $request->max_persons ?: null,
            'max_children'     => $request->max_children ?: null,
            'children_allowed' => $request->boolean('children_allowed'),
            'capacity'         => $request->capacity ?: null,

            // Duration
            'duration_minutes'        => $request->duration_minutes ?: null,
            'buffer_before'           => $request->buffer_before ?? 0,
            'buffer_after'            => $request->buffer_after ?? 0,
            'lead_time_hours'         => $request->lead_time_hours ?? 0,
            'cutoff_time_hours'       => $request->cutoff_time_hours ?? 0,
            'instant_booking'         => $request->boolean('instant_booking'),
            'requires_approval'       => $request->boolean('requires_approval'),
            'allow_reschedule'        => $request->boolean('allow_reschedule'),
            'reschedule_before_hours' => $request->reschedule_before_hours ?? 24,
            'waitlist_enabled'        => $request->boolean('waitlist_enabled'),
            'inventory'               => $request->inventory ?: null,

            // Location
            'location_enabled' => $request->boolean('location_enabled'),
            'address_line'     => $request->address_line,
            'city'             => $request->city,
            'state'            => $request->state,
            'country'          => $request->country,
            'postal_code'      => $request->postal_code,
            'lat'              => $request->lat ?: null,
            'lng'              => $request->lng ?: null,

            // Status & misc
            'status'           => 'draft',
            'featured'         => $request->boolean('featured'),
            'forum_enabled'    => $request->boolean('forum_enabled'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'reviews_enabled'  => $request->boolean('reviews_enabled'),
            'sales'            => 0,
            'views'            => 0,
            'rating'           => 0,
            'review_count'     => 0,
            'reviewer_message' => $request->reviewer_message ?: null,
            'checkout_message' => $request->checkout_message ?: null,
            'meta'             => $this->bookingMeta($request),
        ]);

        $this->sendBookingNotification($booking, 'booking_created');

        return redirect(getAdminPanelUrl('/booking/list'))
            ->with('success', trans('admin/main.created_successfully'));
    }

    // ── Edit ──────────────────────────────────────────────────────────
    public function edit($id)
    {
        $this->authorize('admin_booking_edit');
        removeContentLocale();

        $editBooking = Booking::findOrFail($id);
        $bookings    = Booking::orderBy('created_at', 'desc')->paginate(15);

        $parentCategories = BookingCategory::whereNull('parent_id')
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        $childCategories = BookingCategory::whereNotNull('parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get();

        $bookingTypes = Booking::query()
            ->select('booking_type')
            ->distinct()
            ->orderBy('booking_type')
            ->pluck('booking_type');

        $bookingTypeCategoryMap = [];
        foreach ($parentCategories as $category) {
            $bookingTypeCategoryMap[Str::slug($category->slug)]  = $category->id;
            $bookingTypeCategoryMap[Str::slug($category->title)] = $category->id;
        }

        $allCategories = BookingCategory::orderBy('order')->get();
        $userLanguages = $this->getUserLanguages();
        $instructors   = $this->getInstructors($editBooking->creator_id);

        return view('admin.booking.booking', [
            'pageTitle'              => trans('admin/main.edit_booking'),
            'bookingPageMode'        => 'form',
            'bookings'               => $bookings,
            'editBooking'            => $editBooking,
            'parentCategories'       => $parentCategories,
            'childCategories'        => $childCategories,
            'bookingTypes'           => $bookingTypes,
            'bookingTypeCategoryMap' => $bookingTypeCategoryMap,
            'categories'             => $parentCategories,
            'allCategories'          => $allCategories,
            'userLanguages'          => $userLanguages,
            'instructors'            => $instructors,
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_edit');

        $booking = Booking::findOrFail($id);

        $this->validate($request, [
            'title'          => 'required|string|max:255',
            'category_id'    => 'nullable|exists:booking_categories,id',
            'language'       => 'nullable|string|max:10',
            'booking_type'   => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'price_per'      => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'slug'           => ['nullable', 'string', 'max:255', Rule::unique('bookings', 'slug')->ignore($booking->id)],
            'creator_id'     => 'nullable|exists:users,id',
            'tax'            => 'nullable|numeric|min:0|max:999.99',
            'commission'     => 'nullable|numeric|min:0|max:999.99',
            'deposit_amount' => 'nullable|numeric|min:0',
        ]);

        $booking->update([
            'creator_id'       => $request->creator_id ?: $booking->creator_id,
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'language'         => $request->language ?? $booking->language,
            'slug'             => $request->slug
                                    ? Str::slug($request->slug)
                                    : $booking->slug,
            'booking_type'     => $request->booking_type,
            'sub_type'         => $request->sub_type,
            'description'      => $request->description,
            'requirements'     => $request->requirements,
            'thumbnail'        => $request->thumbnail,
            'cover'            => $request->cover,
            'order'            => $request->has('order') ? $request->order : $booking->order,

            // Pricing
            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,
            'price_unit'       => $request->price_unit,
            'discount_price'   => $request->discount_price ?: null,
            'currency'         => $request->currency ?? 'USD',
            'tax'              => $request->tax ?? 0,
            'commission'       => $request->commission ?? 0,
            'deposit_enabled'  => $request->boolean('deposit_enabled'),
            'deposit_amount'   => $request->boolean('deposit_enabled') ? ($request->deposit_amount ?: null) : null,
            'deposit_type'     => $request->boolean('deposit_enabled') ? $request->deposit_type : null,

            // Capacity
            'min_persons'      => $request->min_persons ?? 1,
            'max_persons'      => $request->max_persons ?: null,
            'max_children'     => $request->max_children ?: null,
            'children_allowed' => $request->boolean('children_allowed'),
            'capacity'         => $request->capacity ?: null,

            // Duration
            'duration_minutes'        => $request->duration_minutes ?: null,
            'buffer_before'           => $request->buffer_before ?? 0,
            'buffer_after'            => $request->buffer_after ?? 0,
            'lead_time_hours'         => $request->lead_time_hours ?? 0,
            'cutoff_time_hours'       => $request->cutoff_time_hours ?? 0,
            'instant_booking'         => $request->boolean('instant_booking'),
            'requires_approval'       => $request->boolean('requires_approval'),
            'allow_reschedule'        => $request->boolean('allow_reschedule'),
            'reschedule_before_hours' => $request->reschedule_before_hours ?? 24,
            'waitlist_enabled'        => $request->boolean('waitlist_enabled'),
            'inventory'               => $request->inventory ?: null,

            // Location
            'location_enabled' => $request->boolean('location_enabled'),
            'address_line'     => $request->address_line,
            'city'             => $request->city,
            'state'            => $request->state,
            'country'          => $request->country,
            'postal_code'      => $request->postal_code,
            'lat'              => $request->lat ?: null,
            'lng'              => $request->lng ?: null,

            // Status & misc
            'status'           => 'draft',
            'featured'         => $request->boolean('featured'),
            'forum_enabled'    => $request->boolean('forum_enabled'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'reviews_enabled'  => $request->boolean('reviews_enabled'),
            'reviewer_message' => $request->reviewer_message ?: null,
            'checkout_message' => $request->checkout_message ?: null,
            'meta'             => $this->bookingMeta($request),
        ]);

        $this->sendBookingNotification($booking, 'booking_updated');

        return redirect(getAdminPanelUrl('/booking/list'))
            ->with('success', trans('admin/main.updated_successfully'));
    }

    // ── Delete ────────────────────────────────────────────────────────
    public function delete($id)
    {
        $this->authorize('admin_booking_delete');

        Booking::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/list'))
            ->with('success', trans('admin/main.deleted_successfully'));
    }

    // ── Private helpers ───────────────────────────────────────────────

    private function sendBookingNotification(Booking $booking, string $template): void
    {
        $notifyOptions = [
            '[c.title]'    => $booking->title,
            '[item_title]' => $booking->title,
            '[u.name]'     => optional(auth()->user())->full_name,
        ];

        sendNotification($template, $notifyOptions, 1);

        if (!empty($booking->creator_id) && $booking->creator_id !== auth()->id()) {
            sendNotification($template, $notifyOptions, $booking->creator_id);
        }
    }

    /**
     * Top-of-page stats — uses booking_orders only.
     *
     * booking_orders columns used:
     *   booking_id  — which booking this order is for
     *   seller_id   — seller (creator) of the booking
     *   buyer_id    — customer who placed the order
     *   status      — confirmed / completed / cancelled / pending
     */
    private function getTopPageStats($query): array
    {
        // Total bookings count
        $totalBookings = deepClone($query)->count();

        // Pluck IDs of bookings in this query scope
        $bookingIds = deepClone($query)->pluck('bookings.id');

        // Total sales = confirmed/completed orders count
        $totalBookingSales = 0;
        $totalCustomers    = 0;

        if ($bookingIds->count()) {
            $totalBookingSales = BookingOrder::whereIn('booking_id', $bookingIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->count();

            // Unique customers (buyer_id) across those orders
            $totalCustomers = BookingOrder::whereIn('booking_id', $bookingIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->whereNotNull('buyer_id')
                ->distinct('buyer_id')
                ->count('buyer_id');
        }

        // Total unique sellers (creators) in this booking scope
        $totalSellers = deepClone($query)
            ->whereNotNull('creator_id')
            ->distinct('creator_id')
            ->count('creator_id');

        return [
            'totalBookings'         => $totalBookings,
            'totalBookingSales'     => $totalBookingSales,
            'totalBookingSellers'   => $totalSellers,
            'totalBookingCustomers' => $totalCustomers,
        ];
    }

    /**
     * Query filters — sort cases now use booking_orders instead of booking_order_items.
     */
    private function handleFilters($query, Request $request)
    {
        $from        = $request->get('from', null);
        $to          = $request->get('to', null);
        $title       = $request->get('title', null);
        $creatorIds  = $request->get('creator_ids', null);
        $categoryId  = $request->get('category_id', null);
        $bookingType = $request->get('booking_type', null);
        $status      = $request->get('status', null);
        $sort        = $request->get('sort', null);

        $query = fromAndToDateFilter($from, $to, $query, 'created_at');

        if (!empty($title)) {
            $query->where('title', 'like', '%' . $title . '%');
        }

        if (!empty($creatorIds) && is_array($creatorIds) && count($creatorIds)) {
            $query->whereIn('creator_id', $creatorIds);
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($bookingType)) {
            $query->where('booking_type', $bookingType);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        switch ($sort) {
            case 'sales_asc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', ['confirmed', 'completed']);
                    })
                    ->select('bookings.*', DB::raw('count(booking_orders.id) as orders_count'))
                    ->groupBy('bookings.id')
                    ->orderBy('orders_count', 'asc');
                break;

            case 'sales_desc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', ['confirmed', 'completed']);
                    })
                    ->select('bookings.*', DB::raw('count(booking_orders.id) as orders_count'))
                    ->groupBy('bookings.id')
                    ->orderBy('orders_count', 'desc');
                break;

            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            case 'income_asc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', ['confirmed', 'completed']);
                    })
                    ->select('bookings.*', DB::raw('coalesce(sum(booking_orders.total_amount), 0) as income_amount'))
                    ->groupBy('bookings.id')
                    ->orderBy('income_amount', 'asc');
                break;

            case 'income_desc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', ['confirmed', 'completed']);
                    })
                    ->select('bookings.*', DB::raw('coalesce(sum(booking_orders.total_amount), 0) as income_amount'))
                    ->groupBy('bookings.id')
                    ->orderBy('income_amount', 'desc');
                break;

            case 'created_at_asc':
                $query->orderBy('bookings.created_at', 'asc');
                break;

            case 'updated_at_asc':
                $query->orderBy('bookings.updated_at', 'asc');
                break;

            case 'updated_at_desc':
                $query->orderBy('bookings.updated_at', 'desc');
                break;

            case 'created_at_desc':
            default:
                $query->orderBy('bookings.created_at', 'desc');
                break;
        }

        return $query;
    }

    private function getSelectedSellers(Request $request)
    {
        $creatorIds = $request->get('creator_ids', []);

        if (empty($creatorIds) || !is_array($creatorIds)) {
            return collect();
        }

        return User::query()
            ->select('id', 'full_name')
            ->whereIn('id', $creatorIds)
            ->get();
    }

    private function getUserLanguages(): array
    {
        $userLanguages = getGeneralSettings('user_languages');

        if (!empty($userLanguages) && is_array($userLanguages)) {
            return getLanguages($userLanguages);
        }

        return [app()->getLocale() => ucfirst(app()->getLocale())];
    }

    private function bookingMeta(Request $request): array
    {
        return [
            'reward_points'        => $request->reward_points,
            'commission_type'      => $request->commission_type ?? 'percent',
            'seo_meta_description' => $request->seo_meta_description,
            'tags'                 => collect(explode(',', (string) $request->tags))
                                        ->map(fn ($tag) => trim($tag))
                                        ->filter()
                                        ->take(10)
                                        ->values()
                                        ->all(),
            'time_zone'            => $request->time_zone ?? 'America/New_York',
        ];
    }

    private function getInstructors(?int $selectedUserId = null)
    {
        return User::query()
            ->select('id', 'full_name', 'role_name')
            ->where(function ($query) use ($selectedUserId) {
                $query->whereIn('role_name', [Role::$teacher, Role::$organization]);

                if (!empty($selectedUserId)) {
                    $query->orWhere('id', $selectedUserId);
                }
            })
            ->orderBy('full_name')
            ->get();
    }
}