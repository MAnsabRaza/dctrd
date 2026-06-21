<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Services\PricingEngine;
use App\Services\SlotEngine;
use App\Services\NightlyAvailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Panel\Booking\Traits\MyBookingsListsTrait;

class BookingController extends Controller
{
     use MyBookingsListsTrait;
    protected $pricingEngine;
    protected $slotEngine;
    protected $nightlyAvailability;

    public function __construct(
        PricingEngine $pricingEngine,
        SlotEngine $slotEngine,
        NightlyAvailability $nightlyAvailability
    ) {
        $this->pricingEngine = $pricingEngine;
        $this->slotEngine = $slotEngine;
        $this->nightlyAvailability = $nightlyAvailability;
    }

    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

   public function index(Request $request)
{
    $this->authorize('panel_bookings');

    $user = auth()->user();

    $query = Booking::query()->where('creator_id', $user->id);
    $query = $this->handleFilters($request, $query);

    $pageListData = $this->getPageListData($request, $query);

    if ($request->ajax()) {
        return $pageListData;
    }

    $topStats = $this->handlePageTopStats($user);

    $pageTitle = trans('panel.my_bookings');
    $breadcrumbs = [
        ['text' => trans('update.platform'), 'url' => '/'],
        ['text' => trans('panel.dashboard'), 'url' => '/panel'],
        ['text' => $pageTitle, 'url' => null],
    ];

    $data = [
        'pageTitle' => $pageTitle,
        'breadcrumbs' => $breadcrumbs,
        ...$topStats,
        ...$pageListData,
    ];

    return view('design_1.panel.bookings.my_bookings.index', $data);
}

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $user = auth()->user();
        $user->loadMissing('userMetas');

        foreach ($user->userMetas as $meta) {
            $user->{$meta->name} = $meta->value;
        }

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.create.index', [

            'pageTitle' => 'New Booking',

            'allCategoryLists' => $allCategoryLists,

            'booking' => null,

            'userLanguages' => $this->getUserLanguages(),

            'bookingDefaults' => [
                'currency' => $user->booking_default_currency ?? $user->currency ?? 'USD',
                'price_unit' => $user->booking_default_price_unit ?? 'booking',
                'status' => !empty($user->booking_auto_publish) ? 'published' : 'draft',
                'location_enabled' => !empty($user->booking_location_enabled),
            ],
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $data = $this->validateBooking($request);

        $data['creator_id'] = auth()->id();

        $booking = Booking::create($data);

        $this->sendBookingNotification($booking, 'booking_created');

        return redirect()
            ->route('panel.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    public function edit($id)
    {
        $booking = $this->findOwnBooking($id);

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.create.index', [

            'pageTitle' => 'Edit Booking',

            'booking' => $booking,

            'allCategoryLists' => $allCategoryLists,

            'userLanguages' => $this->getUserLanguages(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $booking = $this->findOwnBooking($id);

        $data = $this->validateBooking($request, $booking->id);

        $booking->update($data);

        $this->sendBookingNotification($booking, 'booking_updated');

        return redirect()
            ->route('panel.bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        $booking = $this->findOwnBooking($id);

        $booking->delete();

        return redirect()
            ->route('panel.bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK AVAILABILITY
    |--------------------------------------------------------------------------
    */

    public function checkAvailability(Request $request, $id)
    {
        $booking = $this->findOwnBooking($id);

        $request->validate([

            'check_in' => 'required|date',

            'check_out' => 'required|date|after:check_in',
        ]);

        $availability = $this->nightlyAvailability->check(

            booking: $booking,

            checkIn: Carbon::parse($request->check_in),

            checkOut: Carbon::parse($request->check_out)
        );

        return response()->json($availability);
    }

    /*
    |--------------------------------------------------------------------------
    | CALCULATE PRICE
    |--------------------------------------------------------------------------
    */

    public function calculatePrice(Request $request, $id)
    {
        $booking = $this->findOwnBooking($id);

        $request->validate([

            'check_in' => 'required|date',

            'check_out' => 'required|date|after:check_in',

            'adults' => 'nullable|integer|min:1',

            'children' => 'nullable|integer|min:0',
        ]);

        $pricing = $this->pricingEngine->calculate(

            booking: $booking,

            checkIn: Carbon::parse($request->check_in),

            checkOut: Carbon::parse($request->check_out),

            adults: (int) $request->adults,

            children: (int) $request->children,
        );

        return response()->json($pricing);
    }

    /*
    |--------------------------------------------------------------------------
    | GET SLOTS
    |--------------------------------------------------------------------------
    */

    public function getSlots(Request $request, $id)
    {
        $booking = $this->findOwnBooking($id);

        $request->validate([
            'date' => 'required|date',
            'resource_id' => 'nullable|integer|exists:booking_resources,id',
        ]);

        $slots = $this->slotEngine->getAvailableSlots(

            $booking,

            Carbon::parse($request->date),

            $request->filled('resource_id') ? (int) $request->resource_id : null
        );

        return response()->json([
            'slots' => $slots
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | FILTERS
    |--------------------------------------------------------------------------
    */

    private function handleFilters(Request $request, Builder $query)
    {
        if ($request->filled('search')) {

            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category_id')) {

            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {

            if ($request->status == 'active') {

                $query->where('status', 'published');

            } else {

                $query->where('status', 'draft');
            }
        }

        switch ($request->sort) {

            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;

            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;

            default:
                $query->latest();
        }

        return $query;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDATION
    |--------------------------------------------------------------------------
    */

    protected function validateBooking(Request $request, $bookingId = null)
    {
        $data = $request->validate([

            'title' => 'required|string|max:255',

            'slug' => [
                'nullable',
                'string',
                Rule::unique('bookings', 'slug')->ignore($bookingId)
            ],

            'category_id' => 'nullable|exists:booking_categories,id',

            'language' => 'nullable|string|max:10',

            'booking_type' => 'required|string',

            'sub_type' => 'nullable|string|max:255',

            'description' => 'nullable|string',

            'requirements' => 'nullable|string',

            'price' => 'nullable|numeric|min:0',

            'price_per' => 'nullable|numeric|min:0',

            'price_unit' => 'nullable|string|max:64',

            'discount_price' => 'nullable|numeric|min:0',

            'capacity' => 'nullable|integer|min:0',

            'min_persons' => 'nullable|integer|min:0',

            'max_persons' => 'nullable|integer|min:0',

            'duration_minutes' => 'nullable|integer|min:0',

            'currency' => 'nullable|string|max:10',

            'address_line' => 'nullable|string',

            'city' => 'nullable|string',

            'reviewer_message' => 'nullable|string',

            'checkout_message' => 'nullable|string',

            'state' => 'nullable|string',

            'country' => 'nullable|string',

            'postal_code' => 'nullable|string',

            'lat' => 'nullable|numeric',

            'lng' => 'nullable|numeric',

            'meta' => 'nullable|json',
        ]);

        $data['status'] = $request->boolean('status') ? 'published' : 'draft';
        $data['featured'] = $request->boolean('featured');
        $data['location_enabled'] = $request->boolean('location_enabled');

        if (!empty($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        $data['language'] = $data['language'] ?? app()->getLocale();

        if (!empty($data['meta'])) {
            $data['meta'] = json_decode($data['meta'], true);
        }

        return $data;
    }

    private function getUserLanguages(): array
    {
        $userLanguages = getGeneralSettings('user_languages');

        if (!empty($userLanguages) and is_array($userLanguages)) {
            return getLanguages($userLanguages);
        }

        return [app()->getLocale() => ucfirst(app()->getLocale())];
    }

    private function findOwnBooking($id): Booking
    {
        return Booking::query()
            ->where('creator_id', auth()->id())
            ->findOrFail($id);
    }

    private function sendBookingNotification(Booking $booking, string $template): void
    {
        $notifyOptions = [
            '[c.title]' => $booking->title,
            '[item_title]' => $booking->title,
            '[u.name]' => optional(auth()->user())->full_name,
        ];

        sendNotification($template, $notifyOptions, 1);
    }
}
