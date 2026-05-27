<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingFavorite;
use App\Models\BookingCategory;
use App\Services\PricingEngine;
use App\Services\SlotEngine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = Booking::query()
            ->where('status', 'published');

        $filterMaxPrice = ((float) deepClone($query)->max('price') + 10) * 10;

        $query = $this->handleFilters($request, $query);
        $getListData = $this->getListData($request, $query);

        if ($request->ajax()) {
            return $getListData;
        }

        $categories = BookingCategory::query()
            ->roots()
            ->active()
            ->with([
                'children' => function ($query) {
                    $query->active()->orderBy('order', 'asc');
                },
            ])
            ->get();

        $data = array_merge([
            'pageTitle' => 'Bookings',
            'pageDescription' => 'Find and book available services, places, and appointments.',
            'pageRobot' => 'index,follow',
            'bookingCategories' => $categories,
            'filterMaxPrice' => $filterMaxPrice,
        ], $getListData);

        return view('design_1.web.bookings.lists.index', $data);
    }

    public function show(Request $request, $slug)
    {
        $booking = Booking::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->with([
                'creator' => function ($query) {
                    $query->select('id', 'full_name', 'username', 'role_id', 'role_name', 'avatar', 'avatar_settings', 'bio', 'about');
                },
                'category',
                'resources' => function ($query) {
                    $query->where('status', true)->orderBy('order');
                },
                'variants' => function ($query) {
                    $query->where('status', true)->orderBy('sort_order');
                },
                'specifications.specification',
                'policy',
                'reviews' => function ($query) {
                    $query->active()->with('customer')->latest();
                },
                'comments' => function ($query) {
                    $query->where('is_active', true)->with('user')->latest();
                },
            ])
            ->first();

        if (empty($booking)) {
            abort(404);
        }

        $booking->increment('views');

        $relatedBookings = Booking::query()
            ->where('status', 'published')
            ->where('id', '!=', $booking->id)
            ->when($booking->category_id, function ($query) use ($booking) {
                $query->where('category_id', $booking->category_id);
            })
            ->with(['creator', 'category'])
            ->inRandomOrder()
            ->limit(3)
            ->get();

        $availableSlots = null;
        if ($request->filled('date')) {
            $resourceId = $request->filled('resource_id') ? (int) $request->get('resource_id') : null;

            $availableSlots = app(SlotEngine::class)->getAvailableSlots(
                $booking,
                Carbon::parse($request->get('date')),
                $resourceId
            );
        }

        $isFavorited = false;
        if (auth()->check()) {
            $isFavorited = BookingFavorite::where('user_id', auth()->id())
                ->where('booking_id', $booking->id)
                ->exists();
        }

        return view('design_1.web.bookings.show.index', [
            'pageTitle' => $booking->title,
            'pageDescription' => strip_tags((string) $booking->description),
            'pageRobot' => 'index,follow',
            'pageMetaImage' => $booking->thumbnail_url,
            'booking' => $booking,
            'relatedBookings' => $relatedBookings,
            'availableSlots' => $availableSlots,
            'isFavorited' => $isFavorited,
        ]);
    }

    public function calculatePrice(Request $request, $slug, PricingEngine $pricingEngine)
    {
        $booking = $this->getPublishedBooking($slug);

        $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults' => 'nullable|integer|min:1',
            'children' => 'nullable|integer|min:0',
        ]);

        return response()->json($pricingEngine->calculate(
            booking: $booking,
            checkIn: Carbon::parse($request->get('check_in')),
            checkOut: Carbon::parse($request->get('check_out')),
            adults: (int) $request->get('adults', 1),
            children: (int) $request->get('children', 0),
        ));
    }

    public function getSlots(Request $request, $slug, SlotEngine $slotEngine)
    {
        $booking = $this->getPublishedBooking($slug);

        $request->validate([
            'date' => 'required|date',
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

    private function getPublishedBooking(string $slug): Booking
    {
        return Booking::query()
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    }

    private function handleFilters(Request $request, Builder $query): Builder
    {
        $search = $request->get('search');
        $categoryId = $request->get('category_id');
        $bookingTypes = $request->get('booking_type');
        $options = $request->get('options');
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sort = $request->get('sort');
        $provider = $request->get('provider');

        if (!empty($search)) {
            $query->where('title', 'like', '%' . $search . '%');
        }

        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }

        if (!empty($bookingTypes) and is_array($bookingTypes)) {
            $query->whereIn('booking_type', $bookingTypes);
        }

        if (!empty($provider)) {
            $query->where('creator_id', $provider);
        }

        if (!empty($options) and is_array($options)) {
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
            $query->where(function ($query) {
                $query->whereNull('price')->orWhere('price', 0);
            });
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
                $query->orderBy('sales', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        return $query;
    }

    private function getListData(Request $request, Builder $query)
    {
        $page = $request->get('page') ?? 1;
        $count = 9;

        $cloneQuery = deepClone($query);
        $total = DB::table(DB::raw("({$cloneQuery->toSql()}) as sub"))
            ->mergeBindings($cloneQuery->getQuery())
            ->count();

        $bookings = $query->with([
            'creator' => function ($query) {
                $query->select('id', 'full_name', 'username', 'bio', 'role_id', 'role_name', 'avatar', 'avatar_settings');
            },
            'category',
        ])
            ->limit($count)
            ->offset(($page - 1) * $count)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'data' => (string) view()->make('design_1.web.bookings.components.cards.grids.index', [
                    'bookings' => $bookings,
                    'gridCardClassName' => 'col-12 col-lg-6 mt-24',
                ]),
                'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
            ]);
        }

        return [
            'bookings' => $bookings,
            'pagination' => $this->makePagination($request, $bookings, $total, $count, true),
        ];
    }
}
