<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAsset;
use App\Models\BookingBundle;
use App\Models\BookingCalendarIntegration;
use App\Models\BookingCategory;
use App\Models\BookingCoupon;
use App\Models\BookingDiscount;
use App\Models\BookingFeatured;
use App\Models\BookingFilter;
use App\Models\BookingReport;
use App\Models\BookingRule;
use App\Models\BookingWaitlist;
use Illuminate\Http\Request;

class BookingModuleCrudController extends Controller
{
    private array $resources = [
        'filters' => [
            'model' => BookingFilter::class,
            'permission' => 'admin_booking_filters',
            'title' => 'Booking Filters',
            'columns' => ['id', 'category_id', 'title', 'type', 'status', 'order'],
            'fields' => ['category_id', 'title', 'type', 'options', 'is_required', 'status'],
            'json' => ['options'],
            'booleans' => ['is_required', 'status'],
            'auto_order' => true,
            'validation' => [
                'category_id' => 'required|exists:booking_categories,id',
                'title' => 'required|string|max:255',
                'type' => 'required|string|in:checkbox,radio,select,text,number,date',
                'is_required' => 'nullable|boolean',
                'status' => 'nullable|boolean',
            ],
            'help' => 'Filters are used on the booking list/search page. Select a real category first, then define the filter title and input type.',
        ],
        'rules' => [
            'model' => BookingRule::class,
            'permission' => 'admin_booking_rules',
            'title' => 'Booking Rules',
            'columns' => ['id', 'booking_id', 'rule_type', 'starts_at', 'ends_at', 'status'],
            'fields' => ['booking_id', 'rule_type', 'conditions', 'actions', 'starts_at', 'ends_at', 'status'],
            'json' => ['conditions', 'actions'],
            'booleans' => ['status'],
            'validation' => [
                'booking_id' => 'required|exists:bookings,id',
                'rule_type' => 'required|string|max:255',
                'conditions' => 'nullable|json',
                'actions' => 'nullable|json',
                'starts_at' => 'nullable|date',
                'ends_at' => 'nullable|date|after_or_equal:starts_at',
                'status' => 'nullable|boolean',
            ],
            'help' => 'Rules control booking behavior for a selected booking, for example date limits, approval requirements, blackout periods, or custom pricing actions stored as JSON.',
        ],
        'discounts' => [
            'model' => BookingDiscount::class,
            'permission' => 'admin_booking_discounts',
            'title' => 'Booking Discounts',
            'columns' => ['id', 'booking_id', 'bundle_id', 'title', 'discount_type', 'amount', 'status'],
            'fields' => ['booking_id', 'bundle_id', 'title', 'discount_type', 'amount', 'starts_at', 'expires_at', 'usage_limit', 'status', 'meta'],
            'json' => ['meta'],
            'booleans' => ['status'],
            'validation' => [
                'booking_id' => 'nullable|exists:bookings,id|required_without:bundle_id',
                'bundle_id' => 'nullable|exists:booking_bundles,id|required_without:booking_id',
                'title' => 'required|string|max:255',
                'discount_type' => 'required|string|in:percent,fixed',
                'amount' => 'required|numeric|min:0',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:starts_at',
                'usage_limit' => 'nullable|integer|min:1',
                'status' => 'nullable|boolean',
                'meta' => 'nullable|json',
            ],
            'help' => 'Discounts apply to either a booking or a booking bundle. Select at least one target from the dropdowns.',
        ],
        'coupons' => [
            'model' => BookingCoupon::class,
            'permission' => 'admin_booking_coupons',
            'title' => 'Booking Coupons',
            'columns' => ['id', 'code', 'booking_id', 'bundle_id', 'discount_type', 'amount', 'status'],
            'fields' => ['code', 'title', 'booking_id', 'bundle_id', 'discount_type', 'amount', 'minimum_order_amount', 'usage_limit', 'starts_at', 'expires_at', 'status', 'meta'],
            'json' => ['meta'],
            'booleans' => ['status'],
            'validation' => [
                'code' => 'required|string|max:255',
                'title' => 'nullable|string|max:255',
                'booking_id' => 'nullable|exists:bookings,id',
                'bundle_id' => 'nullable|exists:booking_bundles,id',
                'discount_type' => 'required|string|in:percent,fixed',
                'amount' => 'required|numeric|min:0',
                'minimum_order_amount' => 'nullable|numeric|min:0',
                'usage_limit' => 'nullable|integer|min:1',
                'starts_at' => 'nullable|date',
                'expires_at' => 'nullable|date|after_or_equal:starts_at',
                'status' => 'nullable|boolean',
                'meta' => 'nullable|json',
            ],
        ],
        'assets' => [
            'model' => BookingAsset::class,
            'permission' => 'admin_booking_assets',
            'title' => 'Booking Assets',
            'columns' => ['id', 'booking_id', 'type', 'path', 'title', 'status', 'order'],
            'fields' => ['booking_id', 'type', 'path', 'title', 'alt', 'order', 'status', 'meta'],
            'json' => ['meta'],
            'booleans' => ['status'],
        ],
        'reports' => [
            'model' => BookingReport::class,
            'permission' => 'admin_booking_reports',
            'title' => 'Booking Reports',
            'columns' => ['id', 'booking_id', 'order_id', 'user_id', 'reason', 'status', 'reviewed_at'],
            'fields' => ['booking_id', 'order_id', 'user_id', 'reason', 'message', 'status', 'reviewed_by', 'reviewed_at', 'meta'],
            'json' => ['meta'],
        ],
        'featured' => [
            'model' => BookingFeatured::class,
            'permission' => 'admin_booking_featured',
            'title' => 'Featured Bookings/Categories',
            'columns' => ['id', 'booking_id', 'category_id', 'placement', 'starts_at', 'expires_at', 'status', 'order'],
            'fields' => ['booking_id', 'category_id', 'placement', 'starts_at', 'expires_at', 'order', 'status'],
            'booleans' => ['status'],
        ],
        'waitlists' => [
            'model' => BookingWaitlist::class,
            'permission' => 'admin_booking_waitlists',
            'title' => 'Booking Waitlists',
            'columns' => ['id', 'booking_id', 'resource_id', 'user_id', 'email', 'booking_date', 'start_time', 'status'],
            'fields' => ['booking_id', 'resource_id', 'user_id', 'name', 'email', 'phone', 'booking_date', 'start_time', 'end_time', 'persons', 'status', 'meta'],
            'json' => ['meta'],
        ],
        'calendar-integrations' => [
            'model' => BookingCalendarIntegration::class,
            'permission' => 'admin_booking_calendar_integrations',
            'title' => 'Booking Calendar Integrations',
            'columns' => ['id', 'user_id', 'booking_id', 'provider', 'external_calendar_id', 'last_synced_at', 'status'],
            'fields' => ['user_id', 'booking_id', 'provider', 'external_calendar_id', 'token_expires_at', 'last_synced_at', 'status', 'settings'],
            'json' => ['settings'],
            'booleans' => ['status'],
        ],
    ];

    public function index(string $resource)
    {
        $config = $this->config($resource);
        $this->authorize($config['permission']);

        return view('admin.booking.module_crud', [
            'pageTitle' => $config['title'],
            'resource' => $resource,
            'config' => $config,
            'items' => $config['model']::query()->latest('id')->paginate(20),
            'selectOptions' => $this->selectOptions(),
        ]);
    }

    public function store(Request $request, string $resource)
    {
        $config = $this->config($resource);
        $this->authorize($config['permission'] . '_create');

        $data = $this->payload($request, $config);

        if (!empty($config['auto_order'])) {
            $maxOrder = $config['model']::max('order') ?? 0;
            $data['order'] = $maxOrder + 1;
        }

        $config['model']::create($data);

        return back()->with('success', $config['title'] . ' created successfully.');
    }

    public function edit(string $resource, int $id)
    {
        $config = $this->config($resource);
        $this->authorize($config['permission'] . '_edit');

        return view('admin.booking.module_crud', [
            'pageTitle' => $config['title'],
            'resource' => $resource,
            'config' => $config,
            'items' => $config['model']::query()->latest('id')->paginate(20),
            'editItem' => $config['model']::findOrFail($id),
            'selectOptions' => $this->selectOptions(),
        ]);
    }

    public function update(Request $request, string $resource, int $id)
    {
        $config = $this->config($resource);
        $this->authorize($config['permission'] . '_edit');

        $config['model']::findOrFail($id)->update($this->payload($request, $config));

        return redirect(getAdminPanelUrl("/booking/modules/{$resource}"))
            ->with('success', $config['title'] . ' updated successfully.');
    }

    public function delete(string $resource, int $id)
    {
        $config = $this->config($resource);
        $this->authorize($config['permission'] . '_delete');

        $config['model']::findOrFail($id)->delete();

        return back()->with('success', $config['title'] . ' deleted successfully.');
    }

    private function config(string $resource): array
    {
        abort_unless(isset($this->resources[$resource]), 404);

        return $this->resources[$resource];
    }

    private function payload(Request $request, array $config): array
    {
        // Validation
        if (!empty($config['validation'])) {

            $rules = $config['validation'];

            // Dynamic JSON fields ko raw json validate mat karo
            unset(
                $rules['options'],
                $rules['conditions'],
                $rules['actions'],
                $rules['meta']
            );

            $request->validate($rules);
        }

        $data = $request->only($config['fields']);

        // Boolean fields
        foreach ($config['booleans'] ?? [] as $field) {

            $data[$field] = $request->boolean($field);
        }

        /*
        |--------------------------------------------------------------------------
        | Dynamic JSON Fields
        |--------------------------------------------------------------------------
        */

        foreach (['options', 'conditions', 'actions', 'meta'] as $jsonField) {

            if (!in_array($jsonField, $config['fields'] ?? [])) {
                continue;
            }

            // options ka naming alag hai
            $keyInput = $jsonField === 'options'
                ? 'option_keys'
                : $jsonField . '_keys';

            $valueInput = $jsonField === 'options'
                ? 'option_values'
                : $jsonField . '_values';

            $keys = $request->input($keyInput, []);
            $values = $request->input($valueInput, []);

            $built = [];

            foreach ($keys as $i => $key) {

                $key = trim((string) $key);

                if ($key === '') {
                    continue;
                }

                $val = $values[$i] ?? null;

                // Numeric string => number
                if (is_numeric($val)) {
                    $val = $val + 0;
                }

                // true/false string => boolean
                if ($val === 'true') {
                    $val = true;
                }

                if ($val === 'false') {
                    $val = false;
                }

                $built[$key] = $val;
            }

            $data[$jsonField] = !empty($built)
                ? $built
                : null;
        }

        /*
        |--------------------------------------------------------------------------
        | Other JSON Fields
        |--------------------------------------------------------------------------
        */

        foreach ($config['json'] ?? [] as $field) {

            if (in_array($field, ['options', 'conditions', 'actions', 'meta'])) {
                continue;
            }

            if (array_key_exists($field, $data) && is_string($data[$field])) {

                $decoded = json_decode($data[$field], true);

                $data[$field] = json_last_error() === JSON_ERROR_NONE
                    ? $decoded
                    : null;
            }
        }

        return array_filter($data, fn($value) => $value !== '');
    }

    private function selectOptions(): array
    {
        return [
            'category_id' => BookingCategory::query()
                ->orderBy('order')
                ->orderBy('title')
                ->get(['id', 'title'])
                ->map(fn($c) => ['id' => $c->id, 'title' => "#{$c->id} - {$c->title}"]),
            'booking_id' => Booking::query()
                ->orderByDesc('id')
                ->get(['id', 'title'])
                ->map(fn($b) => ['id' => $b->id, 'title' => "#{$b->id} - {$b->title}"]),
            'bundle_id' => BookingBundle::query()
                ->orderByDesc('id')
                ->get(['id', 'title'])
                ->map(fn($b) => ['id' => $b->id, 'title' => "#{$b->id} - {$b->title}"]),
            'type' => collect([
                ['id' => 'checkbox', 'title' => 'Checkbox'],
                ['id' => 'radio', 'title' => 'Radio'],
                ['id' => 'select', 'title' => 'Dropdown'],
                ['id' => 'text', 'title' => 'Text'],
                ['id' => 'number', 'title' => 'Number'],
                ['id' => 'date', 'title' => 'Date'],
            ]),
            'rule_type' => collect([
                ['id' => 'availability', 'title' => 'Availability'],
                ['id' => 'pricing', 'title' => 'Pricing'],
                ['id' => 'approval', 'title' => 'Approval'],
                ['id' => 'blackout', 'title' => 'Blackout'],
                ['id' => 'capacity', 'title' => 'Capacity'],
                ['id' => 'custom', 'title' => 'Custom'],
            ]),
            'discount_type' => collect([
                ['id' => 'percent', 'title' => 'Percent'],
                ['id' => 'fixed', 'title' => 'Fixed Amount'],
            ]),
        ];
    }
}