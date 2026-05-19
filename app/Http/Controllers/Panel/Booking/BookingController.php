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

class BookingController extends Controller
{
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

        $query = Booking::query()
            ->with('category')
            ->where('creator_id', $user->id);

        $query = $this->handleFilters($request, $query);

        $bookings = $query->paginate(10);

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.index', [

            'pageTitle' => 'Bookings',

            'bookings' => $bookings,

            'allCategoryLists' => $allCategoryLists,

            'pagination' => $bookings->links(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.create.index', [

            'pageTitle' => 'New Booking',

            'allCategoryLists' => $allCategoryLists,

            'booking' => null,
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
        $booking = Booking::findOrFail($id);

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title')
            ->orderBy('title')
            ->get();

        return view('design_1.panel.bookings.create.index', [

            'pageTitle' => 'Edit Booking',

            'booking' => $booking,

            'allCategoryLists' => $allCategoryLists,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $data = $this->validateBooking($request, $booking->id);

        $booking->update($data);

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
        $booking = Booking::findOrFail($id);

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
        $booking = Booking::findOrFail($id);

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
        $booking = Booking::findOrFail($id);

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
        $booking = Booking::findOrFail($id);

        $request->validate([
            'date' => 'required|date',
        ]);

        $slots = $this->slotEngine->getAvailableSlots(

            $booking,

            Carbon::parse($request->date)
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
        return $request->validate([

            'title' => 'required|string|max:255',

            'slug' => [
                'nullable',
                'string',
                Rule::unique('bookings', 'slug')->ignore($bookingId)
            ],

            'category_id' => 'nullable|exists:booking_categories,id',

            'booking_type' => 'required|string',

            'description' => 'nullable|string',

            'price' => 'nullable|numeric|min:0',

            'discount_price' => 'nullable|numeric|min:0',

            'capacity' => 'nullable|integer|min:0',

            'min_persons' => 'nullable|integer|min:0',

            'max_persons' => 'nullable|integer|min:0',

            'duration_minutes' => 'nullable|integer|min:0',

            'currency' => 'nullable|string|max:10',

            'address_line' => 'nullable|string',

            'city' => 'nullable|string',

            'state' => 'nullable|string',

            'country' => 'nullable|string',

            'postal_code' => 'nullable|string',

            'lat' => 'nullable|numeric',

            'lng' => 'nullable|numeric',
        ]);
    }
}