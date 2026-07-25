<?php

namespace App\Services;

use App\Models\CheckoutModule;
use App\Models\CheckoutModuleAudit;
use App\Models\EntityCheckoutModule;
use App\Models\OrderItemMeta;
use App\Models\OrderMeta;
use App\Models\OrgCheckoutModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class CheckoutModuleService
{
    /**
     * Kisi bhi product/course/booking ke liye enabled modules laata hai.
     * Priority: Entity Override > Org Setting > Default (disabled)
     *
     * Display ab sirf is_active + enabled ke basis par hoti hai.
     * "required" concept poori tarah remove kar diya gaya hai.
     */
    public function getModulesForEntity(
        string $entityType,
        int $entityId,
        int $orgId,
        ?string $bookingType = null
    ): Collection {
        $allModules = CheckoutModule::where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->get();

        $orgEnabledIds = OrgCheckoutModule::where('org_id', $orgId)
            ->pluck('enabled', 'module_id'); // [module_id => true/false]

        $entityOverrides = EntityCheckoutModule::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get()
            ->keyBy('module_id');

        $enabledModules = $allModules->filter(function ($module) use ($orgEnabledIds, $entityOverrides) {
            // Entity level override hai toh woh priority pe hai
            if ($entityOverrides->has($module->id)) {
                return $entityOverrides[$module->id]->enabled;
            }

            // Org level setting check karo
            if ($orgEnabledIds->has($module->id)) {
                return (bool) $orgEnabledIds[$module->id];
            }

            // Default: disabled
            return false;
        });

        $enabledModules = $enabledModules->map(function ($module) use ($entityOverrides, $bookingType) {
            if ($entityOverrides->has($module->id)) {
                $override = $entityOverrides[$module->id];
                if (!empty($override->config_override)) {
                    $module->config = array_merge(
                        $module->config ?? [],
                        $override->config_override ?? []
                    );
                }
            }

            $this->applyBookingTypeConfig($module, $bookingType);

            return $module;
        });

        return $enabledModules->values();
    }


private function applyBookingTypeConfig($module, ?string $bookingType): void
{
    if ($module->name !== 'extra_services') {
        return;
    }

    $config = $module->config ?? [];
    $optionsByBookingType = $config['options_by_booking_type'] ?? null;

    // Agar naya multi-type structure hi nahi hai (purana flat 'options'),
    // toh kuch mat chhero — backward compatible rehne do.
    if (!is_array($optionsByBookingType)) {
        return;
    }

    $key = $this->resolveBookingTypeConfigKey($bookingType, $optionsByBookingType) ?? 'default';

    $config['options'] = (isset($optionsByBookingType[$key]) && is_array($optionsByBookingType[$key]))
        ? $optionsByBookingType[$key]
        : [];

    $module->config = $config;
}

private function resolveBookingTypeConfigKey(?string $bookingType, array $optionsByBookingType): ?string
{
    if (empty($bookingType)) {
        return null;
    }

    $normalized = Str::of($bookingType)->lower()->replace(['-', ' '], '_')->toString();
    $candidates = array_unique([
        $bookingType,
        $normalized,
        str_replace('_', '-', $normalized),
        $normalized === 'beauty_spa' ? 'beauty_and_spa' : null,
        $normalized === 'beauty_and_spa' ? 'beauty_spa' : null,
    ]);

    foreach ($candidates as $candidate) {
        if (!empty($candidate) && array_key_exists($candidate, $optionsByBookingType)) {
            return $candidate;
        }
    }

    return null;
}

    private function getConfigOptions(array $config): array
    {
        return is_array($config['options'] ?? null) ? $config['options'] : [];
    }

    // =========================================================

  public function calculateExtraPrice(Collection $modules, array $submittedData): float
{
    $breakdown = $this->calculateExtraPriceBreakdown($modules, $submittedData);

    return round(array_sum(array_column($breakdown, 'amount')), 2);
}

public function calculateExtraPriceBreakdown(Collection $modules, array $submittedData): array
{
    $breakdown = [];

    foreach ($modules as $module) {
       $priceRule = $module->price_rule ?? [];
        $type      = $priceRule['type'] ?? 'none';
        $config    = $module->config ?? [];
        $type      = $priceRule['type'] ?? 'none';
        $data      = $submittedData[$module->name] ?? null;
         \Log::info('MODULE PRICE CHECK', [
        'module_name' => $module->name,
        'type' => $type,
        'data_received' => $data,
        'config' => $config,
    ]);

        if ($this->isEmptyValue($data)) {
            continue;
        }

        $amount = 0.0;
        $detail = [];

        switch ($type) {
            case 'per_day':
                if (!empty($data['check_in']) && !empty($data['check_out'])) {
                    $checkIn  = \Carbon\Carbon::parse($data['check_in']);
                    $checkOut = \Carbon\Carbon::parse($data['check_out']);
                    $days     = max(1, $checkIn->diffInDays($checkOut));
                    $perDay   = $priceRule['amount'] ?? ($config['price_per_day'] ?? 0);
                    $amount   = $days * $perDay;
                    $detail   = ['days' => $days, 'price_per_day' => $perDay];
                }
                break;

            case 'per_hour':
                $hours   = !empty($data) ? 1 : 0; // 1 slot = 1 hour (jaisa JS mein hai)
                $perHour = $priceRule['amount'] ?? ($config['price_per_hour'] ?? 0);
                $amount  = $hours * $perHour;
                $detail  = ['hours' => $hours, 'price_per_hour' => $perHour];
                break;

            case 'per_person':
                // ✅ FIX: price 'config' se aati hai (adults/children ka alag-alag rate),
                // 'price_rule.amount' se NAHI — kyunke seeder mein wo key hai hi nahi.
                $adults     = isset($data['adults']) ? (int) $data['adults'] : 0;
                $children   = isset($data['children']) ? (int) $data['children'] : 0;
                $adultPrice = (float) ($config['adults']['price'] ?? 0);
                $childPrice = (float) ($config['children']['price'] ?? 0);
                $adultTotal = $adults * $adultPrice;
                $childTotal = $children * $childPrice;
                $amount     = $adultTotal + $childTotal;
                $detail     = [
                    'adults'      => $adults,
                    'adult_price' => $adultPrice,
                    'adult_total' => round($adultTotal, 2),
                    'children'    => $children,
                    'child_price' => $childPrice,
                    'child_total' => round($childTotal, 2),
                ];
                break;

            case 'additive':
                $options  = $this->getConfigOptions($config);
                $selected = is_array($data) ? $data : [];
                $selectedItems = [];

                foreach ($options as $option) {
                    if (in_array((string) $option['label'], $selected, true)) {
                        $optPrice = (float) ($option['price'] ?? 0);
                        $amount  += $optPrice;
                        $selectedItems[] = ['label' => $option['label'], 'price' => $optPrice];
                    }
                }
                $detail = ['selected' => $selectedItems];
                break;

            case 'none':
            default:
                break;
        }

        if ($amount > 0 || !empty($detail)) {
            $breakdown[$module->name] = array_merge([
                'type'   => $type,
                'amount' => round($amount, 2),
            ], $detail);
        }
    }

    return $breakdown;
}

    // =========================================================

    public function saveOrderMeta(int $orderId, array $moduleData): void
    {
        foreach ($moduleData as $moduleName => $value) {
            if (is_array($value)) {
                $value = $this->filterEmptyValues($value);
            }

            if ($this->isEmptyValue($value)) {
                continue;
            }

            $newValue = json_encode($value);

            $existing = OrderMeta::where('order_id', $orderId)
                ->where('key', $moduleName)
                ->first();
            $oldValue = $existing ? $existing->getRawOriginal('value') : null;

            OrderMeta::updateOrCreate(
                ['order_id' => $orderId, 'key' => $moduleName],
                ['value'    => $value]
            );

            if ($existing && $oldValue !== $newValue) {
                $changedBy = Auth::id();

                if (empty($changedBy)) {
                    continue;
                }

                CheckoutModuleAudit::create([
                    'order_id'    => $orderId,
                    'module_name' => $moduleName,
                    'old_value'   => $oldValue,
                    'new_value'   => $newValue,
                    'changed_by'  => $changedBy,
                    'reason'      => trans('checkout.audit_order_update'),
                ]);
            }
        }
    }

    public function saveOrderItemMeta(int $orderItemId, array $moduleData): void
    {
        foreach ($moduleData as $moduleName => $value) {
            if (is_array($value)) {
                $value = $this->filterEmptyValues($value);
            }

            if ($this->isEmptyValue($value)) {
                continue;
            }

            OrderItemMeta::updateOrCreate(
                ['order_item_id' => $orderItemId, 'key' => $moduleName],
                ['value' => $value]
            );
        }
    }

    // =========================================================

    public function getOrderMeta(int $orderId): array
    {
        $metas = OrderMeta::where('order_id', $orderId)->get();

        $result = [];
        foreach ($metas as $meta) {
            if (is_array($meta->value)) {
                $result[$meta->key] = $meta->value;
                continue;
            }

            $decoded = json_decode($meta->value, true);
            $result[$meta->key] = ($decoded !== null) ? $decoded : $meta->value;
        }

        return $result;
    }

    // =========================================================

    /**
     * Checkout submit hone se pehle user ka data validate karta hai.
     * "required" flag hata diya gaya hai - ab sirf format/logic validation
     * hoti hai (e.g. check_out > check_in, max_length, valid option),
     * koi field mandatory nahi hai module-level flag se.
     */
   public function validateModuleData(
    Collection $modules,
    array $submittedData
): array {
    $errors = [];

    foreach ($modules as $module) {
        $name   = $module->name;
        $data   = $submittedData[$name] ?? null;
        $config = $module->config ?? [];

        // ✅ Cancellation Policy — hamesha mandatory hai jab module active ho.
        // Customer ko checkbox check karna hi hoga, warna checkout nahi hoga.
        if ($name === 'cancellation_policy') {
            if ($this->isEmptyValue($data)) {
                $errors[$name] ="Cancellation Policy is required."
                    ?: 'Please agree to the cancellation policy to continue.';
            }
            continue;
        }

        // Baaki tamam modules (including extra_services) optional hain —
        // agar data hi nahi bheja gaya, to validate karne ki zaroorat nahi.
        if ($this->isEmptyValue($data)) {
            continue;
        }

        switch ($module->input_type) {

            case 'date_range':
                if (!empty($data['check_in']) && strtotime($data['check_in']) < strtotime('today')) {
                    $errors[$name . '.check_in'] = "Check-in date cannot be in the past.";
                }

                if (
                    !empty($data['check_in']) &&
                    !empty($data['check_out']) &&
                    strtotime($data['check_out']) <= strtotime($data['check_in'])
                ) {
                    $errors[$name . '.check_out'] = "Check-out date must be after check-in date.";
                }
                break;

            case 'time_slot':
                $availableSlots = $config['slots'] ?? [];
                if (!empty($availableSlots) && !in_array($data, $availableSlots)) {
                    $errors[$name] = "Selected time slot is not available.";
                }
                break;

            case 'stepper':
                $fields = [
                    'adults'   => $config['adults']   ?? ['min' => 1, 'max' => 20],
                    'children' => $config['children'] ?? ['min' => 0, 'max' => 10],
                    'rooms'    => $config['rooms']    ?? ['min' => 1, 'max' => 10],
                ];

                foreach ($fields as $field => $limits) {
                    if (!isset($data[$field])) {
                        continue;
                    }

                    $val = (int) $data[$field];
                    if ($val < $limits['min']) {
                        $errors[$name . '.' . $field] = "Value for {$field} is below the minimum allowed.";
                    }
                    if ($val > $limits['max']) {
                        $errors[$name . '.' . $field] = "Value for {$field} is above the maximum allowed.";
                    }
                }
                break;

            case 'checkbox_list':
                // ✅ Extra Services hamesha optional hain — checkbox check na
                // karna valid hai (upar hi skip ho jata hai). Invalid/mismatched
                // label aane par bhi checkout ko block nahi karna — pricing
                // calculation (calculateExtraPriceBreakdown) waise hi sirf
                // valid config options ko match karke price count karta hai,
                // baaki khud-ba-khud ignore ho jata hai. Isliye yahan
                // koi error add nahi karna.
                break;

            case 'textarea':
                $maxLength = $config['max_length'] ?? 500;
                if (mb_strlen($data) > $maxLength) {
                    $errors[$name] = "Text exceeds the maximum allowed length of {$maxLength} characters.";
                }
                break;

            case 'info_checkbox':
                // cancellation_policy upar handle ho chuki hai (continue se
                // pehle). Agar koi aur info_checkbox module ho to filhal
                // koi extra validation nahi.
                break;
        }
    }

    return [
        'valid'  => empty($errors),
        'errors' => $errors,
    ];
}

    // =========================================================

    /**
     * Org/Instructor ke panel mein dikhane ke liye saare modules
     * aur unka enabled/disabled status laata hai (status-based only).
     */
    public function getOrgModuleSettings(int $orgId): array
    {
        $allModules = CheckoutModule::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        $orgSettings = OrgCheckoutModule::where('org_id', $orgId)
            ->pluck('enabled', 'module_id');

        return $allModules->map(function ($module) use ($orgSettings) {
            return [
                'id'          => $module->id,
                'name'        => $module->name,
                'label'       => $module->translated_label,
                'help_text'   => $module->translated_help_text,
                'input_type'  => $module->input_type,
                'order_index' => $module->order_index,
                'enabled'     => (bool) ($orgSettings[$module->id] ?? false),
            ];
        })->toArray();
    }
    /**
 * Booking/Meeting/Package se uski category slug nikalta hai
 * aur usko config keys ke saath match karne layak banata hai
 * (spaces/dashes -> underscore, lowercase).
 */
public function resolveBookingType($entity): ?string
{
    if (empty($entity)) {
        return null;
    }

    // ✅ FIX: booking_type column (beauty-spa, doctors-clinics, events, etc.)
    // ko PEHLE check karo — ye BookingTemplateConfig aur checkout_modules
    // config ke "options_by_booking_type" keys se match karta hai.
    // Pehle ye method category->slug use kar raha tha, jo kabhi bhi
    // config keys se match nahi karta tha — isi wajah se hamesha
    // "default" extras show ho rahe the, chahe booking type kuch bhi ho.
    if (!empty($entity->booking_type)) {
        return Str::of($entity->booking_type)->lower()->replace(['-', ' '], '_')->toString();
    }

    // Fallback: purana category-slug wala logic (agar booking_type khali ho)
    $category = optional($entity)->category;

    while (!empty($category)) {
        if (!empty($category->slug)) {
            return Str::of($category->slug)->lower()->replace(['-', ' '], '_')->toString();
        }
        $category = $category->parent;
    }

    return null;
}

    // =========================================================

    /**
     * Org/Instructor ki module preferences save karta hai.
     * $moduleSettings = ['days' => true, 'hours' => false, ...]
     */
    public function saveOrgModuleSettings(int $orgId, array $moduleSettings): void
    {
        foreach ($moduleSettings as $moduleName => $enabled) {

            $module = CheckoutModule::where('name', $moduleName)->first();

            if (!$module) {
                continue;
            }

            OrgCheckoutModule::updateOrCreate(
                [
                    'org_id'    => $orgId,
                    'module_id' => $module->id,
                ],
                [
                    'enabled' => (bool) $enabled,
                ]
            );
        }
    }

    private function isEmptyValue($value): bool
    {
        if (is_array($value)) {
            return empty($this->filterEmptyValues($value));
        }

        return $value === null || $value === '';
    }

    private function filterEmptyValues(array $values): array
    {
        $filtered = [];

        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $value = $this->filterEmptyValues($value);
            }

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $filtered[$key] = $value;
        }

        return $filtered;
    }
}
