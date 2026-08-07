<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Exports\BookingsExport;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingOrder;
use App\Models\Role;
use App\Services\BookingTemplateConfig;
use App\Services\BookingSubTemplateConfig;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class BookingController extends Controller
{
    // ── Index / New Booking Form ───────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorize('admin_booking');
        removeContentLocale();

        if (!$request->session()->has('errors')) {
            $request->session()->forget('_old_input');
        }

        $request->session()->put('admin_booking_draft_id', (string) Str::uuid());

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

        $bookingTypes           = array_keys(BookingTemplateConfig::allTypes());
        $bookingTypeLabels      = BookingTemplateConfig::allTypes();
        $bookingTypeCategoryMap = $this->buildTypeCategoryMap($parentCategories);
        $allCategories          = BookingCategory::orderBy('order')->get();
        $userLanguages          = $this->getUserLanguages();
        $instructors            = $this->getInstructors();

        // Template configs as JSON for JS dynamic field switching (Booking Type level)
        $templateConfigs = $this->buildTemplateConfigsForJs();

        // Category (subcategory/template) level config — 23 templates
        // (Doctor Appointment, Clinic Visit, Event Tickets, ...). Key = category slug.
        $subTemplateConfigs = $this->buildSubTemplateConfigsForJs();

        // Har parent (booking type) ke child categories ka JS-friendly map
        // (id + title + slug). Booking Type select hone par JS isi map se
        // sirf usi parent ke children Category dropdown mein dikhata hai,
        // aur slug se sahi sub-template (23 templates mein se) match karta hai.
        $categoriesByParent = $this->buildCategoriesByParentMap($childCategories);

        return view('admin.booking.booking', [
            'pageTitle'              => trans('admin/main.create_booking'),
            'bookingPageMode'        => 'form',
            'productCategories'      => $productCategories,
            'parentCategories'       => $parentCategories,
            'childCategories'        => $childCategories,
            'bookingTypes'           => $bookingTypes,
            'bookingTypeLabels'      => $bookingTypeLabels,
            'bookingTypeCategoryMap' => $bookingTypeCategoryMap,
            'categories'             => $parentCategories,
            'allCategories'          => $allCategories,
            'userLanguages'          => $userLanguages,
            'instructors'            => $instructors,
            'templateConfigs'        => json_encode($templateConfigs),
            'subTemplateConfigs'     => json_encode($subTemplateConfigs),
            'categoriesByParent'     => json_encode($categoriesByParent),
            'draftId'                => $request->session()->get('admin_booking_draft_id'),
        ]);
    }

    public function categoriesByType(Request $request)
    {
        $this->authorize('admin_booking');

        $type = (string) $request->get('booking_type', '');

        if (empty($type) || !array_key_exists($type, BookingTemplateConfig::allTypes())) {
            return response()->json(['categories' => []]);
        }

        $validCategoryIds = $this->validCategoryIdsForBookingType($type);

        $categories = BookingCategory::query()
            ->whereIn('id', $validCategoryIds)
            ->whereNotNull('parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get(['id', 'title', 'slug']);

        return response()->json([
            'categories' => $categories->map(fn($category) => [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
            ])->values(),
        ]);
    }

    // ── List ──────────────────────────────────────────────────────────

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

    // ── Store ─────────────────────────────────────────────────────────

    public function store(Request $request)
    {
        $this->authorize('admin_booking_create');

        // 1. Get template config for selected booking type (parent level)
        $templateConfig = BookingTemplateConfig::for($request->booking_type ?? '');

        // 2. Agar selected category kisi specific sub-template (23
        //    templates mein se) se match karti hai, uski config bhi lo.
        $categorySlug   = $this->categorySlugFromId($request->category_id);
        $subTemplate    = BookingSubTemplateConfig::forSlug($categorySlug);

        // 3. Build validation rules: global + booking-type rules + (agar
        //    mila to) sub-template rules. Sub-template rules baad mein
        //    merge hoti hain taake wo required/optional ko override kar
        //    saken (zyada specific config jeetni chahiye).
        $validationRules = $this->bookingValidationRules(
            $request,
            $templateConfig,
            $subTemplate
        );

        $this->validate($request, $validationRules, $this->validationMessages(), $this->validationAttributes());

        $nextOrder = (Booking::max('order') ?? 0) + 1;

        // 4. Build meta: global meta + har template/sub-template ka
        //    bheja hua meta.* field (FIX: pehle sirf booking-type level
        //    config ke fields() se match hone wale meta keys save hote
        //    the, is wajah se sub-template (23 templates) ke bohat se
        //    fields — jaise meta.required_docs, meta.room_type,
        //    meta.check_in_date, meta.amenities — silently drop ho jate
        //    the. Ab extractTemplateMeta() form se aane wala HAR meta
        //    field save karta hai, chahe kisi bhi category ka ho.)
        $meta = array_merge(
            $this->bookingMeta($request),
            $this->extractTemplateMeta($request, $templateConfig, $subTemplate)
        );

        // price_unit — agar sub-template mila to uski price_unit ko
        // priority do (booking-type level se zyada specific).
        $resolvedPriceUnit = $request->price_unit
            ?: ($subTemplate ? $subTemplate->priceUnit() : $templateConfig->priceUnitLabel());
        $locationEnabled = $request->boolean('location_enabled');

        $booking = Booking::create([
            'creator_id'       => $request->creator_id ?: auth()->id(),
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'language'         => $request->language ?? app()->getLocale(),
            'slug'             => $request->slug
                                    ? Str::slug($request->slug)
                                    : Str::slug($request->title) . '-' . uniqid(),
            'booking_type'     => $request->booking_type,
            'status'           => $request->status ?: 'draft',
            'sub_type'         => $request->sub_type,
            'description'      => $request->description,
            'requirements'     => $request->requirements,
            'thumbnail'        => $request->thumbnail,
            'cover'            => $request->cover,
            'order'            => $request->order ?: $nextOrder,

            // Pricing
            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,
            'price_unit'       => $resolvedPriceUnit,
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
            'location_enabled' => $locationEnabled,
            'address_line'     => $locationEnabled ? $request->address_line : null,
            'city'             => $locationEnabled ? $request->city : null,
            'state'            => $locationEnabled ? $request->state : null,
            'country'          => $locationEnabled ? $request->country : null,
            'postal_code'      => $locationEnabled ? $request->postal_code : null,
            'lat'              => $locationEnabled ? ($request->lat ?: null) : null,
            'lng'              => $locationEnabled ? ($request->lng ?: null) : null,

            // Status & misc
        'featured'         => $request->boolean('featured'),
            'forum_enabled'    => $request->boolean('forum_enabled'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'reviews_enabled'  => $request->boolean('reviews_enabled'),
            'qr_enabled'       => $request->boolean('qr_enabled'),
            'sales'            => 0,
            'views'            => 0,
            'rating'           => 0,
            'review_count'     => 0,
            'reviewer_message' => $request->reviewer_message ?: null,
            'checkout_message' => $request->checkout_message ?: null,
            'meta'             => $meta,
            'allowed_customer_groups' => !empty($request->allowed_customer_groups) ? $request->allowed_customer_groups : null,
        ]);

        $this->sendBookingNotification($booking, 'booking_created');

        return redirect(getAdminPanelUrl('/booking/list'))
            ->with('success', 'Booking created successfully.');
    }

    // ── Edit ──────────────────────────────────────────────────────────

    public function edit($id)
    {
        $this->authorize('admin_booking_edit');
        removeContentLocale();

        $editBooking = Booking::findOrFail($id);
        $bookings    = Booking::orderBy('created_at', 'desc')->paginate(15);

        // Load template config for this booking's type
        $templateConfig     = BookingTemplateConfig::for($editBooking->booking_type ?? '');
        $templateConfigs    = $this->buildTemplateConfigsForJs();
        $subTemplateConfigs = $this->buildSubTemplateConfigsForJs();

        $parentCategories = BookingCategory::whereNull('parent_id')
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        $childCategories = BookingCategory::whereNotNull('parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get();

        $bookingTypes           = array_keys(BookingTemplateConfig::allTypes());
        $bookingTypeLabels      = BookingTemplateConfig::allTypes();
        $bookingTypeCategoryMap = $this->buildTypeCategoryMap($parentCategories);
        $allCategories          = BookingCategory::orderBy('order')->get();
        $userLanguages          = $this->getUserLanguages();
        $instructors            = $this->getInstructors($editBooking->creator_id);

        // Edit form ke liye bhi parent->children (id+title+slug) map chahiye
        // taake saved booking_type + category ke hisab se sahi dropdown aur
        // sahi sub-template (23 templates) dono load hon.
        $categoriesByParent = $this->buildCategoriesByParentMap($childCategories);

        return view('admin.booking.booking', [
            'pageTitle'              => trans('admin/main.edit_booking'),
            'bookingPageMode'        => 'form',
            'bookings'               => $bookings,
            'editBooking'            => $editBooking,
            'activeTemplateConfig'   => $templateConfig,
            'parentCategories'       => $parentCategories,
            'childCategories'        => $childCategories,
            'bookingTypes'           => $bookingTypes,
            'bookingTypeLabels'      => $bookingTypeLabels,
            'bookingTypeCategoryMap' => $bookingTypeCategoryMap,
            'categories'             => $parentCategories,
            'allCategories'          => $allCategories,
            'userLanguages'          => $userLanguages,
            'instructors'            => $instructors,
            'templateConfigs'        => json_encode($templateConfigs),
            'subTemplateConfigs'     => json_encode($subTemplateConfigs),
            'categoriesByParent'     => json_encode($categoriesByParent),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_edit');

        $booking        = Booking::findOrFail($id);
        $templateConfig = BookingTemplateConfig::for($request->booking_type ?? $booking->booking_type ?? '');

        // Sub-template (category level) config
        $categorySlug = $this->categorySlugFromId($request->category_id ?? $booking->category_id);
        $subTemplate  = BookingSubTemplateConfig::forSlug($categorySlug);

        $validationRules = $this->bookingValidationRules(
            $request,
            $templateConfig,
            $subTemplate,
            $booking->id,
            $booking->booking_type ?? ''
        );

        $this->validate($request, $validationRules, $this->validationMessages(), $this->validationAttributes());

        // Merge existing meta with new template meta (preserve non-overwritten keys).
        // FIX: extractTemplateMeta() ab sub-template bhi pass leta hai aur
        // form se aane wala HAR meta.* field save karta hai (pehle sirf
        // booking-type level config ke fields() se match hone wale keys
        // save hote the, baqi silently discard ho jate the).
        $existingMeta = $booking->meta ?? [];
        $newMeta      = array_merge(
            $existingMeta,
            $this->bookingMeta($request),
            $this->extractTemplateMeta($request, $templateConfig, $subTemplate)
        );

        $resolvedPriceUnit = $request->price_unit
            ?: ($subTemplate ? $subTemplate->priceUnit() : $templateConfig->priceUnitLabel());
        $locationEnabled = $request->boolean('location_enabled');

        $booking->update([
            'creator_id'       => $request->creator_id ?: $booking->creator_id,
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'language'         => $request->language ?? $booking->language,
            'slug'             => $request->slug ? Str::slug($request->slug) : $booking->slug,
            'booking_type'     => $request->booking_type,
            'status'           => $request->status ?: $booking->status,
            'sub_type'         => $request->sub_type,
            'description'      => $request->description,
            'requirements'     => $request->requirements,
            'thumbnail'        => $request->thumbnail,
            'cover'            => $request->cover,
            'order'            => $request->has('order') ? $request->order : $booking->order,

            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,
            'price_unit'       => $resolvedPriceUnit,
            'discount_price'   => $request->discount_price ?: null,
            'currency'         => $request->currency ?? 'USD',
            'tax'              => $request->tax ?? 0,
            'commission'       => $request->commission ?? 0,
            'deposit_enabled'  => $request->boolean('deposit_enabled'),
            'deposit_amount'   => $request->boolean('deposit_enabled') ? ($request->deposit_amount ?: null) : null,
            'deposit_type'     => $request->boolean('deposit_enabled') ? $request->deposit_type : null,

            'min_persons'      => $request->min_persons ?? 1,
            'max_persons'      => $request->max_persons ?: null,
            'max_children'     => $request->max_children ?: null,
            'children_allowed' => $request->boolean('children_allowed'),
            'capacity'         => $request->capacity ?: null,

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

            'location_enabled' => $locationEnabled,
            'address_line'     => $locationEnabled ? $request->address_line : null,
            'city'             => $locationEnabled ? $request->city : null,
            'state'            => $locationEnabled ? $request->state : null,
            'country'          => $locationEnabled ? $request->country : null,
            'postal_code'      => $locationEnabled ? $request->postal_code : null,
            'lat'              => $locationEnabled ? ($request->lat ?: null) : null,
            'lng'              => $locationEnabled ? ($request->lng ?: null) : null,

            'featured'         => $request->boolean('featured'),
            'forum_enabled'    => $request->boolean('forum_enabled'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'reviews_enabled'  => $request->boolean('reviews_enabled'),
            'qr_enabled'       => $request->boolean('qr_enabled'),
            'reviewer_message' => $request->reviewer_message ?: null,
            'checkout_message' => $request->checkout_message ?: null,
            'meta'             => $newMeta,
            'allowed_customer_groups' => !empty($request->allowed_customer_groups) ? $request->allowed_customer_groups : null,
        ]);

        $this->sendBookingNotification($booking, 'booking_updated');

        return redirect(getAdminPanelUrl('/booking/list'))
            ->with('success', 'Booking updated successfully.');
    }

    // ── Delete ────────────────────────────────────────────────────────

    public function delete($id)
    {
        $this->authorize('admin_booking_delete');

        Booking::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/list'))
            ->with('success', 'Booking deleted successfully.');
    }
      public function regenerateQr($id)
    {
        $this->authorize('admin_booking_edit');

        $booking = Booking::findOrFail($id);

        if (empty($booking->qr_enabled)) {
            return back()->with('error', 'QR Code is not enabled for this booking.');
        }

        app(\App\Services\PusClient::class)->createLink($booking);

        return redirect(getAdminPanelUrl('/booking/' . $id . '/edit'))
            ->with('success', 'QR Code and Short URL re-generated successfully.');
    }

    // ── Export Excel ──────────────────────────────────────────────────

    public function exportExcel(Request $request)
    {
        $this->authorize('admin_booking');

        $query = Booking::query();

        if (!empty($request->get('in_house_bookings'))) {
            $adminRoleIds = Role::where('is_admin', true)->pluck('id')->toArray();
            $query->whereHas('creator', fn($q) => $q->whereIn('role_id', $adminRoleIds));
        }

        $bookings = $this->handleFilters($query, $request)
            ->with(['category', 'creator' => fn($qu) => $qu->select('id', 'full_name')])
            ->withCount(['orders as sales_count' => fn($qu) => $qu->whereIn('status', $this->paidBookingOrderStatuses())])
            ->addSelect(['booking_income' => BookingOrder::query()
                ->selectRaw('coalesce(sum(sales.total_amount), 0)')
                ->join('sales', fn($join) => $join->on('sales.id', '=', 'booking_orders.sale_id')->whereNull('sales.refund_at'))
                ->whereColumn('booking_orders.booking_id', 'bookings.id')
                ->whereIn('booking_orders.status', $this->paidBookingOrderStatuses())
            ])
            ->get();

        return Excel::download(new BookingsExport($bookings), 'bookings.xlsx');
    }

    // ── Private: Shared list builder ──────────────────────────────────

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
            $query->whereHas('creator', fn($q) => $q->whereIn('role_id', $adminRoleIds));
        }

        $topStatData = $this->getTopPageStats(deepClone($query));

        $query = $this->handleFilters($query, $request)
            ->with(['category', 'creator' => fn($qu) => $qu->select('id', 'full_name')])
            ->withCount(['orders as sales_count' => fn($qu) => $qu->whereIn('status', $this->paidBookingOrderStatuses())])
            ->addSelect(['booking_income' => BookingOrder::query()
                ->selectRaw('coalesce(sum(sales.total_amount), 0)')
                ->join('sales', fn($join) => $join->on('sales.id', '=', 'booking_orders.sale_id')->whereNull('sales.refund_at'))
                ->whereColumn('booking_orders.booking_id', 'bookings.id')
                ->whereIn('booking_orders.status', $this->paidBookingOrderStatuses())
            ]);

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
            'bookingTypeLabels' => BookingTemplateConfig::allTypes(),
        ];

        return view('admin.booking.booking', array_merge($data, $topStatData));
    }

    // ── Private: Template config for JavaScript ───────────────────────

    /**
     * Build a JS-friendly array of all Booking Type (parent level) configs.
     * Used by the frontend to switch form sections dynamically.
     */
    private function buildTemplateConfigsForJs(): array
    {
        $configs = [];
        foreach (BookingTemplateConfig::allTypes() as $slug => $label) {
            $config                = BookingTemplateConfig::for($slug);
            $configs[$slug] = [
                'label'             => $label,
                'fields'            => $config->fields(),
                'field_labels'      => $config->fieldLabels(),
                'required'          => $config->required(),
                'pricing_mode'      => $config->pricingMode(),
                'availability_mode' => $config->availabilityMode(),
                'price_unit_label'  => $config->priceUnitLabel(),
                'has_staff'         => $config->hasStaff(),
                'has_date_range'    => $config->hasDateRange(),
                'has_time_slot'     => $config->hasTimeSlot(),
                'has_extras'        => $config->hasExtras(),
                'filters'           => $config->filters(),
                'meta'              => $config->meta(),
            ];
        }
        return $configs;
    }

    /**
     * Build a JS-friendly array of all 23 Category-level (sub-template)
     * configs, keyed by category slug — e.g. 'doctor-appointment' => [...].
     * Frontend JS isko use karke, jab admin category select kare, required/
     * optional fields aur price unit ko further filter karta hai.
     */
    private function buildSubTemplateConfigsForJs(): array
    {
        $configs = [];
        foreach (BookingSubTemplateConfig::all() as $slug => $raw) {
            $sub            = BookingSubTemplateConfig::forSlug($slug);
            $configs[$slug] = $sub->toArray();
        }
        return $configs;
    }

    /**
     * category_id se uska slug nikalna — sub-template match karne
     * ke liye zaroori hai (validation + price_unit resolve karne ke liye).
     */
    private function categorySlugFromId($categoryId): ?string
    {
        if (empty($categoryId)) {
            return null;
        }
        return BookingCategory::where('id', $categoryId)->value('slug');
    }

    /**
     * Har parent category id ke against uske active children
     * (id + title + slug) ka array — Category dropdown JS se filter
     * karne ke liye, aur slug se 23-template config match karne ke liye.
     * Format: [ parent_id => [ ['id'=>.., 'title'=>.., 'slug'=>..], ... ], ... ]
     */
    private function buildCategoriesByParentMap($childCategories): array
    {
        $map = [];
        foreach ($childCategories as $child) {
            $map[$child->parent_id][] = [
                'id'    => $child->id,
                'title' => $child->title,
                'slug'  => $child->slug,
            ];
        }
        return $map;
    }

    /**
     * category_id validation rule — sirf usi parent ke children allow
     * hote hain jo selected booking_type se map hote hain. Invalid combination
     * (e.g. Doctors booking_type + Beauty subcategory) validation fail karega.
     */
    private function categoryValidationRule(string $bookingType): array
    {
        $validCategoryIds = $this->validCategoryIdsForBookingType($bookingType);

        return [
            'required',
            Rule::exists('booking_categories', 'id')->where(function ($q) use ($validCategoryIds) {
                if (!empty($validCategoryIds)) {
                    $q->whereIn('id', $validCategoryIds);
                } else {
                    // booking_type map nahi hua to koi bhi category valid na maani jaye
                    $q->whereRaw('1 = 0');
                }
            }),
        ];
    }

    private function bookingValidationRules(
        Request $request,
        BookingTemplateConfig $templateConfig,
        ?BookingSubTemplateConfig $subTemplate = null,
        ?int $ignoreId = null,
        ?string $fallbackBookingType = null
    ): array {
        $templateRules = $templateConfig->rules();

        $rules = array_merge(
            $this->globalValidationRules($request, $ignoreId),
            $templateRules,
            $subTemplate ? $subTemplate->rules() : []
        );

        if ($subTemplate) {
            $subTemplateFields = $subTemplate->relevantFields();

            foreach (array_keys($templateRules) as $field) {
                if ($this->isGlobalValidationField($field)) {
                    continue;
                }

                if (!in_array($field, $subTemplateFields, true)) {
                    unset($rules[$field]);
                }
            }
        }

        $bookingType = $request->booking_type ?? $fallbackBookingType ?? '';
        $rules['category_id'] = $this->categoryValidationRule($bookingType);

        return $rules;
    }

    private function isGlobalValidationField(string $field): bool
    {
        return in_array($field, [
            'title',
            'slug',
            'category_id',
            'language',
            'booking_type',
            'status',
            'creator_id',
            'tax',
            'commission',
            'deposit_amount',
            'discount_price',
            'price',
            'price_per',
        ], true);
    }

    private function validationMessages(): array
    {
        return [
            'category_id.required' => 'Please select a category for this booking type.',
            'category_id.exists'   => 'The selected category does not match the selected booking type. Please choose a category from the selected booking type.',
        ];
    }

    private function validationAttributes(): array
    {
        return [
            'booking_type'           => 'booking type',
            'category_id'            => 'category',
            'sub_type'               => 'sub type',
            'staff_id'               => 'staff member',
            'duration_minutes'       => 'duration',
            'max_persons'            => 'maximum guests',
            'max_children'           => 'maximum children',
            'meta.appointment_type'  => 'appointment type',
            'meta.payment_option'    => 'payment option',
            'meta.online_link'       => 'online meeting link',
            'meta.required_docs'     => 'required documents',
            'meta.service_type'      => 'service type',
            'meta.vehicle_type'      => 'vehicle information',
            'meta.required_notes'    => 'required notes',
            'meta.pickup_location'   => 'pickup location',
            'meta.dropoff_location'  => 'drop-off location',
            'meta.vehicle_specs'     => 'vehicle specifications',
            'meta.room_type'         => 'room or resource',
            'meta.venue_type'        => 'venue type',
            'meta.specifications'    => 'specifications',
            'meta.level'             => 'level',
            'meta.prerequisites'     => 'prerequisites',
            'meta.check_in_date'     => 'check-in date',
            'meta.check_out_date'    => 'check-out date',
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
        $parentId = $typeMap[$bookingType] ?? null;

        if (empty($parentId)) {
            return [];
        }

        return BookingCategory::query()
            ->where('parent_id', $parentId)
            ->where('status', 1)
            ->pluck('id')
            ->all();
    }

    /**
     * Extract template/sub-template-specific meta fields from the request.
     *
     * FIX (important): pehle ye function sirf BookingTemplateConfig
     * (booking-type/parent level, 7 types) ke fields() array se match
     * hone wale 'meta.*' keys save karta tha. Lekin aapke 23 sub-templates
     * (category level) mein bohat se meta fields aise hain jo parent-level
     * config mein declare nahi — jaise 'meta.required_docs' (Doctor
     * Appointment ke optional mein hai lekin doctorsClinicsConfig ke
     * fields() mein nahi), 'meta.room_type', 'meta.check_in_date',
     * 'meta.amenities', 'meta.vehicle_specs' waghera. Nateeja: form se
     * value aati thi lekin DB mein save nahi hoti thi — silently discard.
     *
     * Naya approach: chunke `meta` ek JSON column hai (koi fixed schema
     * nahi), ab ye function form se aane wala HAR `meta[...]` field save
     * karta hai — chahe wo kisi bhi Booking Type ya kisi bhi 23-template
     * ka ho. Isse:
     *   - Koi bhi category select ho, uske specific fields hamesha save hote hain.
     *   - Naya sub-template (23 mein se koi bhi) add/edit karo, controller
     *     mein kuch change karne ki zaroorat nahi — meta automatically save hoga.
     *   - staff_id aur extras bhi ab unconditional save hote hain (pehle
     *     hasStaff()/hasExtras() flag ke peeche gated the, jo sub-template
     *     level par galat results deta tha).
     */
    private function extractTemplateMeta(Request $request, BookingTemplateConfig $config, ?BookingSubTemplateConfig $subTemplate = null): array
    {
        $meta = [];

        // Staff / Provider — jis form/section mein bhi staff_id aaya,
        // hamesha meta.staff_id ke naam se save hoga.
        if ($request->filled('staff_id')) {
            $meta['staff_id'] = $request->staff_id;
        }

        // Extras / Add-ons — top-level 'extras[]' array (name/price rows).
        // Kai sub-templates (Beauty, Events, Accommodation, Education, ...)
        // isi ek shared UI section ko use karte hain.
        if ($request->has('extras')) {
            $extras = collect($request->input('extras', []))
                ->filter(fn($row) => !empty($row['name']) || !empty($row['price']))
                ->values()
                ->all();
            $meta['extras'] = $extras;
        }

        // Har meta.* field jo form ne bheja — sub-template ya booking-type
        // config mein declared ho ya na ho, sab save hota hai.
        $requestMeta = $request->input('meta', []);

        foreach ($requestMeta as $key => $value) {
            if ($value === null) {
                continue;
            }

            if (is_array($value)) {
                // e.g. meta[amenities][] checkboxes — khali values nikal do
                $meta[$key] = collect($value)->filter(fn($v) => $v !== null && $v !== '')->values()->all();
                continue;
            }

            $trimmed = is_string($value) ? trim($value) : $value;

            // Khali string ko save mat karo (taake purani saved value
            // accidentally empty se overwrite na ho jaye update ke waqt)
            if ($trimmed === '') {
                continue;
            }

            $meta[$key] = $trimmed;
        }

        return $meta;
    }

    // ── Private: Validation rules ─────────────────────────────────────

    private function globalValidationRules(Request $request, ?int $ignoreId = null): array
    {
        $slugRule = ['nullable', 'string', 'max:255'];
        $slugRule[] = $ignoreId
            ? Rule::unique('bookings', 'slug')->ignore($ignoreId)
            : Rule::unique('bookings', 'slug');

        return [
            'title'          => 'required|string|max:255',
            'slug'           => $slugRule,
            // NOTE: category_id ka rule ab yahan se nahi, categoryValidationRule() se
            // controller mein overwrite hota hai (booking_type ke hisab se filtered).
            'language'       => 'nullable|string|max:10',
            'booking_type'   => 'required|string|max:255',
            'status'         => 'nullable|in:draft,pending,published,rejected,inactive',
            'creator_id'     => 'nullable|exists:users,id',
            'tax'            => 'nullable|numeric|min:0|max:999.99',
            'commission'     => 'nullable|numeric|min:0|max:999.99',
            'deposit_amount' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'price_per'      => 'nullable|numeric|min:0',
        ];
    }

    // ── Private: Filters ──────────────────────────────────────────────

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

        if (!empty($creatorIds) && is_array($creatorIds)) {
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

        // City filter (applies to all types)
        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Price range filter
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        // sub_type filter (online/in-person, rental/service)
        if ($request->filled('sub_type')) {
            $query->where('sub_type', $request->sub_type);
        }

        // Language filter
        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        switch ($sort) {
            case 'sales_asc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', $this->paidBookingOrderStatuses());
                    })
                    ->select('bookings.*', DB::raw('count(booking_orders.id) as orders_count'))
                    ->groupBy('bookings.id')
                    ->orderBy('orders_count', 'asc');
                break;

            case 'sales_desc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', $this->paidBookingOrderStatuses());
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
                             ->whereIn('booking_orders.status', $this->paidBookingOrderStatuses());
                    })
                    ->leftJoin('sales', function ($join) {
                        $join->on('sales.id', '=', 'booking_orders.sale_id')
                            ->whereNull('sales.refund_at');
                    })
                    ->select('bookings.*', DB::raw('coalesce(sum(sales.total_amount), 0) as income_amount'))
                    ->groupBy('bookings.id')
                    ->orderBy('income_amount', 'asc');
                break;

            case 'income_desc':
                $query->leftJoin('booking_orders', function ($join) {
                        $join->on('bookings.id', '=', 'booking_orders.booking_id')
                             ->whereIn('booking_orders.status', $this->paidBookingOrderStatuses());
                    })
                    ->leftJoin('sales', function ($join) {
                        $join->on('sales.id', '=', 'booking_orders.sale_id')
                            ->whereNull('sales.refund_at');
                    })
                    ->select('bookings.*', DB::raw('coalesce(sum(sales.total_amount), 0) as income_amount'))
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

            default:
                $query->orderBy('bookings.created_at', 'desc');
                break;
        }

        return $query;
    }

    // ── Private: Helpers ──────────────────────────────────────────────

    private function buildTypeCategoryMap($parentCategories): array
    {
        $map = [];
        foreach ($parentCategories as $category) {
            $map[Str::slug($category->slug)]  = $category->id;
            $map[Str::slug($category->title)] = $category->id;
        }
        return $map;
    }

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

    private function paidBookingOrderStatuses(): array
    {
        return [
            BookingOrder::$waitingDelivery,
            BookingOrder::$shipped,
            BookingOrder::$success,
            'confirmed',
            'completed',
        ];
    }

    private function getTopPageStats($query): array
    {
        $totalBookings = deepClone($query)->count();
        $bookingIds    = deepClone($query)->pluck('bookings.id');

        $totalBookingSales = 0;
        $totalCustomers    = 0;

        if ($bookingIds->count()) {
            $totalBookingSales = BookingOrder::whereIn('booking_id', $bookingIds)
                ->whereIn('status', $this->paidBookingOrderStatuses())
                ->count();

            $totalCustomers = BookingOrder::whereIn('booking_id', $bookingIds)
                ->whereIn('status', $this->paidBookingOrderStatuses())
                ->whereNotNull('buyer_id')
                ->distinct('buyer_id')
                ->count('buyer_id');
        }

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

    private function getSelectedSellers(Request $request)
    {
        $creatorIds = $request->get('creator_ids', []);
        if (empty($creatorIds) || !is_array($creatorIds)) {
            return collect();
        }
        return User::query()->select('id', 'full_name')->whereIn('id', $creatorIds)->get();
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
                                        ->map(fn($tag) => trim($tag))
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
