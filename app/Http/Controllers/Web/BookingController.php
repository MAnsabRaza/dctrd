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
use App\Models\RewardAccounting;
use App\Models\Sale;
use App\Services\PricingEngine;
use App\Services\SlotEngine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

        $categories = BookingCategory::query()
            ->whereNull('parent_id')
            ->where('status', 'active')
            ->with([
                'children' => fn ($q) => $q->where('status', 'active')->orderBy('order', 'asc'),
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
                    'id','full_name','username','role_id','role_name',
                    'avatar','avatar_settings','bio','about','cover_img','profile_secondary_image'
                ),
                'category',
                'resources'  => fn ($q) => $q->where('status', true)->orderBy('order'),
                'variants'   => fn ($q) => $q->where('status', true)->orderBy('sort_order'),
                'specifications.specification',
                'policy',
                'faqs',
                'reviews'  => fn ($q) => $q->where('status', 'active')->with('reviewer')->latest(),
                'comments' => fn ($q) => $q->where('is_active', true)->with('user')->latest(),
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
            $availableSlots = app(SlotEngine::class)->getAvailableSlots(
                $booking,
                Carbon::parse($request->get('date')),
                $resourceId
            );
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
            'user'               => $user,
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

        $request->validate([
            'date'        => 'required|date',
            'resource_id' => 'nullable|integer|exists:booking_resources,id',
        ]);

        return response()->json([
            'slots' => $slotEngine->getAvailableSlots(
                $booking,
                Carbon::parse($request->get('date')),
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

    public function directPayment(Request $request)
    {
        $user = auth()->user();

        if (empty($user) || empty(getFeaturesSettings('direct_bookings_payment_button_status'))) {
            return response()->json([], 422);
        }

        $this->validate($request, ['item_id' => 'required']);

        $data       = $request->except('_token');
        $bookingId  = $data['item_id'];
        $quantity   = (int) ($data['quantity'] ?? 1);

        $booking = Booking::query()->where('id', $bookingId)->where('status', 'published')->first();

        if (empty($booking)) {
            return response()->json([], 422);
        }

        $activeDiscount = method_exists($booking, 'getActiveDiscount') ? $booking->getActiveDiscount() : null;

        $bookingOrder = BookingOrder::updateOrCreate(
            ['booking_id' => $booking->id, 'seller_id' => $booking->creator_id, 'buyer_id' => $user->id],
            [
                'quantity'            => $quantity,
                'booking_discount_id' => !empty($activeDiscount) ? $activeDiscount->id : null,
                'status'              => BookingOrder::$pending,
                'created_at'          => time(),
            ]
        );

        Cart::updateOrCreate(
            ['creator_id' => $user->id, 'booking_order_id' => $bookingOrder->id],
            ['created_at' => time()]
        );

        return response()->json([
            'code'        => 200,
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

    private function getBookingFeaturedContents(): array
{
    $data = [];

    // Top Categories
    $data['topCategories'] = BookingTopCategory::query()
        ->with([
            'category' => fn ($q) => $q->withCount('bookings'),
        ])
        ->get();

    // Featured Categories
    $data['featuredCategories'] = BookingFeatureCategory::query()
        ->with([
            'category' => fn ($q) => $q->withCount('bookings'),
        ])
        ->get();

    // Featured Bookings — settings check skip, sirf featured=true wale lo
    $data['featuredBookings'] = Booking::query()
        ->where('status', 'published')
        ->where('featured', true)
        ->with([
            'creator' => fn ($q) => $q->select('id','full_name','role_id','username','avatar','avatar_settings','bio'),
            'category',
        ])
        ->limit(10)
        ->get();

    return $data;
}

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

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($bookingTypes) && is_array($bookingTypes)) {
            $query->whereIn('booking_type', $bookingTypes);
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
                $query->orderBy('bookings.created_at', 'desc');
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
                'id','full_name','username','bio','role_id','role_name','avatar','avatar_settings'
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