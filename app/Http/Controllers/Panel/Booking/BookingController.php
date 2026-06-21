<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingResource;
use App\Models\BookingTimeSlot;
use App\Models\BookingFaq;
use App\Models\BookingSpecification;
use App\Models\OrgAvailabilityRule;
use App\Models\OrgAvailabilityRange;
use App\Models\BookingRatePlan;
use App\Services\PricingEngine;
use App\Services\SlotEngine;
use App\Services\NightlyAvailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
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
    | CREATE (legacy single-page form — kept for backward compatibility)
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
    | STORE (legacy single-page form)
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
    | EDIT (legacy single-page form)
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
    | UPDATE (legacy single-page form)
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
    |==========================================================================
    | CREATE BOOKING WIZARD (new multi-step flow)
    |==========================================================================
    */

    /**
     * Step map: number => [key, view, label, types]
     * `types` empty array = applicable to ALL booking types.
     */
    private function wizardSteps(): array
    {
        return [
            1 => ['key' => 'general',        'view' => 'steps.step1_general',        'label' => 'General Info',             'types' => []],
            2 => ['key' => 'pricing',        'view' => 'steps.step2_pricing',        'label' => 'Pricing & Availability',   'types' => []],
            3 => ['key' => 'resources',      'view' => 'steps.step3_resources',      'label' => 'Participants & Resources', 'types' => []],
            4 => ['key' => 'content',        'view' => 'steps.step4_content',        'label' => 'Content',                  'types' => []],
            5 => ['key' => 'prerequisites',  'view' => 'steps.step5_prerequisites',  'label' => 'Prerequisites & Related',  'types' => []],
            6 => ['key' => 'faq',            'view' => 'steps.step6_faq',            'label' => 'FAQ',                      'types' => []],
            7 => ['key' => 'location_specs', 'view' => 'steps.step7_location_specs', 'label' => 'Location & Filters',       'types' => []],
            8 => ['key' => 'final',          'view' => 'steps.step8_final',          'label' => 'Review & Submit',          'types' => []],
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | WIZARD: START
    |--------------------------------------------------------------------------
    */

    public function wizardStart(Request $request)
    {
        $booking = Booking::create([
            'creator_id'   => auth()->id(),
            'title'        => 'Untitled booking',
            'booking_type' => $request->input('booking_type', 'tour'),
            'status'       => 'draft',
            'wizard_step'  => 1,
            'language'     => app()->getLocale(),
        ]);

        return redirect()->route('panel.bookings.wizard.show_step', [
            'booking' => $booking->id,
            'step' => 1,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | WIZARD: SHOW STEP
    |--------------------------------------------------------------------------
    */

    public function wizardShowStep(Request $request, $bookingId, int $step)
    {
        $booking = $this->findOwnBooking($bookingId);

        $steps = $this->wizardSteps();

        if (!isset($steps[$step])) {
            abort(404);
        }

        $booking->loadMissing([
            'resources', 'timeSlots', 'faqs', 'ratePlans', 'category',
        ]);

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title', 'parent_id')
            ->orderBy('title')
            ->get();

        $orgAvailabilityRule = OrgAvailabilityRule::where('org_id', auth()->id())->first();

        $specifications = $booking->category_id
            ? BookingSpecification::active()->ordered()->forCategory($booking->category_id)->with('bookingValues')->get()
            : collect();

        $data = [
            'pageTitle'           => 'New Booking',
            'booking'             => $booking,
            'steps'               => $steps,
            'currentStep'         => $step,
            'allCategoryLists'    => $allCategoryLists,
            'userLanguages'       => $this->getUserLanguages(),
            'orgAvailabilityRule' => $orgAvailabilityRule,
            'specifications'      => $specifications,
            // Previously chosen specification value ids are stored on booking.meta
            // (no booking<->specification_value pivot table exists yet).
            'selectedSpecValueIds' => $booking->meta['specification_value_ids'] ?? [],
        ];

        if ($request->ajax()) {
            // Return just the step partial — used when navigating via the top stepper
            return view('design_1.panel.booking.create_booking.' . $steps[$step]['view'], $data)->render();
        }

        return view('design_1.panel.booking.create_booking.index', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | WIZARD: SAVE STEP (AJAX)
    |--------------------------------------------------------------------------
    */

    public function wizardSaveStep(Request $request, $bookingId, int $step)
    {
        $booking = $this->findOwnBooking($bookingId);

        switch ($step) {
            case 1:
                $this->wizardSaveGeneral($request, $booking);
                break;
            case 2:
                $this->wizardSavePricing($request, $booking);
                break;
            case 3:
                $this->wizardSaveParticipantsToggles($request, $booking);
                break;
            case 4:
                $this->wizardSaveContent($request, $booking);
                break;
            case 5:
                $this->wizardSavePrerequisites($request, $booking);
                break;
            case 6:
                // FAQ rows are saved individually via storeFaq(); nothing bulk to do here
                break;
            case 7:
                $this->wizardSaveLocationSpecs($request, $booking);
                break;
            case 8:
                $this->wizardSaveFinal($request, $booking);
                break;
        }

        $booking->wizard_step = max((int) $booking->wizard_step, $step);
        $booking->save();

        return response()->json([
            'success'    => true,
            'message'    => 'Step saved.',
            'booking_id' => $booking->id,
            'next_step'  => min($step + 1, 8),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | STEP SAVE HANDLERS
    |--------------------------------------------------------------------------
    */

    private function wizardSaveGeneral(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'title'        => 'required|string|max:255',
            'slug'         => ['nullable', 'string', Rule::unique('bookings', 'slug')->ignore($booking->id)],
            'category_id'  => 'nullable|exists:booking_categories,id',
            'language'     => 'nullable|string|max:10',
            'booking_type' => 'required|string',
            'sub_type'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'requirements' => 'nullable|string',
        ]);

        $booking->fill($data);
        $booking->save();
    }

    private function wizardSavePricing(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'price'            => 'nullable|numeric|min:0',
            'discount_price'   => 'nullable|numeric|min:0',
            'currency'         => 'nullable|string|max:10',
            'price_per'        => 'nullable|numeric|min:0',
            'price_unit'       => 'nullable|string|max:64',
            'duration_minutes' => 'nullable|integer|min:0',

            'rate_plans'         => 'nullable|array',
            'rate_plans.*.name'  => 'required_with:rate_plans|string|max:255',
            'rate_plans.*.from'  => 'nullable|string',
            'rate_plans.*.to'    => 'nullable|string',
            'rate_plans.*.price' => 'required_with:rate_plans|numeric|min:0',
        ]);

        if (!empty($data['currency'])) {
            $data['currency'] = strtoupper($data['currency']);
        }

        $ratePlans = $data['rate_plans'] ?? [];
        unset($data['rate_plans']);

        $booking->fill($data);
        $booking->save();

        // Replace rate plans wholesale for simplicity in this pass
        $booking->ratePlans()->delete();

        foreach ($ratePlans as $plan) {
            $booking->ratePlans()->create([
                'name'             => $plan['name'],
                'type'             => 'seasonal',
                'price'            => $plan['price'],
                'price_unit'       => $booking->price_unit,
                'calculation_type' => 'flat',
                'priority'         => 0,
                'conditions'       => [
                    'from' => $plan['from'] ?? null,
                    'to'   => $plan['to'] ?? null,
                ],
                'status' => true,
            ]);
        }
    }

    /**
     * Step 3 "Participants" now writes straight onto the Booking row
     * (min_persons/max_persons/max_children/children_allowed already
     * exist there — no separate table needed).
     *
     * Resources & Assets are saved live via storeResource()/destroyResource()
     * as the user adds/removes rows in the UI, so there's nothing bulk
     * to persist for them here.
     *
     * Recurring slots are saved live via storeTimeSlot()/destroyTimeSlot().
     * This method just persists the on/off toggles for all four sections.
     */
    private function wizardSaveParticipantsToggles(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'participants_enabled' => 'nullable|boolean',
            'resources_enabled'    => 'nullable|boolean',
            'assets_enabled'       => 'nullable|boolean',
            'recurring_enabled'    => 'nullable|boolean',

            'min_persons'     => 'nullable|integer|min:0',
            'max_persons'     => 'nullable|integer|min:0',
            'max_children'    => 'nullable|integer|min:0',
            'children_allowed'=> 'nullable|boolean',
        ]);

        $booking->min_persons      = $data['min_persons'] ?? $booking->min_persons;
        $booking->max_persons      = $data['max_persons'] ?? $booking->max_persons;
        $booking->max_children     = $data['max_children'] ?? $booking->max_children;
        $booking->children_allowed = $request->boolean('children_allowed');

        $meta = $booking->meta ?? [];
        $meta['participants_enabled'] = $request->boolean('participants_enabled');
        $meta['resources_enabled']    = $request->boolean('resources_enabled');
        $meta['assets_enabled']       = $request->boolean('assets_enabled');
        $meta['recurring_enabled']    = $request->boolean('recurring_enabled');
        $booking->meta = $meta;

        $booking->save();
    }

    private function wizardSaveContent(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'content_sections'         => 'nullable|array',
            'content_sections.*.title' => 'required_with:content_sections|string|max:255',
            'content_sections.*.body'  => 'nullable|string',
        ]);

        $meta = $booking->meta ?? [];
        $meta['content_sections'] = $data['content_sections'] ?? [];
        $booking->meta = $meta;
        $booking->save();
    }

    private function wizardSavePrerequisites(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'related_booking_ids'   => 'nullable|array',
            'related_booking_ids.*' => 'integer|exists:bookings,id',
            'prerequisite_text'     => 'nullable|string',
        ]);

        $meta = $booking->meta ?? [];
        $meta['related_booking_ids'] = $data['related_booking_ids'] ?? [];
        $meta['prerequisite_text']   = $data['prerequisite_text'] ?? null;
        $booking->meta = $meta;
        $booking->save();
    }

    private function wizardSaveLocationSpecs(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'location_enabled' => 'nullable|boolean',
            'address_line'     => 'nullable|string',
            'city'             => 'nullable|string',
            'state'            => 'nullable|string',
            'country'          => 'nullable|string',
            'postal_code'      => 'nullable|string',
            'lat'              => 'nullable|numeric',
            'lng'              => 'nullable|numeric',

            'specification_values'   => 'nullable|array',
            'specification_values.*' => 'integer|exists:booking_specification_values,id',
        ]);

        $data['location_enabled'] = $request->boolean('location_enabled');

        $specValueIds = $data['specification_values'] ?? [];
        unset($data['specification_values']);

        $booking->fill($data);

        // Pivot-less: chosen specification VALUE ids are stored on booking meta,
        // since there's no booking<->specification_value pivot table provided.
        $meta = $booking->meta ?? [];
        $meta['specification_value_ids'] = $specValueIds;
        $booking->meta = $meta;

        $booking->save();
    }

    private function wizardSaveFinal(Request $request, Booking $booking): void
    {
        $data = $request->validate([
            'reviewer_message' => 'nullable|string',
            'checkout_message' => 'nullable|string',
            'terms_accepted'   => 'required|accepted',
        ]);

        $booking->reviewer_message = $data['reviewer_message'] ?? null;
        $booking->checkout_message = $data['checkout_message'] ?? null;
        $booking->terms_accepted   = true;
        $booking->save();
    }

    /*
    |--------------------------------------------------------------------------
    | WIZARD: SUBMIT (final action)
    |--------------------------------------------------------------------------
    */

    public function wizardSubmit(Request $request, $bookingId)
    {
        $booking = $this->findOwnBooking($bookingId);

        if (!$booking->terms_accepted) {
            return response()->json([
                'success' => false,
                'message' => 'Please accept the terms and conditions before submitting.',
            ], 422);
        }

        $booking->status = 'pending'; // goes to review queue, not auto-published
        $booking->save();

        $this->sendBookingNotification($booking, 'booking_created');

        if ($request->ajax()) {
            return response()->json([
                'success'  => true,
                'redirect' => route('panel.bookings.index'),
            ]);
        }

        return redirect()->route('panel.bookings.index')->with('success', 'Booking submitted for review.');
    }

    /*
    |--------------------------------------------------------------------------
    | SUB-RESOURCE: RESOURCES & ASSETS (both live in booking_resources)
    |--------------------------------------------------------------------------
    */

    public function storeResource(Request $request, $bookingId)
    {
        $booking = $this->findOwnBooking($bookingId);

        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'type'        => 'nullable|string|max:64',
            'description' => 'nullable|string',
            'capacity'    => 'nullable|integer|min:0',
            'extra_price' => 'nullable|numeric|min:0',
        ]);

        $data['type'] = $data['type'] ?? 'resource';
        $data['status'] = true;
        $data['order'] = $booking->resources()->count();

        $resource = $booking->resources()->create($data);

        return response()->json(['success' => true, 'resource' => $resource]);
    }

    public function destroyResource($resourceId)
    {
        $resource = BookingResource::whereHas('booking', function ($q) {
            $q->where('creator_id', auth()->id());
        })->findOrFail($resourceId);

        $resource->delete();

        return response()->json(['success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | SUB-RESOURCE: TIME SLOTS (Recurring Bookings)
    |--------------------------------------------------------------------------
    */

    public function storeTimeSlot(Request $request, $bookingId)
    {
        $booking = $this->findOwnBooking($bookingId);

        $data = $request->validate([
            'resource_id'      => 'nullable|integer|exists:booking_resources,id',
            'day_of_week'      => 'required|array|min:1',
            'day_of_week.*'    => 'string|in:mon,tue,wed,thu,fri,sat,sun',
            'start_time'       => 'required',
            'end_time'         => 'required',
            'duration_minutes' => 'nullable|integer|min:0',
            'buffer_minutes'   => 'nullable|integer|min:0',
            'max_bookings'     => 'nullable|integer|min:1',
        ]);

        $data['status'] = true;
        $data['max_bookings'] = $data['max_bookings'] ?? 1;

        $timeSlot = $booking->timeSlots()->create($data);

        return response()->json(['success' => true, 'time_slot' => $timeSlot]);
    }

    public function destroyTimeSlot($timeSlotId)
    {
        $timeSlot = BookingTimeSlot::whereHas('booking', function ($q) {
            $q->where('creator_id', auth()->id());
        })->findOrFail($timeSlotId);

        $timeSlot->delete();

        return response()->json(['success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | SUB-RESOURCE: FAQ
    |--------------------------------------------------------------------------
    */

    public function storeFaq(Request $request, $bookingId)
    {
        $booking = $this->findOwnBooking($bookingId);

        $data = $request->validate([
            'question' => 'required|string|max:500',
            'answer'   => 'nullable|string',
        ]);

        $data['status'] = true;
        $data['sort_order'] = $booking->faqs()->count();

        $faq = $booking->faqs()->create($data);

        return response()->json(['success' => true, 'faq' => $faq]);
    }

    public function destroyFaq($faqId)
    {
        $faq = BookingFaq::whereHas('booking', function ($q) {
            $q->where('creator_id', auth()->id());
        })->findOrFail($faqId);

        $faq->delete();

        return response()->json(['success' => true]);
    }

    /*
    |--------------------------------------------------------------------------
    | CATEGORY SPECIFICATIONS (AJAX, used on category change in step 1/7)
    |--------------------------------------------------------------------------
    */

    public function getCategorySpecifications($categoryId)
    {
        $specifications = BookingSpecification::active()
            ->ordered()
            ->forCategory((int) $categoryId)
            ->with('bookingValues')
            ->get();

        return response()->json(['specifications' => $specifications]);
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
    | VALIDATION (legacy single-page form)
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