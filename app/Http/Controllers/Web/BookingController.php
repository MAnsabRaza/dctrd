<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\CheckContentLimitationTrait;
use App\Mixins\Logs\VisitLogMixin;
use App\Models\AdvertisingBanner;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingFavorite;
use App\Models\BookingFeatureCategory;
use App\Models\BookingOrder;
use App\Models\BookingTopCategory;
use App\Models\Cart;
use App\Models\Comment;
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Services\PricingEngine;
use App\Services\SlotEngine;
use App\Services\Calendar\CalendarSyncService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Services\CustomerGroupAccessService;

class BookingController extends Controller
{
    use CheckContentLimitationTrait;

    /* ══════════════════════════════════════════════
       INDEX  —  Listing page
    ══════════════════════════════════════════════ */

    public function index(Request $request)
    {
        $query = Booking::query()->where('status', 'published');

        $filterMaxPrice = ((float) deepClone($query)->max('price') + 10) * 10;

        $query      = $this->handleFilters($request, $query);
        $getListData = $this->getListData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        // NOTE: BookingCategory casts `status` as boolean (1/0 in the DB).
        // Filtering with the string 'active' (as before) never matches any
        // row, so parent categories (and their children) silently vanished
        // from every filter. We now use the model's own active()/roots()
        // scopes, which compare against boolean true correctly.
        $categories = BookingCategory::query()
            ->roots()
            ->active()
            ->with([
                'children' => fn ($q) => $q->active()->orderBy('order', 'asc'),
            ])
            ->get();

        $categoryId = $request->get('category_id', null);

        $seoSettings      = getSeoMetas('bookings_lists');
        $pageTitle        = $seoSettings['title']       ?? 'Bookings';
        $pageDescription  = $seoSettings['description'] ?? 'Find and book services, places, and appointments.';
        $pageRobot        = getPageRobot('bookings_lists');

        $data = array_merge([
            'pageTitle'              => $pageTitle,
            'pageDescription'        => $pageDescription,
            'pageRobot'              => $pageRobot,
            'bookingCategories'      => $categories,
            'filterMaxPrice'         => $filterMaxPrice,
            'seoSettings'            => $seoSettings,
            'pageBottomSeoContent'   => $this->getPageBottomSeoContent($categoryId),
        ], $getListData, $this->getBookingFeaturedContents());

        return view('design_1.web.bookings.lists.index', $data);
    }

    /* ══════════════════════════════════════════════
       SHOW  —  Single booking detail page
    ══════════════════════════════════════════════ */

    public function show(Request $request, $slug)
    {
        $user = auth()->check() ? auth()->user() : null;

        $contentLimitation = $this->checkContentLimitation($user, true);
        if ($contentLimitation !== 'ok') {
            return $contentLimitation;
        }

        $booking = Booking::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'creator' => fn ($q) => $q->select(
                    'id', 'full_name', 'username', 'role_id', 'role_name',
                    'avatar', 'avatar_settings', 'bio', 'about', 'cover_img', 'profile_secondary_image'
                ),
                'category',
                'resources' => fn ($q) => $q->where('status', true)->orderBy('order'),
                'variants'  => fn ($q) => $q->where('status', true)->orderBy('sort_order'),
                'policy',
                'faqs',
                'reviews'  => fn ($q) => $q->where('status', 'active')->latest(),
            ])
            ->first();

        if (empty($booking)) {
            abort(404);
        }

        $booking->increment('views');

        /* ── Related bookings ── */
        $relatedBookings = Booking::query()
            ->where('status', 'published')
            ->where('id', '!=', $booking->id)
            ->when($booking->category_id, fn ($q) => $q->where('category_id', $booking->category_id))
            ->with(['creator', 'category'])
            ->inRandomOrder()
            ->limit(3)
            ->get();

        /* ── Provider extra data ── */
        $provider = $booking->creator;
        if (!empty($provider)) {
            $provider->someRandomBookings = Booking::query()
                ->where('creator_id', $provider->id)
                ->where('id', '!=', $booking->id)
                ->where('status', 'published')
                ->inRandomOrder()
                ->limit(3)
                ->with(['category'])
                ->get();
        }

        /* ── Available slots (if date passed) ── */
        $availableSlots = null;
        if ($request->filled('date')) {
            $resourceId = $request->filled('resource_id') ? (int) $request->get('resource_id') : null;
            $resourceRequired = $booking->resources->isNotEmpty();

            if (!$resourceRequired || !empty($resourceId)) {
                $availableSlots = app(SlotEngine::class)->getAvailableSlots(
                    $booking,
                    Carbon::parse($request->get('date'), function_exists('getGeneralSettings') ? (getGeneralSettings('default_time_zone') ?: config('app.timezone')) : config('app.timezone')),
                    $resourceId
                );
            }
        }

        /* ── Favourite state ── */
        $isFavorited = false;
        if (!empty($user)) {
            $isFavorited = BookingFavorite::where('user_id', $user->id)
                ->where('booking_id', $booking->id)
                ->exists();
        }

        /* ── Advertising banners ── */
        $advertisingBanners = AdvertisingBanner::where('published', true)
            ->whereIn('position', ['booking_show'])
            ->get();

        /* ── Visit log ── */
        $visitLogMixin = new VisitLogMixin();
        $visitLogMixin->storeVisit($request, $booking->creator_id, $booking->id, 'booking');

        /* ── Active special offer / discount ── */
        $activeSpecialOffer = method_exists($booking, 'getActiveDiscount')
            ? $booking->getActiveDiscount()
            : null;

        $bookingReviews = $this->getBookingReviews($booking);
        $bookingComments = $this->getBookingComments($booking);

        $pageRobot = getPageRobot('booking_show');

        return view('design_1.web.bookings.show.index', [
            'pageTitle'          => $booking->title,
            'pageDescription'    => strip_tags((string) $booking->description),
            'pageRobot'          => $pageRobot,
            'pageMetaImage'      => $booking->thumbnail_url,
            'booking'            => $booking,
            'relatedBookings'    => $relatedBookings,
            'availableSlots'     => $availableSlots,
            'isFavorited'        => $isFavorited,
            'provider'           => $provider,
            'advertisingBanners' => $advertisingBanners,
            'activeSpecialOffer' => $activeSpecialOffer,
            'bookingReviews'     => $bookingReviews,
            'bookingComments'    => $bookingComments,
            'authUser'           => $user,
            'user'               => $user,
        ]);
    }

    private function getBookingReviews(Booking $booking): array
    {
        $count = 10;

        $query = $booking->reviews()
            ->where('status', 'active')
            ->with([
                'comments' => fn ($query) => $query->where('status', 'active')->with('user'),
                'creator' => fn ($query) => $query->select('id', 'username', 'full_name', 'role_id', 'role_name', 'avatar', 'avatar_settings'),
            ])
            ->orderBy('created_at', 'desc');

        $total = $query->count();

        return [
            'reviews' => $query->limit($count)->get(),
            'has_more' => $total > $count,
            'reviews_count' => $total,
        ];
    }

    private function getBookingComments(Booking $booking): array
    {
        $count = 10;

        $query = Comment::query()
            ->where('booking_id', $booking->id)
            ->where('status', 'active')
            ->whereNull('reply_id')
            ->whereNull('review_id')
            ->whereNull('product_review_id')
            ->whereNull('booking_review_id')
            ->with([
                'user' => fn ($query) => $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings'),
                'replies' => function ($query) {
                    $query->where('status', 'active');
                    $query->with([
                        'user' => fn ($query) => $query->select('id', 'full_name', 'role_name', 'role_id', 'username', 'avatar', 'avatar_settings'),
                    ]);
                },
            ])
            ->orderBy('created_at', 'desc');

        $total = $query->count();

        return [
            'comments' => $query->limit($count)->get(),
            'comments_count' => $total,
            'has_more' => $total > $count,
        ];
    }

    public function checkAvailability(Request $request, $slug, SlotEngine $slotEngine)
{
    $booking = $this->getPublishedBooking($slug);
    $resourceRequired = $booking->resources()->where('status', true)->exists();

    $request->validate([
        'date'        => 'required|date',
        'start_time'  => 'required',
        'resource_id' => [
            $resourceRequired ? 'required' : 'nullable',
            'integer',
            Rule::exists('booking_resources', 'id')->where(function ($query) use ($booking) {
                $query->where('booking_id', $booking->id)->where('status', true);
            }),
        ],
    ]);

    $timezone = function_exists('getGeneralSettings') ? (getGeneralSettings('default_time_zone') ?: config('app.timezone')) : config('app.timezone');
    $date = Carbon::parse($request->get('date'), $timezone);
    $startTime = $request->get('start_time');
    $resourceId = $request->filled('resource_id') ? (int) $request->get('resource_id') : null;

    $durationMinutes = (int) ($booking->duration_minutes ?? 60);
    $endTime = Carbon::parse($request->get('date') . ' ' . $startTime, $timezone)
        ->addMinutes($durationMinutes)
        ->format('H:i');

    $isAvailable = $slotEngine->isSlotAvailable($booking, $date, $startTime, $endTime, $resourceId);

    return response()->json([
        'available' => $isAvailable,
        'message' => $isAvailable
            ? ('This slot is available.')
            : ( 'This time slot is no longer available. Please select another slot.'),
    ]);
}
    /* ══════════════════════════════════════════════
       CALCULATE PRICE  (AJAX)
    ══════════════════════════════════════════════ */

    public function calculatePrice(Request $request, $slug, PricingEngine $pricingEngine)
    {
        $booking = $this->getPublishedBooking($slug);

        $request->validate([
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults'    => 'nullable|integer|min:1',
            'children'  => 'nullable|integer|min:0',
        ]);

        return response()->json($pricingEngine->calculate(
            booking:  $booking,
            checkIn:  Carbon::parse($request->get('check_in')),
            checkOut: Carbon::parse($request->get('check_out')),
            adults:   (int) $request->get('adults', 1),
            children: (int) $request->get('children', 0),
        ));
    }

    /* ══════════════════════════════════════════════
       GET SLOTS  (AJAX)
    ══════════════════════════════════════════════ */

   public function getSlots(Request $request, $slug, SlotEngine $slotEngine)
{
    $booking = $this->getPublishedBooking($slug);
    $resourceRequired = $booking->resources()->where('status', true)->exists();

    $request->validate([
        'date'        => 'required|date',
        'resource_id' => [
            $resourceRequired ? 'required' : 'nullable',
            'integer',
            Rule::exists('booking_resources', 'id')->where(function ($query) use ($booking) {
                $query->where('booking_id', $booking->id)->where('status', true);
            }),
        ],
    ]);

    // ✅ FIX: timezone missing thi yahan, baqi controller methods (show, checkAvailability,
    // directPayment) ki tarah business timezone explicitly pass karo, warna Carbon::parse()
    // server ki default (app.timezone) mein date resolve karta hai jo business tz se mismatch ho sakti hai.
    $timezone = function_exists('getGeneralSettings')
        ? (getGeneralSettings('default_time_zone') ?: config('app.timezone'))
        : config('app.timezone');

    return response()->json([
        'slots' => $slotEngine->getAvailableSlots(
            $booking,
            Carbon::parse($request->get('date'), $timezone),
            $request->filled('resource_id') ? (int) $request->get('resource_id') : null
        ),
    ]);
}

    /* ══════════════════════════════════════════════
       BUY WITH POINT
    ══════════════════════════════════════════════ */

    public function buyWithPoint(Request $request, $slug)
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        $user    = auth()->user();
        $data    = $request->all();
        $booking = Booking::where('slug', $slug)->where('status', 'published')->first();

        if (empty($booking) || ($data['item_id'] ?? null) != $booking->id) {
            abort(404);
        }

        if (empty($booking->point)) {
            return back()->with(['toast' => [
                'title' => '', 'msg' => trans('update.can_not_buy_this_booking_with_point'), 'status' => 'error',
            ]]);
        }

        $quantity        = (int) ($data['quantity'] ?? 1);
        $availablePoints = $user->getRewardPoints();
        $needPoints      = $booking->point * $quantity;

        if ($availablePoints < $needPoints) {
            return back()->with(['toast' => [
                'title' => '', 'msg' => trans('update.you_have_no_enough_points_for_this_product'), 'status' => 'error',
            ]]);
        }

        $bookingOrder = BookingOrder::create([
            'booking_id' => $booking->id,
            'seller_id'  => $booking->creator_id,
            'buyer_id'   => $user->id,
            'quantity'   => $quantity,
            'status'     => BookingOrder::$pending,
            'created_at' => time(),
        ]);

        $sale = Sale::create([
            'buyer_id'         => $user->id,
            'seller_id'        => $booking->creator_id,
            'booking_order_id' => $bookingOrder->id,
            'type'             => 'booking',
            'payment_method'   => Sale::$credit,
            'amount'           => 0,
            'total_amount'     => 0,
            'created_at'       => time(),
        ]);

        $bookingOrder->update([
            'sale_id' => $sale->id,
            'status'  => BookingOrder::$success,
        ]);

        app(CalendarSyncService::class)->syncBooking($bookingOrder->fresh(), 'create');

        \App\Models\OrderItem::create([
            'user_id'          => $user->id,
            'booking_order_id' => $bookingOrder->id,
            'quantity'         => $quantity,
            'amount'           => 0,
            'total_amount'     => 0,
            'created_at'       => time(),
        ]);

        RewardAccounting::makeRewardAccounting(
            $user->id, $needPoints, 'withdraw', null, false, RewardAccounting::DEDUCTION
        );

        return back()->with(['toast' => [
            'title' => '', 'msg' => trans('update.success_pay_product_with_point_msg'), 'status' => 'success',
        ]]);
    }

    /* ══════════════════════════════════════════════
       DIRECT PAYMENT  (AJAX)
    ══════════════════════════════════════════════ */

    public function directPayment(Request $request, SlotEngine $slotEngine)
    {
        $user = auth()->user();

        if (empty($user)) {
            $request->session()->put('url.intended', $request->headers->get('referer', url()->previous()));

            return response()->json([
                'message' => 'Please login before adding this booking to cart.',
                'redirect_to' => '/login',
            ], 401);
        }

        $itemValidator = Validator::make($request->all(), [
            'item_id' => ['required', 'integer'],
        ], [
            'item_id.required' => 'Booking item is required.',
        ]);

        if ($itemValidator->fails()) {
            return response()->json([
                'message' => 'Please fix the highlighted fields.',
                'errors' => $itemValidator->errors(),
            ], 422);
        }

        $data       = $request->except('_token');
        $bookingId  = (int) $data['item_id'];
        $quantity   = (int) ($data['quantity'] ?? 1);

       $booking = Booking::query()->where('id', $bookingId)->where('status', 'published')->first();

if (empty($booking)) {
    return response()->json([
        'message' => 'The selected booking is no longer available.',
        'errors' => [
            'item_id' => ['The selected booking is no longer available.'],
        ],
    ], 422);
}

// 👇 YE NAYA BLOCK ADD KARO
$customerGroupService = app(CustomerGroupAccessService::class);

if (!$customerGroupService->canPurchase($booking, $user)) {
    return response()->json([
        'message' => $customerGroupService->denialMessage($booking),
        'errors' => [
            'item_id' => [$customerGroupService->denialMessage($booking)],
        ],
    ], 422);
}
// 👆 YAHAN TAK

$resourceRequired = $booking->resources()->where('status', true)->exists();
        $validator = Validator::make($request->all(), [
            'slot_date' => ['required', 'date'],
            'slot_start' => ['required', 'date_format:H:i'],
            'slot_end' => ['required', 'date_format:H:i'],
            'quantity' => ['required', 'integer', 'min:1'],
            'resource_id' => [
                $resourceRequired ? 'required' : 'nullable',
                'integer',
                Rule::exists('booking_resources', 'id')->where(function ($query) use ($booking) {
                    $query->where('booking_id', $booking->id)->where('status', true);
                }),
            ],
        ], [
            'slot_date.required' => 'Please select a booking date.',
            'slot_start.required' => 'Please select an available slot.',
            'slot_end.required' => 'Please select an available slot.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'resource_id.required' => 'Please select a resource.',
            'resource_id.exists' => 'The selected resource is not available for this booking.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Please fix the highlighted fields.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $activeDiscount = method_exists($booking, 'getActiveDiscount') ? $booking->getActiveDiscount() : null;
        $timezone = function_exists('getGeneralSettings') ? (getGeneralSettings('default_time_zone') ?: config('app.timezone')) : config('app.timezone');
        $slotDateCarbon = Carbon::parse($request->input('slot_date'), $timezone);
        if ($slotDateCarbon->lt(Carbon::now($timezone)->startOfDay())) {
            return response()->json([
                'message' => 'Please select a current or future booking date.',
                'errors' => [
                    'slot_date' => ['Please select a current or future booking date.'],
                ],
            ], 422);
        }

        $slotDate = $slotDateCarbon->toDateString();
        $slotStart = $request->input('slot_start');
        $slotEnd = $request->input('slot_end');
        $resourceId = $request->filled('resource_id') ? (int) $request->input('resource_id') : null;

        $payloadHash = md5(json_encode([
            'user_id' => $user->id,
            'session_id' => $request->session()->getId(),
            'booking_id' => $booking->id,
            'resource_id' => $resourceId,
            'slot_date' => $slotDate,
            'slot_start' => $slotStart,
            'slot_end' => $slotEnd,
            'quantity' => $quantity,
        ]));

        $lock = Cache::lock("booking_cart_submit:{$payloadHash}", 10);

        try {
            $bookingOrder = $lock->block(5, function () use ($booking, $user, $activeDiscount, $quantity, $slotEngine, $slotDate, $slotStart, $slotEnd, $resourceId) {
                $existingOrderQuery = BookingOrder::query()
                    ->where('booking_id', $booking->id)
                    ->where('seller_id', $booking->creator_id)
                    ->where('buyer_id', $user->id)
                    ->where('booking_date', $slotDate)
                    ->where('start_time', $slotStart)
                    ->where('end_time', $slotEnd)
                    ->where('quantity', $quantity)
                    ->where('status', BookingOrder::$pending);

                if ($resourceId) {
                    $existingOrderQuery->where('resource_id', $resourceId);
                } else {
                    $existingOrderQuery->whereNull('resource_id');
                }

                $existingOrder = $existingOrderQuery->first();

                if ($existingOrder) {
                    Cart::updateOrCreate(
                        ['creator_id' => $user->id, 'booking_order_id' => $existingOrder->id],
                        ['created_at' => time()]
                    );

                    return $existingOrder;
                }

                if (!$slotEngine->isSlotAvailable($booking, Carbon::parse($slotDate), $slotStart, $slotEnd, $resourceId)) {
                    return null;
                }

                return DB::transaction(function () use ($booking, $user, $activeDiscount, $quantity, $slotDate, $slotStart, $slotEnd, $resourceId) {
                    $bookingOrder = BookingOrder::create([
                        'booking_id'          => $booking->id,
                        'seller_id'           => $booking->creator_id,
                        'buyer_id'            => $user->id,
                        'resource_id'         => $resourceId,
                        'booking_date'        => $slotDate,
                        'start_time'          => $slotStart,
                        'end_time'            => $slotEnd,
                        'quantity'            => $quantity,
                        'booking_discount_id' => !empty($activeDiscount) ? $activeDiscount->id : null,
                        'status'              => BookingOrder::$pending,
                        'created_at'          => time(),
                    ]);

                    Cart::updateOrCreate(
                        ['creator_id' => $user->id, 'booking_order_id' => $bookingOrder->id],
                        ['created_at' => time()]
                    );

                    return $bookingOrder;
                });
            });
        } finally {
            optional($lock)->release();
        }

        if (!$bookingOrder) {
            return response()->json([
                'message' => 'This time slot is no longer available. Please select another slot.',
                'errors' => [
                    'slot_start' => ['This time slot is no longer available. Please select another slot.'],
                ],
            ], 422);
        }

        return response()->json([
            'code'        => 200,
            'status'      => 'success',
            'title'       => trans('cart.cart_add_success_title'),
            'msg'         => trans('cart.cart_add_success_msg'),
            'redirect_to' => '/cart',
        ]);
    }

    /* ══════════════════════════════════════════════
       FAVOURITE TOGGLE  (AJAX)
    ══════════════════════════════════════════════ */

    public function favoriteToggle(Request $request, $slug)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $booking = $this->getPublishedBooking($slug);
        $userId  = auth()->id();

        $favorite = BookingFavorite::where('user_id', $userId)
            ->where('booking_id', $booking->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            $status = 'removed';
        } else {
            BookingFavorite::create(['user_id' => $userId, 'booking_id' => $booking->id]);
            $status = 'added';
        }

        return response()->json(['status' => $status]);
    }

    /* ══════════════════════════════════════════════
       PRIVATE HELPERS
    ══════════════════════════════════════════════ */

    private function getPublishedBooking(string $slug): Booking
    {
        return Booking::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    private function getPageBottomSeoContent($categoryId = null)
    {
        if (!empty($categoryId)) {
            $category = BookingCategory::query()->where('id', $categoryId)->first();

            if (!empty($category) && !empty($category->bottom_seo_title) && !empty($category->bottom_seo_description)) {
                return [
                    'title'       => $category->bottom_seo_title,
                    'description' => $category->bottom_seo_description,
                ];
            }
        } else {
            $seoSettings = getSeoMetas('bookings_lists');

            if (!empty($seoSettings['bottom_seo_title']) && !empty($seoSettings['bottom_seo_content'])) {
                return [
                    'title'       => $seoSettings['bottom_seo_title'],
                    'description' => $seoSettings['bottom_seo_content'],
                ];
            }
        }

        return null;
    }

    /**
     * Booking Type filter now sends real BookingCategory ids (parent and/or
     * child). If a parent id is selected, we also include all of its
     * children ids, since bookings are normally attached to a leaf/child
     * category rather than the parent itself.
     */
    private function expandCategoryIdsWithChildren(array $ids): array
    {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));

        if (empty($ids)) {
            return [];
        }

        $childIds = BookingCategory::query()
            ->whereIn('parent_id', $ids)
            ->active()
            ->pluck('id')
            ->toArray();

        return array_values(array_unique(array_merge($ids, $childIds)));
    }

    private function getBookingFeaturedContents(): array
    {
        $data = [];

        $data['topCategories'] = BookingTopCategory::query()
            ->with([
                'category' => fn ($q) => $q->withCount('bookings'),
            ])
            ->get();

        $data['featuredCategories'] = BookingFeatureCategory::query()
            ->with([
                'category' => fn ($q) => $q->withCount('bookings'),
            ])
            ->get();

        $data['featuredBookings'] = Booking::query()
            ->where('status', 'published')
            ->where('featured', true)
            ->with([
                'creator' => fn ($q) => $q->select('id', 'full_name', 'role_id', 'username', 'avatar', 'avatar_settings', 'bio'),
                'category',
            ])
            ->limit(10)
            ->get();

        return $data;
    }

    /**
     * NOTE ON MILESTONE 3.4 (Nearby / Spatial filter):
     * Previously the `default` sort case always applied
     * `orderBy('bookings.created_at', 'desc')`, even when a lat/lng radius
     * search was active and no explicit `sort` was requested. Because SQL
     * applies ORDER BY clauses in the order they're added, the results ended
     * up sorted primarily by created_at, and the distance order-by was
     * effectively ignored. The radius WHERE filter did work, but the
     * "closest first" sorting did not.
     *
     * Fix: lat/lng + radius are now read up-front, and we only fall back to
     * the default created_at sort when we are NOT about to sort by distance.
     */
    private function handleFilters(Request $request, Builder $query): Builder
    {
        $search       = $request->get('search');
        $categoryId   = $request->get('category_id');
        $bookingTypes = $request->get('booking_type');
        $options      = $request->get('options');
        $minPrice     = $request->get('min_price');
        $maxPrice     = $request->get('max_price');
        $sort         = $request->get('sort');
        $provider     = $request->get('provider');
        $lat          = $request->get('lat');
        $lng          = $request->get('lng');
        $radius       = (float) ($request->get('radius', 25)); // km, default 25

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($bookingTypes) && is_array($bookingTypes)) {
            // $bookingTypes now holds BookingCategory ids (parent and/or
            // child) coming from the "Booking Type" checkbox tree, which is
            // rendered from the real admin categories in the database.
            $bookingTypeCategoryIds = $this->expandCategoryIdsWithChildren($bookingTypes);

            if (!empty($bookingTypeCategoryIds)) {
                $query->whereIn('category_id', $bookingTypeCategoryIds);
            }
        }

        if (!empty($provider)) {
            $query->where('creator_id', $provider);
        }

        if (!empty($options) && is_array($options)) {
            if (in_array('featured', $options)) {
                $query->where('featured', true);
            }
            if (in_array('instant_booking', $options)) {
                $query->where('instant_booking', true);
            }
            if (in_array('location_enabled', $options)) {
                $query->where('location_enabled', true);
            }
        }

        if ($request->get('free') === 'on') {
            $query->where(fn ($q) => $q->whereNull('price')->orWhere('price', 0));
        }

        if (!empty($minPrice)) {
            $query->where('price', '>=', $minPrice);
        }

        if (!empty($maxPrice)) {
            $query->where('price', '<=', $maxPrice);
        }

       $hasLocationFilter  = $lat !== null && $lat !== '' && $lng !== null && $lng !== '';
        $willSortByDistance = $hasLocationFilter && empty($sort);

        switch ($sort) {
            case 'expensive':
                $query->orderBy('price', 'desc');
                break;
            case 'inexpensive':
                $query->orderBy('price', 'asc');
                break;
            case 'best_rates':
                $query->orderBy('rating', 'desc')->orderBy('review_count', 'desc');
                break;
            case 'bestsellers':
                $query->leftJoin('booking_orders', function ($join) {
                    $join->on('bookings.id', '=', 'booking_orders.booking_id')
                        ->whereNotNull('booking_orders.sale_id')
                        ->whereNotIn('booking_orders.status', [BookingOrder::$canceled, BookingOrder::$pending]);
                })
                ->select('bookings.*', DB::raw('COUNT(booking_orders.id) as sales_count'))
                ->groupBy('bookings.id')
                ->orderBy('sales_count', 'desc');
                break;
            default:
                // Only fall back to created_at DESC when we're not about
                // to order the results by distance instead.
                if (!$willSortByDistance) {
                    $query->orderBy('bookings.created_at', 'desc');
                }
        }

        if ($hasLocationFilter) {
            $lat = (float) $lat;
            $lng = (float) $lng;

            // Haversine formula — distance in km
            $haversine = "(
                6371 * ACOS(
                    COS(RADIANS($lat))
                    * COS(RADIANS(bookings.lat))
                    * COS(RADIANS(bookings.lng) - RADIANS($lng))
                    + SIN(RADIANS($lat))
                    * SIN(RADIANS(bookings.lat))
                )
            )";

            $query
                ->whereNotNull('bookings.lat')
                ->whereNotNull('bookings.lng')
                ->whereRaw("{$haversine} <= ?", [$radius])
                ->selectRaw("bookings.*, {$haversine} AS distance_km");

            if ($willSortByDistance) {
                $query->orderByRaw('distance_km ASC');
            }
        }

        return $query;
    }

    private function getListData(Request $request, Builder $query)
    {
        $page  = $request->get('page') ?? 1;
        $count = 9;

        $cloneQuery = deepClone($query);
        $total = DB::table(DB::raw("({$cloneQuery->toSql()}) as sub"))
            ->mergeBindings($cloneQuery->getQuery())
            ->count();

        $bookings = $query->with([
            'creator' => fn ($q) => $q->select(
                'id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings'
            ),
            'category',
        ])
        ->limit($count)
        ->offset(($page - 1) * $count)
        ->get();

        /* isFavorited flag attach karo */
        if (auth()->check() && $bookings->count()) {
            $favoriteIds = BookingFavorite::where('user_id', auth()->id())
                ->whereIn('booking_id', $bookings->pluck('id')->toArray())
                ->pluck('booking_id')
                ->toArray();

            foreach ($bookings as $booking) {
                $booking->isFavorited = in_array($booking->id, $favoriteIds);
            }
        }

        if ($request->ajax()) {
            return $this->getAjaxResponse($request, $bookings, $total, $count);
        }

        return [
            'bookings'   => $bookings,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ];
    }

    private function getAjaxResponse(Request $request, $bookings, int $total, int $count)
    {
        $categoryId      = $request->get('category_id', null);
        $specificContent = null;

        if (!empty($categoryId)) {
            $pageBottomSeoContent = $this->getPageBottomSeoContent($categoryId);

            $specificContent = [
                'el'   => '.js-page-bottom-seo-content',
                'html' => (!empty($pageBottomSeoContent['title']) && !empty($pageBottomSeoContent['description']))
                    ? (string) view()->make(
                        'design_1.web.bookings.lists.includes.bottom_seo_content',
                        ['seoContent' => $pageBottomSeoContent]
                      )
                    : null,
            ];
        }

        $html = (string) view()->make('design_1.web.bookings.components.cards.grids.index', [
            'bookings'         => $bookings,
            'gridCardClassName' => 'col-12 col-lg-6 mt-24',
        ]);

        return response()->json([
            'data'             => $html,
            'pagination'       => $this->makePagination($request, $bookings, $total, $count, true),
            'specific_content' => $specificContent,
        ]);
    }
}
