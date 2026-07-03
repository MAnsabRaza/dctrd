<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Exports\BookingsExport;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingOrder;
use App\Models\Role;
use App\Services\BookingTemplateConfig;
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

        return view('admin.booking.booking', [
            'pageTitle'         => trans('admin/main.create_booking'),
            'bookingPageMode'   => 'form',
            'productCategories' => $productCategories,
            'parentCategories'  => $parentCategories,
            'childCategories'   => $childCategories,
            'bookingTypes'      => BookingTemplateConfig::allTypes(),           // 7, for the top-level select
            'categories'        => $parentCategories,
            'allCategories'     => BookingCategory::orderBy('order')->get(),
            'userLanguages'     => $this->getUserLanguages(),
            'instructors'       => $this->getInstructors(),

            // NEW: subcategories grouped by their parent booking type,
            // so the Category dropdown can be filtered client-side
            'categoriesByType'  => json_encode($this->buildCategoriesByType($childCategories)),

            // NEW: 23-template field configs, keyed by template_key (not booking_type)
            'templateConfigs'   => json_encode($this->buildTemplateConfigsForJs()),
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

        // Resolve the template from the selected category (subcategory),
        // NOT just from booking_type — this is what drives the 23 distinct field sets.
        $category       = BookingCategory::find($request->category_id);
        $templateConfig = $category && $category->template_key
            ? BookingTemplateConfig::for($category->template_key)
            : BookingTemplateConfig::for('');

        $validationRules = array_merge(
            $this->globalValidationRules($request),
            $this->categoryMatchesTypeRule($request),
            $templateConfig->rules()
        );

        $this->validate($request, $validationRules);

        $nextOrder = (Booking::max('order') ?? 0) + 1;

        $meta = array_merge(
            $this->bookingMeta($request),
            $this->extractTemplateMeta($request, $templateConfig)
        );

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

            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,
            'price_unit'       => $request->price_unit ?: $templateConfig->priceUnitLabel(),
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

            'location_enabled' => $request->boolean('location_enabled'),
            'address_line'     => $request->address_line,
            'city'             => $request->city,
            'state'            => $request->state,
            'country'          => $request->country,
            'postal_code'      => $request->postal_code,
            'lat'              => $request->lat ?: null,
            'lng'              => $request->lng ?: null,

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
            'meta'             => $meta,
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

        $category       = $editBooking->category;
        $templateConfig = $category && $category->template_key
            ? BookingTemplateConfig::for($category->template_key)
            : BookingTemplateConfig::for('');

        $parentCategories = BookingCategory::whereNull('parent_id')
            ->where('status', 1)
            ->orderBy('order')
            ->get();

        $childCategories = BookingCategory::whereNotNull('parent_id')
            ->where('status', 1)
            ->orderBy('title')
            ->get();

        return view('admin.booking.booking', [
            'pageTitle'            => trans('admin/main.edit_booking'),
            'bookingPageMode'      => 'form',
            'bookings'             => $bookings,
            'editBooking'          => $editBooking,
            'activeTemplateConfig' => $templateConfig,
            'parentCategories'     => $parentCategories,
            'childCategories'      => $childCategories,
            'bookingTypes'         => BookingTemplateConfig::allTypes(),
            'categories'           => $parentCategories,
            'allCategories'        => BookingCategory::orderBy('order')->get(),
            'userLanguages'        => $this->getUserLanguages(),
            'instructors'          => $this->getInstructors($editBooking->creator_id),
            'categoriesByType'     => json_encode($this->buildCategoriesByType($childCategories)),
            'templateConfigs'      => json_encode($this->buildTemplateConfigsForJs()),
        ]);
    }

    // ── Update ────────────────────────────────────────────────────────

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_edit');

        $booking  = Booking::findOrFail($id);
        $category = BookingCategory::find($request->category_id ?? $booking->category_id);

        $templateConfig = $category && $category->template_key
            ? BookingTemplateConfig::for($category->template_key)
            : BookingTemplateConfig::for('');

        $validationRules = array_merge(
            $this->globalValidationRules($request, $booking->id),
            $this->categoryMatchesTypeRule($request),
            $templateConfig->rules()
        );

        $this->validate($request, $validationRules);

        $existingMeta = $booking->meta ?? [];
        $newMeta      = array_merge(
            $existingMeta,
            $this->bookingMeta($request),
            $this->extractTemplateMeta($request, $templateConfig)
        );

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
            'price_unit'       => $request->price_unit ?: $templateConfig->priceUnitLabel(),
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

            'location_enabled' => $request->boolean('location_enabled'),
            'address_line'     => $request->address_line,
            'city'             => $request->city,
            'state'            => $request->state,
            'country'          => $request->country,
            'postal_code'      => $request->postal_code,
            'lat'              => $request->lat ?: null,
            'lng'              => $request->lng ?: null,

            'featured'         => $request->boolean('featured'),
            'forum_enabled'    => $request->boolean('forum_enabled'),
            'comments_enabled' => $request->boolean('comments_enabled'),
            'reviews_enabled'  => $request->boolean('reviews_enabled'),
            'reviewer_message' => $request->reviewer_message ?: null,
            'checkout_message' => $request->checkout_message ?: null,
            'meta'             => $newMeta,
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
            ->withCount(['orders as sales_count' => fn($qu) => $qu->whereIn('status', ['confirmed', 'completed'])])
            ->addSelect(['booking_income' => BookingOrder::query()
                ->selectRaw('coalesce(sum(sales.total_amount), 0)')
                ->join('sales', fn($join) => $join->on('sales.id', '=', 'booking_orders.sale_id')->whereNull('sales.refund_at'))
                ->whereColumn('booking_orders.booking_id', 'bookings.id')
                ->whereIn('booking_orders.status', ['confirmed', 'completed'])
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
            ->withCount(['orders as sales_count' => fn($qu) => $qu->whereIn('status', ['confirmed', 'completed'])])
            ->addSelect(['booking_income' => BookingOrder::query()
                ->selectRaw('coalesce(sum(sales.total_amount), 0)')
                ->join('sales', fn($join) => $join->on('sales.id', '=', 'booking_orders.sale_id')->whereNull('sales.refund_at'))
                ->whereColumn('booking_orders.booking_id', 'bookings.id')
                ->whereIn('booking_orders.status', ['confirmed', 'completed'])
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

    // ── Private: Template config for JavaScript (23 templates, not 7 types) ──

    private function buildTemplateConfigsForJs(): array
    {
        $configs = [];
        foreach (BookingTemplateConfig::TEMPLATES as $key => $definition) {
            $config          = BookingTemplateConfig::for($key);
            $configs[$key] = [
                'label'             => $config->label(),
                'parent_type'       => $config->parentType(),
                'fields'            => $config->fields(),
                'field_labels'      => $config->fieldLabels(),
                'required'          => $config->required(),
                'optional'          => $config->optional(),
                'checkout_modules'  => $config->checkoutModules(),
                'pricing_mode'      => $config->pricingMode(),
                'availability_mode' => $config->availabilityMode(),
                'price_unit_label'  => $config->priceUnitLabel(),
                'has_staff'         => $config->hasStaff(),
                'has_date_range'    => $config->hasDateRange(),
                'has_time_slot'     => $config->hasTimeSlot(),
                'has_extras'        => $config->hasExtras(),
            ];
        }
        return $configs;
    }

    /** Subcategories grouped by their parent booking_type, for client-side Category filtering */
    private function buildCategoriesByType($childCategories): array
    {
        $map = [];
        foreach ($childCategories as $child) {
            $type = $child->effective_booking_type;
            if (empty($type)) {
                continue; // orphan child without a resolvable type — admin must fix in Categories screen
            }
            $map[$type][] = [
                'id'           => $child->id,
                'title'        => $child->title,
                'template_key' => $child->template_key,
            ];
        }
        return $map;
    }

    private function extractTemplateMeta(Request $request, BookingTemplateConfig $config): array
    {
        $meta         = [];
        $metaFields   = array_filter($config->fields(), fn($f) => str_starts_with($f, 'meta.'));
        $requestMeta  = $request->input('meta', []);

        if ($config->hasStaff() && $request->filled('staff_id')) {
            $meta['staff_id'] = $request->staff_id;
        }

        if ($config->hasExtras() && $request->has('extras')) {
            $meta['extras'] = $request->input('extras', []);
        }

        foreach ($metaFields as $field) {
            $key = str_replace('meta.', '', $field);
            if (array_key_exists($key, $requestMeta)) {
                $meta[$key] = $requestMeta[$key];
            }
        }

        // Persist which template produced this booking, useful for reporting/debugging
        $meta['template_key'] = $config->key();

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
            'category_id'    => 'required|exists:booking_categories,id',
            'language'       => 'nullable|string|max:10',
            'booking_type'   => ['required', 'string', Rule::in(array_keys(BookingTemplateConfig::allTypes()))],
            'status'         => 'nullable|in:draft,pending,published,rejected,inactive',
            'creator_id'     => 'nullable|exists:users,id',
            'tax'            => 'nullable|numeric|min:0|max:999.99',
            'commission'     => 'nullable|numeric|min:0|max:999.99',
            'deposit_amount' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
            'price_per'      => 'nullable|numeric|min:0',
            'requirements'   => 'required|string', // Cancellation / Rescheduling Policy — required per spec
        ];
    }

    /**
     * Guard against invalid combinations, e.g. Doctors/Clinics booking_type
     * with a category whose parent booking_type is Automotive.
     */
    private function categoryMatchesTypeRule(Request $request): array
    {
        return [
            'category_id' => [
                'bail',
                function ($attr, $value, $fail) use ($request) {
                    $category = BookingCategory::find($value);
                    if ($category && $category->effective_booking_type !== $request->booking_type) {
                        $fail(trans('admin/main.category_does_not_match_booking_type'));
                    }
                    if ($category && empty($category->template_key) && $category->parent_id) {
                        $fail(trans('admin/main.category_missing_template'));
                    }
                },
            ],
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

        if ($request->filled('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        if ($request->filled('sub_type')) {
            $query->where('sub_type', $request->sub_type);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
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
                             ->whereIn('booking_orders.status', ['confirmed', 'completed']);
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

    private function getTopPageStats($query): array
    {
        $totalBookings = deepClone($query)->count();
        $bookingIds    = deepClone($query)->pluck('bookings.id');

        $totalBookingSales = 0;
        $totalCustomers    = 0;

        if ($bookingIds->count()) {
            $totalBookingSales = BookingOrder::whereIn('booking_id', $bookingIds)
                ->whereIn('status', ['confirmed', 'completed'])
                ->count();

            $totalCustomers = BookingOrder::whereIn('booking_id', $bookingIds)
                ->whereIn('status', ['confirmed', 'completed'])
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