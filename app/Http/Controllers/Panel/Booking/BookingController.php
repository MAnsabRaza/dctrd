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
use App\Services\BookingTemplateConfig;
use App\Services\BookingSubTemplateConfig;
use App\Services\PricingEngine;
use App\Services\SlotEngine;
use App\Services\NightlyAvailability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
    | CREATE — step 1, brand new booking (no record yet, no template chosen yet)
    |--------------------------------------------------------------------------
    */

    public function create()
    {
        $this->authorize('panel_bookings_create');

        $user = auth()->user();
        $user->loadMissing('userMetas');

        foreach ($user->userMetas as $meta) {
            $user->{$meta->name} = $meta->value;
        }

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title', 'slug', 'parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get();

        $parentCategories = $allCategoryLists->whereNull('parent_id')->values();
        $childCategories  = $allCategoryLists->whereNotNull('parent_id')->values();

        $data = [
            'pageTitle'   => trans('update.new_booking_page_title') ?: 'New Booking',
            'currentStep' => 1,
            'stepCount'   => 8,
            'booking'     => null,
            'config'      => null, // no template selected yet on a brand-new booking
            'allCategoryLists' => $allCategoryLists,
            'userLanguages'    => $this->getUserLanguages(),
            'templateOptions'  => BookingTemplateConfig::allTypes(),
            'bookingTypeCategoryMap' => $this->buildTypeCategoryMap($parentCategories),
            'categoriesByParent'     => $this->buildCategoriesByParentMap($childCategories),
            'subTemplateConfigs'     => $this->buildSubTemplateConfigsForJs(),
            'bookingDefaults'  => [
                'currency'         => $user->booking_default_currency ?? $user->currency ?? 'USD',
                'price_unit'       => $user->booking_default_price_unit ?? 'booking',
                'status'           => !empty($user->booking_auto_publish) ? 'published' : 'draft',
                'location_enabled' => !empty($user->booking_location_enabled),
            ],
        ];

        return view('design_1.panel.bookings.create_booking.index', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | STORE — creates the booking from step 1, then redirects to step 2
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $this->authorize('panel_bookings_create');

        $user = auth()->user();

        $rules = [
            'title'        => 'required|string|max:255',
            'slug'         => 'nullable|string|max:255|unique:bookings,slug',
            'category_id'  => $this->categoryValidationRule($request->booking_type ?? '', true),
            'language'     => 'nullable|string|max:10',
            'booking_type' => 'required|in:' . implode(',', array_keys(BookingTemplateConfig::allTypes())),
            'sub_type'     => 'nullable|string|max:255',
            'description'  => 'nullable|string',
            'requirements' => 'nullable|string',
            'status' => 'nullable|in:draft,pending,published,rejected,inactive',
        ];

        $this->validate($request, $rules);

        $data = $request->all();

        $isDraft   = $this->isEnabledRequestValue($data['draft'] ?? null);
        $getNext   = $this->isEnabledRequestValue($data['get_next'] ?? null);
        $status     = ($isDraft || $getNext) ? 'draft' : 'pending';

        $booking = Booking::create([
            'creator_id'   => $user->id,
            'title'        => $data['title'],
            'slug'         => $data['slug'] ?? null,
            'category_id'  => $data['category_id'] ?? null,
            'language'     => $data['language'] ?? app()->getLocale(),
            'booking_type' => $data['booking_type'],
            'sub_type'     => $data['sub_type'] ?? null,
            'description'  => $data['description'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'status'       => $status,
            'qr_enabled'   => $request->boolean('qr_enabled'),
        ]);
        $notifyOptions = [
            '[u.name]'      => $user->full_name,
            '[item_title]'  => $booking->title,
            '[content_type]'=> trans('update.booking') ?: 'Booking',
        ];
        sendNotification('new_item_created', $notifyOptions, 1);

        $url = '/panel/bookings';
        if ($getNext) {
            $url = '/panel/bookings/' . $booking->id . '/step/2';
        }

        return redirect($url);
    }

    /*
    |--------------------------------------------------------------------------
    | EDIT — renders any step for an existing booking
    |--------------------------------------------------------------------------
    */

    public function edit(Request $request, $id, $step = 1)
    {
        $this->authorize('panel_bookings_create');

        $user = auth()->user();

        $stepCount = 8;

        if ($step > $stepCount) {
            return redirect("/panel/bookings/{$id}/step/{$stepCount}");
        }

        $query = Booking::where('id', $id)
            ->where('creator_id', $user->id)
            ->with(['category', 'ratePlans']);

        if ($step == 3) {
            $query->with(['resources', 'timeSlots']);
        } elseif ($step == 6) {
            $query->with(['faqs' => function ($q) {
                $q->orderBy('id', 'asc');
            }]);
        } elseif ($step == 7) {
            $query->with(['resources']);
        }

        $booking = $query->first();

        if (empty($booking)) {
            abort(404);
        }

        $allCategoryLists = BookingCategory::query()
            ->select('id', 'title', 'slug', 'parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get();
        $parentCategories = $allCategoryLists->whereNull('parent_id')->values();
        $childCategories  = $allCategoryLists->whereNotNull('parent_id')->values();

        $config = BookingTemplateConfig::for($booking->booking_type);
        $subTemplate = $this->subTemplateFromCategoryId($booking->category_id);

        $data = [
            'pageTitle'        => (trans('update.edit_booking') ?: 'Edit Booking') . ' | ' . $booking->title,
            'booking'          => $booking,
            'currentStep'      => (int) $step,
            'stepCount'        => $stepCount,
            'allCategoryLists' => $allCategoryLists,
            'userLanguages'    => $this->getUserLanguages(),
            'config'           => $config,
            'subTemplate'      => $subTemplate,
            'templateOptions'  => BookingTemplateConfig::allTypes(),
            'bookingTypeCategoryMap' => $this->buildTypeCategoryMap($parentCategories),
            'categoriesByParent'     => $this->buildCategoriesByParentMap($childCategories),
            'subTemplateConfigs'     => $this->buildSubTemplateConfigsForJs(),
        ];

        if ($step == 1) {
            // nothing extra needed
        } elseif ($step == 2) {
            $data['orgAvailabilityRule'] = OrgAvailabilityRule::where('org_id', $user->id)->first();
        } elseif ($step == 7) {
            $data['specifications'] = $booking->category_id
                ? BookingSpecification::active()->ordered()->forCategory($booking->category_id)->with('bookingValues')->get()
                : collect();

            $data['selectedSpecValueIds'] = $booking->meta['specification_value_ids'] ?? [];

            $data['staffResources'] = $booking->resources
                ? $booking->resources->where('type', 'staff')
                : collect();
        } elseif ($step == 5) {
            $data['allBookings'] = Booking::query()
                ->where('creator_id', $user->id)
                ->where('id', '!=', $booking->id)
                ->select('id', 'title')
                ->orderBy('title')
                ->get();
        }

        return view('design_1.panel.bookings.create_booking.index', $data);
    }

    /*
    |--------------------------------------------------------------------------
    | UPDATE — saves whichever step was submitted, then redirects
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $this->authorize('panel_bookings_create');

        $user = auth()->user();

        $data = $request->all();
        $currentStep = (int) $data['current_step'];
        $getStep     = $data['get_step'] ?? null;
        $getNextStep = $this->isEnabledRequestValue($data['get_next'] ?? null);
        $isDraft     = $this->isEnabledRequestValue($data['draft'] ?? null);

        $booking = Booking::where('id', $id)
            ->where('creator_id', $user->id)
            ->first();

        if (empty($booking)) {
            abort(404);
        }

        $config = BookingTemplateConfig::for($booking->booking_type);
        $subTemplate = $this->subTemplateFromCategoryId($booking->category_id);

        $rules = [];

        if ($currentStep == 1) {
            $rules = [
                'title'        => 'required|string|max:255',
                'slug'         => ['nullable', 'string', 'max:255', Rule::unique('bookings', 'slug')->ignore($booking->id)],
                'category_id'  => $this->categoryValidationRule($request->booking_type ?? $booking->booking_type ?? '', true),
                'language'     => 'nullable|string|max:10',
                'booking_type' => 'required|in:' . implode(',', array_keys(BookingTemplateConfig::allTypes())),
                'sub_type'     => 'nullable|string|max:255',
                'description'  => 'nullable|string',
                'requirements' => 'nullable|string',
                 'status'       => 'nullable|in:draft,pending,published,rejected,inactive', // <-- YE LINE ADD KI
            ];
        } elseif ($currentStep == 2) {
            $extraRules = collect($config->rules())->only(['capacity', 'inventory'])->toArray();
            if ($subTemplate) {
                $extraRules = array_merge(
                    $extraRules,
                    collect($subTemplate->rules())->only(['price', 'duration_minutes', 'capacity', 'inventory'])->toArray()
                );
            }

            $rules = array_merge([
                'price'            => 'nullable|numeric|min:0',
                'discount_price'   => 'nullable|numeric|min:0',
                'currency'         => 'nullable|string|max:10',
                'price_per'        => 'nullable|numeric|min:0',
                'price_unit'       => 'nullable|string|max:64',
                'duration_minutes' => 'nullable|integer|min:0',

                'deposit_enabled'  => 'nullable|in:on',
                'deposit_amount'   => 'nullable|numeric|min:0',
                'deposit_type'     => 'nullable|in:fixed,percentage',

                'rate_plans'         => 'nullable|array',
                'rate_plans.*.name'  => 'required_with:rate_plans|string|max:255',
                'rate_plans.*.from'  => 'nullable|string',
                'rate_plans.*.to'    => 'nullable|string',
                'rate_plans.*.price' => 'required_with:rate_plans|numeric|min:0',
            ], $extraRules);
        } elseif ($currentStep == 3) {
            $rules = [
                'min_persons'      => 'nullable|integer|min:0',
                'max_persons'      => 'nullable|integer|min:0',
                'max_children'     => 'nullable|integer|min:0',
                'children_allowed' => 'nullable|in:on',
            ];
        } elseif ($currentStep == 4) {
            $rules = [
                'content_sections'          => 'nullable|array',
                'content_sections.*.title'  => 'required_with:content_sections|string|max:255',
                'content_sections.*.body'   => 'nullable|string',
            ];
        } elseif ($currentStep == 5) {
            $rules = [
                'related_booking_ids'   => 'nullable|array',
                'related_booking_ids.*' => 'integer|exists:bookings,id',
                'prerequisite_text'     => 'nullable|string',
            ];
        } elseif ($currentStep == 6) {
            // FAQ rows are managed via storeFaq()/destroyFaq(); nothing to validate here
        } elseif ($currentStep == 7) {
            $metaRules = collect($config->rules())
                ->filter(fn ($rule, $key) => str_starts_with($key, 'meta.'))
                ->toArray();
            if ($subTemplate) {
                $metaRules = array_merge(
                    $metaRules,
                    collect($subTemplate->rules())
                        ->filter(fn ($rule, $key) => str_starts_with($key, 'meta.'))
                        ->toArray()
                );
            }

            $rules = array_merge([
                'location_enabled' => 'nullable|in:on',
                'address_line'     => 'nullable|string|max:255',
                'city'             => 'nullable|string|max:100',
                'state'            => 'nullable|string|max:100',
                'country'          => 'nullable|string|max:100',
                'postal_code'      => 'nullable|string|max:20',
                'lat'              => 'nullable|numeric',
                'lng'              => 'nullable|numeric',

                'specification_values'   => 'nullable|array',
                'specification_values.*' => 'integer|exists:booking_specification_values,id',
            ], $metaRules);

            $data['location_enabled'] = !empty($data['location_enabled']) && $data['location_enabled'] === 'on';
        } elseif ($currentStep == 8) {
            $rules = [
                'reviewer_message' => 'nullable|string',
                'checkout_message' => 'nullable|string',
                'terms_accepted'   => (!$isDraft && !$getNextStep) ? 'required|in:on' : 'nullable|in:on',
            ];
        }

        $this->validate($request, $rules);

        $finalSubmit = ($currentStep == 8 and !$getNextStep and !$isDraft);

        $data['status'] = $isDraft ? 'draft' : ($finalSubmit ? 'pending' : $booking->status);

        if ($currentStep == 1) {
            $booking->fill([
                'title'        => $data['title'],
                'slug'         => $data['slug'] ?? $booking->slug,
                'category_id'  => $data['category_id'] ?? null,
                'language'     => $data['language'] ?? $booking->language,
                'booking_type' => $data['booking_type'],
                'sub_type'     => $data['sub_type'] ?? null,
                'description'  => $data['description'] ?? null,
                'requirements' => $data['requirements'] ?? null,
                'qr_enabled'   => $request->boolean('qr_enabled'),
            ]);
        } elseif ($currentStep == 2) {
            $ratePlans = $data['rate_plans'] ?? [];

            if (!empty($data['currency'])) {
                $data['currency'] = strtoupper($data['currency']);
            }

            $booking->fill([
                'price'            => $data['price'] ?? null,
                'discount_price'   => $data['discount_price'] ?? null,
                'currency'         => $data['currency'] ?? $booking->currency,
                'price_per'        => $data['price_per'] ?? null,
                'price_unit'       => $data['price_unit'] ?? ($subTemplate ? $subTemplate->priceUnit() : $booking->price_unit),
                'duration_minutes' => $data['duration_minutes'] ?? null,

                'capacity'  => $this->fieldRelevant('capacity', $config, $subTemplate) ? ($data['capacity'] ?? null) : null,
                'inventory' => $this->fieldRelevant('inventory', $config, $subTemplate) ? ($data['inventory'] ?? null) : null,

                'deposit_enabled' => !empty($data['deposit_enabled']) && $data['deposit_enabled'] === 'on',
                'deposit_amount'  => $data['deposit_amount'] ?? null,
                'deposit_type'    => $data['deposit_type'] ?? null,
            ]);

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
        } elseif ($currentStep == 3) {
            $booking->fill([
                'min_persons'      => $data['min_persons'] ?? $booking->min_persons,
                'max_persons'      => $data['max_persons'] ?? $booking->max_persons,
                'max_children'     => $data['max_children'] ?? $booking->max_children,
                'children_allowed' => !empty($data['children_allowed']) && $data['children_allowed'] === 'on',
            ]);

            $meta = $booking->meta ?? [];
            $meta['participants_enabled'] = !empty($data['participants_enabled']) && $data['participants_enabled'] === 'on';
            $meta['resources_enabled']    = !empty($data['resources_enabled']) && $data['resources_enabled'] === 'on';
            $meta['assets_enabled']       = !empty($data['assets_enabled']) && $data['assets_enabled'] === 'on';
            $meta['recurring_enabled']    = !empty($data['recurring_enabled']) && $data['recurring_enabled'] === 'on';
            $booking->meta = $meta;
        } elseif ($currentStep == 4) {
            $meta = $booking->meta ?? [];
            $meta['content_sections'] = $data['content_sections'] ?? [];
            $booking->meta = $meta;
        } elseif ($currentStep == 5) {
            $meta = $booking->meta ?? [];
            $meta['related_booking_ids'] = $data['related_booking_ids'] ?? [];
            $meta['prerequisite_text']   = $data['prerequisite_text'] ?? null;
            $booking->meta = $meta;
        } elseif ($currentStep == 6) {
            // FAQ rows are managed live via storeFaq()/destroyFaq()
        } elseif ($currentStep == 7) {
            $specValueIds = $data['specification_values'] ?? [];

            $booking->fill([
                'location_enabled' => $data['location_enabled'],
                'address_line'     => $data['location_enabled'] ? ($data['address_line'] ?? null) : null,
                'city'             => $data['location_enabled'] ? ($data['city'] ?? null) : null,
                'state'            => $data['location_enabled'] ? ($data['state'] ?? null) : null,
                'country'          => $data['location_enabled'] ? ($data['country'] ?? null) : null,
                'postal_code'      => $data['location_enabled'] ? ($data['postal_code'] ?? null) : null,
                'lat'              => $data['location_enabled'] ? ($data['lat'] ?? null) : null,
                'lng'              => $data['location_enabled'] ? ($data['lng'] ?? null) : null,
            ]);

            $meta = $booking->meta ?? [];
            $meta['specification_value_ids'] = $specValueIds;

            $multiValueKeys = ['amenities', 'extra_fees', 'ticket_types', 'extras'];

            $metaFields = $subTemplate
                ? collect($subTemplate->relevantFields())->filter(fn ($field) => str_starts_with($field, 'meta.'))->values()->all()
                : $config->fields();

            foreach ($metaFields as $field) {
                if (!str_starts_with($field, 'meta.')) {
                    continue;
                }

                $metaKey = substr($field, 5);
                $submitted = $data['meta'][$metaKey] ?? null;

                $meta[$metaKey] = $submitted !== null
                    ? $submitted
                    : (in_array($metaKey, $multiValueKeys) ? [] : null);
            }

            if ($this->fieldRelevant('staff_id', $config, $subTemplate)) {
                $meta['staff_id'] = $data['meta']['staff_id'] ?? null;
            }
            if ($this->fieldRelevant('extras', $config, $subTemplate)) {
                $meta['extras'] = $data['meta']['extras'] ?? [];
            }

            $booking->meta = $meta;
        } elseif ($currentStep == 8) {
            $booking->fill([
                'reviewer_message' => $data['reviewer_message'] ?? null,
                'checkout_message' => $data['checkout_message'] ?? null,
            ]);

            $meta = $booking->meta ?? [];
            $meta['terms_accepted'] = true;
            $booking->meta = $meta;
        }

        $booking->status = $data['status'];
        $booking->save();

        if ($finalSubmit) {
            $notifyOptions = [
                '[u.name]'       => $user->full_name,
                '[item_title]'   => $booking->title,
                '[content_type]' => trans('update.booking') ?: 'Booking',
            ];
            sendNotification('content_review_request', $notifyOptions, 1);
        }

        $url = '/panel/bookings';

        if ($getNextStep) {
            $nextStep = (!empty($getStep) and $getStep > 0) ? $getStep : $currentStep + 1;
            $url = '/panel/bookings/' . $booking->id . '/step/' . (($nextStep <= 8) ? $nextStep : 8);
        }

        return redirect($url);
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

    public function regenerateQr($id)
{
    $booking = $this->findOwnBooking($id);

    if (empty($booking->qr_enabled)) {
        return back()->with('error', 'QR Code is not enabled for this booking.');
    }

    app(\App\Services\PusClient::class)->createLink($booking);

    return redirect('/panel/bookings/' . $id . '/step/1')
        ->with('success', 'QR Code and Short URL re-generated successfully.');
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
            'check_in'  => 'required|date',
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
            'check_in'  => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'adults'    => 'nullable|integer|min:1',
            'children'  => 'nullable|integer|min:0',
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
            'date'        => 'required|date',
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
    | SUB-RESOURCE: RESOURCES, ASSETS & STAFF
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
    | SUB-RESOURCE: TIME SLOTS
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
            'title'  => 'required|string|max:255',
            'answer' => 'required|string',
        ]);

        $data['creator_id'] = auth()->id();
        $data['locale']     = app()->getLocale();

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
    | CATEGORY SPECIFICATIONS
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

        if ($request->filled('booking_type')) {
            $query->where('booking_type', $request->booking_type);
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

    private function getUserLanguages(): array
    {
        $userLanguages = getGeneralSettings('user_languages');

        if (!empty($userLanguages) and is_array($userLanguages)) {
            return getLanguages($userLanguages);
        }

        return [app()->getLocale() => ucfirst(app()->getLocale())];
    }

    private function buildSubTemplateConfigsForJs(): array
    {
        $configs = [];
        foreach (BookingSubTemplateConfig::all() as $slug => $raw) {
            $sub = BookingSubTemplateConfig::forSlug($slug);
            $configs[$slug] = $sub->toArray();
        }
        return $configs;
    }

    private function subTemplateFromCategoryId($categoryId): ?BookingSubTemplateConfig
    {
        if (empty($categoryId)) {
            return null;
        }

        return BookingSubTemplateConfig::forSlug(
            BookingCategory::where('id', $categoryId)->value('slug')
        );
    }

    private function fieldRelevant(string $field, BookingTemplateConfig $config, ?BookingSubTemplateConfig $subTemplate): bool
    {
        return $subTemplate
            ? in_array($field, $subTemplate->relevantFields(), true)
            : in_array($field, $config->fields(), true);
    }

    private function buildCategoriesByParentMap($childCategories): array
    {
        $map = [];
        foreach ($childCategories as $child) {
            $map[$child->parent_id][] = [
                'id' => $child->id,
                'title' => $child->title,
                'slug' => $child->slug,
            ];
        }
        return $map;
    }

    /**
     * FIX: ab dono taraf (booking_type constants aur category slug/title)
     * ko exactly wahi Str::slug() normalization se guzara jata hai jo JS ke
     * normalizeSlug() function mein hoti hai (lowercase + dashes). Isse
     * "beauty_spa" vs "beauty-spa" jaisi mismatches khatam ho jati hain.
     */
    private function buildTypeCategoryMap($parentCategories): array
    {
        $map = [];
        foreach ($parentCategories as $category) {
            $map[Str::slug($category->slug)] = $category->id;
            $map[Str::slug($category->title)] = $category->id;
        }

        // Extra safety: booking_type constants ko bhi direct keys ke tor par
        // add karo agar koi parent category unse match karti ho (case/format
        // farq ke bawajood) — taake JS side ka fallback aur bhi mazboot ho.
        foreach (BookingTemplateConfig::allTypes() as $typeSlug => $label) {
            $normalizedType = Str::slug($typeSlug);
            if (!isset($map[$normalizedType])) {
                foreach ($parentCategories as $category) {
                    if (Str::slug($category->slug) === $normalizedType
                        || Str::slug($category->title) === $normalizedType) {
                        $map[$normalizedType] = $category->id;
                        break;
                    }
                }
            }
        }

        return $map;
    }

    private function categoryValidationRule(string $bookingType, bool $required = false): array
    {
        $validCategoryIds = $this->validCategoryIdsForBookingType($bookingType);

        return [
            $required ? 'required' : 'nullable',
            Rule::exists('booking_categories', 'id')->where(function ($q) use ($validCategoryIds) {
                if (!empty($validCategoryIds)) {
                    $q->whereIn('id', $validCategoryIds);
                } else {
                    $q->whereRaw('1 = 0');
                }
            }),
        ];
    }

    private function validCategoryIdsForBookingType(string $bookingType): array
    {
        if (empty($bookingType)) {
            return [];
        }

        $childCategories = BookingCategory::query()
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->get(['id', 'slug']);

        $ids = [];
        foreach ($childCategories as $category) {
            $subTemplate = BookingSubTemplateConfig::forSlug($category->slug);
            if ($subTemplate && $subTemplate->parentType() === $bookingType) {
                $ids[] = $category->id;
            }
        }

        if (!empty($ids)) {
            return $ids;
        }

        $parentCategories = BookingCategory::whereNull('parent_id')
            ->where('status', 1)
            ->get();

        $typeMap  = $this->buildTypeCategoryMap($parentCategories);
        $parentId = $typeMap[Str::slug($bookingType)] ?? ($typeMap[$bookingType] ?? null);

        if (empty($parentId)) {
            return [];
        }

        return BookingCategory::query()
            ->where('parent_id', $parentId)
            ->where('status', 1)
            ->pluck('id')
            ->all();
    }

    private function findOwnBooking($id): Booking
    {
        return Booking::query()
            ->where('creator_id', auth()->id())
            ->findOrFail($id);
    }

    private function isEnabledRequestValue($value): bool
    {
        return in_array($value, [1, '1', true, 'true', 'on'], true);
    }

    private function sendBookingNotification(Booking $booking, string $template): void
    {
        $notifyOptions = [
            '[c.title]'    => $booking->title,
            '[item_title]' => $booking->title,
            '[u.name]'     => optional(auth()->user())->full_name,
        ];

        sendNotification($template, $notifyOptions, 1);
    }
}
