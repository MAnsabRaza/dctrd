<?php

namespace App\Services;

use App\Models\CheckoutModule;
use App\Models\CheckoutModuleAudit;
use App\Models\EntityCheckoutModule;
use App\Models\OrderMeta;
use App\Models\OrgCheckoutModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class CheckoutModuleService
{
    /**
     * METHOD 1: getModulesForEntity()
     *
     * Kisi bhi product/course/booking ke liye enabled modules laata hai.
     * Priority: Entity Override > Org Setting > Default (disabled)
     * RULE: Sirf is_active = true wale modules show honge.
     *       is_required ka koi role nahi visibility mein.
     */
    public function getModulesForEntity(
        string $entityType,
        int $entityId,
        int $orgId
    ): Collection {
        // Step 1: Saare is_active modules laao, order_index se sort karo
        $allModules = CheckoutModule::where('is_active', true)
            ->orderBy('order_index', 'asc')
            ->get();

        // Step 2: Org level pe kon se modules enabled hain
        $orgEnabledIds = OrgCheckoutModule::where('org_id', $orgId)
            ->where('enabled', true)
            ->pluck('enabled', 'module_id'); // [module_id => true/false]

        // Step 3: Entity level pe koi override hai?
        $entityOverrides = EntityCheckoutModule::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get()
            ->keyBy('module_id'); // [module_id => EntityCheckoutModule]

        // Step 4: Har module ko check karo — enabled hai ya nahi
        // is_required NAHI dekha jaata — sirf is_active (already filtered above)
        // aur org/entity level enabling
        $enabledModules = $allModules->filter(function ($module) use ($orgEnabledIds, $entityOverrides) {

            // Entity level override hai toh woh priority pe hai
            if ($entityOverrides->has($module->id)) {
                return (bool) $entityOverrides[$module->id]->enabled;
            }

            // Org level setting check karo
            if ($orgEnabledIds->has($module->id)) {
                return (bool) $orgEnabledIds[$module->id];
            }

            // Default: disabled (org ne enable nahi kiya)
            return false;
        });

        // Step 5: Entity config_override merge karo agar hai
        $enabledModules = $enabledModules->map(function ($module) use ($entityOverrides) {
            if ($entityOverrides->has($module->id)) {
                $override = $entityOverrides[$module->id];
                if (!empty($override->config_override)) {
                    $mergedConfig = array_merge(
                        $module->config ?? [],
                        $override->config_override ?? []
                    );
                    $module->config = $mergedConfig;
                }
            }
            return $module;
        });

        return $enabledModules->values();
    }

    // =========================================================

    /**
     * METHOD 2: calculateExtraPrice()
     *
     * Enabled modules aur user ke submitted data se extra price calculate karta hai.
     */
    public function calculateExtraPrice(
        Collection $modules,
        array $submittedData
    ): float {
        $extraTotal = 0.0;

        foreach ($modules as $module) {
            $priceRule = $module->price_rule ?? [];
            $type      = $priceRule['type'] ?? 'none';
            $data      = $submittedData[$module->name] ?? null;

            if (empty($data)) {
                continue;
            }

            switch ($type) {

                case 'per_day':
                    if (!empty($data['check_in']) && !empty($data['check_out'])) {
                        $checkIn   = \Carbon\Carbon::parse($data['check_in']);
                        $checkOut  = \Carbon\Carbon::parse($data['check_out']);
                        $days      = max(1, $checkIn->diffInDays($checkOut));
                        $basePrice = $priceRule['amount'] ?? 0;
                        $extraTotal += $days * $basePrice;
                    }
                    break;

                case 'per_hour':
                    $hours      = isset($data['hours_count']) ? (int) $data['hours_count'] : 1;
                    $basePrice  = $priceRule['amount'] ?? 0;
                    $extraTotal += $hours * $basePrice;
                    break;

                case 'per_person':
                    $adults     = isset($data['adults']) ? (int) $data['adults'] : 0;
                    $amount     = $priceRule['amount'] ?? 0;
                    $extraTotal += $adults * $amount;
                    break;

                case 'additive':
                    $config   = $module->config ?? [];
                    $options  = $config['options'] ?? [];
                    $selected = is_array($data) ? $data : [];

                    foreach ($options as $index => $option) {
                        if (in_array((string) $index, $selected) || in_array($option['label'], $selected)) {
                            $extraTotal += (float) ($option['price'] ?? 0);
                        }
                    }
                    break;

                case 'none':
                default:
                    break;
            }
        }

        return round($extraTotal, 2);
    }

    // =========================================================

    /**
     * METHOD 3: saveOrderMeta()
     *
     * Order place hone ke baad module data order_meta table mein save karta hai.
     */
    public function saveOrderMeta(int $orderId, array $moduleData): void
    {
        foreach ($moduleData as $moduleName => $value) {
            $newValue = json_encode($value);

            $existing = OrderMeta::where('order_id', $orderId)
                ->where('key', $moduleName)
                ->first();
            $oldValue = $existing ? $existing->getRawOriginal('value') : null;

            OrderMeta::updateOrCreate(
                ['order_id' => $orderId, 'key' => $moduleName],
                ['value'    => $newValue]
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

    // =========================================================

    /**
     * METHOD 4: getOrderMeta()
     *
     * Order ka saved module data wapas laata hai.
     */
    public function getOrderMeta(int $orderId): array
    {
        $metas = OrderMeta::where('order_id', $orderId)->get();

        $result = [];
        foreach ($metas as $meta) {
            $decoded        = json_decode($meta->value, true);
            $result[$meta->key] = ($decoded !== null) ? $decoded : $meta->value;
        }

        return $result;
    }

    // =========================================================

    /**
     * METHOD 5: validateModuleData()
     *
     * Checkout submit hone se pehle user ka data validate karta hai.
     * NOTE: is_required ki validation bhi hata di — sirf is_active se control hota hai.
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

            // Data diya hi nahi — skip karo (koi field required nahi)
            if (empty($data)) {
                continue;
            }

            // Input type ke hisaab se validate karo (format check only)
            switch ($module->input_type) {

                case 'date_range':
                    if (!empty($data['check_in']) && strtotime($data['check_in']) < strtotime('today')) {
                        $errors[$name . '.check_in'] = trans('checkout.validation.check_in_past');
                    }
                    if (
                        !empty($data['check_in']) &&
                        !empty($data['check_out']) &&
                        strtotime($data['check_out']) <= strtotime($data['check_in'])
                    ) {
                        $errors[$name . '.check_out'] = trans('checkout.validation.check_out_before_check_in');
                    }
                    break;

                case 'time_slot':
                    $availableSlots = $config['slots'] ?? [];
                    if (!empty($availableSlots) && !in_array($data, $availableSlots)) {
                        $errors[$name] = trans('checkout.validation.invalid_time_slot');
                    }
                    break;

                case 'stepper':
                    $fields = [
                        'adults'   => $config['adults']   ?? ['min' => 1, 'max' => 20],
                        'children' => $config['children'] ?? ['min' => 0, 'max' => 10],
                        'rooms'    => $config['rooms']    ?? ['min' => 1, 'max' => 10],
                    ];
                    foreach ($fields as $field => $limits) {
                        if (isset($data[$field])) {
                            $val = (int) $data[$field];
                            if ($val < $limits['min']) {
                                $errors[$name . '.' . $field] = trans('checkout.validation.min_value', [
                                    'field' => $field, 'min' => $limits['min']
                                ]);
                            }
                            if ($val > $limits['max']) {
                                $errors[$name . '.' . $field] = trans('checkout.validation.max_value', [
                                    'field' => $field, 'max' => $limits['max']
                                ]);
                            }
                        }
                    }
                    break;

                case 'checkbox_list':
                    $validLabels = collect($config['options'] ?? [])->pluck('label')->toArray();
                    $selected    = is_array($data) ? $data : [];
                    foreach ($selected as $item) {
                        if (!in_array($item, $validLabels)) {
                            $errors[$name] = trans('checkout.validation.invalid_option');
                            break;
                        }
                    }
                    break;

                case 'textarea':
                    $maxLength = $config['max_length'] ?? 500;
                    if (mb_strlen($data) > $maxLength) {
                        $errors[$name] = trans('checkout.validation.max_length', ['max' => $maxLength]);
                    }
                    break;

                // info_checkbox aur baaki — koi validation nahi
                default:
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
     * METHOD 6: getOrgModuleSettings()
     *
     * Org/Instructor ke panel mein dikhane ke liye saare modules
     * aur unka enabled/disabled status laata hai.
     * NOTE: is_required se enabled force NAHI hota — sirf is_active matter karta hai.
     */
    public function getOrgModuleSettings(int $orgId): array
    {
        // Sirf is_active = true wale modules
        $allModules = CheckoutModule::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        // Is org ke enabled modules
        $orgSettings = OrgCheckoutModule::where('org_id', $orgId)
            ->pluck('enabled', 'module_id'); // [module_id => true/false]

        return $allModules->map(function ($module) use ($orgSettings) {
            return [
                'id'          => $module->id,
                'name'        => $module->name,
                'label'       => $module->translated_label,
                'help_text'   => $module->translated_help_text,
                'input_type'  => $module->input_type,
                'order_index' => $module->order_index,
                // is_required nahi — sirf org ka enabled status
                'enabled'     => (bool) ($orgSettings[$module->id] ?? false),
            ];
        })->toArray();
    }

    // =========================================================

    /**
     * METHOD 7: saveOrgModuleSettings()
     *
     * Org/Instructor ki module preferences save karta hai.
     * NOTE: is_required force-enable NAHI karta ab.
     */
    public function saveOrgModuleSettings(int $orgId, array $moduleSettings): void
    {
        foreach ($moduleSettings as $moduleName => $isEnabled) {

            $module = CheckoutModule::where('name', $moduleName)->first();

            if (!$module) {
                continue;
            }

            // Sirf org ki choice — is_required ignore
            $enabled = (bool) $isEnabled;

            OrgCheckoutModule::updateOrCreate(
                [
                    'org_id'    => $orgId,
                    'module_id' => $module->id,
                ],
                [
                    'enabled' => $enabled,
                ]
            );
        }
    }
}