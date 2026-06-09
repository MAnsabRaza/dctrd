<?php

namespace App\Services;

use App\Models\CheckoutModule;
use App\Models\EntityCheckoutModule;
use App\Models\OrderMeta;
use App\Models\OrgCheckoutModule;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutModuleService
{
    /**
     * METHOD 1: getModulesForEntity()
     *
     * Kisi bhi product/course/booking ke liye enabled modules laata hai.
     * Priority: Entity Override > Org Setting > Default (disabled)
     */
    public function getModulesForEntity(
        string $entityType,
        int $entityId,
        int $orgId
    ): Collection {
        // Step 1: Saare active modules laao, order_index se sort karo
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
        $enabledModules = $allModules->filter(function ($module) use ($orgEnabledIds, $entityOverrides) {

            // Entity level override hai toh woh priority pe hai
            if ($entityOverrides->has($module->id)) {
                return $entityOverrides[$module->id]->enabled;
            }

            // Org level setting check karo
            if ($orgEnabledIds->has($module->id)) {
                return $orgEnabledIds[$module->id];
            }

            // Default: disabled
            return false;
        });

        // Step 5: Entity config_override merge karo agar hai
        $enabledModules = $enabledModules->map(function ($module) use ($entityOverrides) {
            if ($entityOverrides->has($module->id)) {
                $override = $entityOverrides[$module->id];
                if (!empty($override->config_override)) {
                    // Original config ke upar override merge karo
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

                // Har din ka hisaab — check_in se check_out tak
                case 'per_day':
                    if (!empty($data['check_in']) && !empty($data['check_out'])) {
                        $checkIn  = \Carbon\Carbon::parse($data['check_in']);
                        $checkOut = \Carbon\Carbon::parse($data['check_out']);
                        $days     = max(1, $checkIn->diffInDays($checkOut));
                        $basePrice = $priceRule['amount'] ?? 0;
                        $extraTotal += $days * $basePrice;
                    }
                    break;

                // Har ghante ka hisaab
                case 'per_hour':
                    $hours     = isset($data['hours_count']) ? (int) $data['hours_count'] : 1;
                    $basePrice = $priceRule['amount'] ?? 0;
                    $extraTotal += $hours * $basePrice;
                    break;

                // Har adult ka alag price
                case 'per_person':
                    $adults    = isset($data['adults']) ? (int) $data['adults'] : 0;
                    $amount    = $priceRule['amount'] ?? 0;
                    $extraTotal += $adults * $amount;
                    break;

                // Extra services — selected options ki prices jodo
                case 'additive':
                    $config  = $module->config ?? [];
                    $options = $config['options'] ?? [];
                    $selected = is_array($data) ? $data : [];

                    foreach ($options as $index => $option) {
                        if (in_array((string) $index, $selected) || in_array($option['label'], $selected)) {
                            $extraTotal += (float) ($option['price'] ?? 0);
                        }
                    }
                    break;

                // Koi extra price nahi (info checkbox, textarea etc.)
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
     * Agar order pehle se exist kare toh audit trail bhi likhta hai.
     */
    public function saveOrderMeta(int $orderId, array $moduleData): void
    {
        foreach ($moduleData as $moduleName => $value) {
            $newValue = json_encode($value);

            // Pehle se koi record hai? Audit ke liye old value chahiye
            $existing = OrderMeta::where('order_id', $orderId)
                ->where('key', $moduleName)
                ->first();

            // Save/Update order_meta
            OrderMeta::updateOrCreate(
                ['order_id' => $orderId, 'key' => $moduleName],
                ['value'    => $newValue]
            );

            // Agar pehle se record tha toh audit trail likho
            if ($existing && $existing->value !== $newValue) {
                DB::table('checkout_module_audit')->insert([
                    'order_id'    => $orderId,
                    'module_name' => $moduleName,
                    'old_value'   => $existing->value,
                    'new_value'   => $newValue,
                    'changed_by'  => Auth::id(),
                    'reason'      => trans('checkout.audit_order_update'),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        }
    }

    // =========================================================

    /**
     * METHOD 4: getOrderMeta()
     *
     * Order ka saved module data wapas laata hai.
     * Returns: ['days' => [...], 'hours' => '...', ...]
     */
    public function getOrderMeta(int $orderId): array
    {
        $metas = OrderMeta::where('order_id', $orderId)->get();

        $result = [];
        foreach ($metas as $meta) {
            $decoded = json_decode($meta->value, true);
            // Agar valid JSON nahi toh original string rakhte hain
            $result[$meta->key] = ($decoded !== null) ? $decoded : $meta->value;
        }

        return $result;
    }

    // =========================================================

    /**
     * METHOD 5: validateModuleData()
     *
     * Checkout submit hone se pehle user ka data validate karta hai.
     * Returns: ['valid' => true/false, 'errors' => ['field' => 'message']]
     */
    public function validateModuleData(
        Collection $modules,
        array $submittedData
    ): array {
        $errors = [];

        foreach ($modules as $module) {
            $name  = $module->name;
            $data  = $submittedData[$name] ?? null;
            $config = $module->config ?? [];

            // Required module hai aur data khali hai
            if ($module->is_required && empty($data)) {
                $errors[$name] = trans('checkout.validation.required', [
                    'field' => $module->translated_label
                ]);
                continue;
            }

            // Data diya hi nahi — optional hai toh skip
            if (empty($data)) {
                continue;
            }

            // Input type ke hisaab se validate karo
            switch ($module->input_type) {

                case 'date_range':
                    if (empty($data['check_in'])) {
                        $errors[$name . '.check_in'] = trans('checkout.validation.check_in_required');
                    } elseif (strtotime($data['check_in']) < strtotime('today')) {
                        $errors[$name . '.check_in'] = trans('checkout.validation.check_in_past');
                    }

                    if (empty($data['check_out'])) {
                        $errors[$name . '.check_out'] = trans('checkout.validation.check_out_required');
                    } elseif (
                        !empty($data['check_in']) &&
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
                                    'field' => $field,
                                    'min'   => $limits['min']
                                ]);
                            }
                            if ($val > $limits['max']) {
                                $errors[$name . '.' . $field] = trans('checkout.validation.max_value', [
                                    'field' => $field,
                                    'max'   => $limits['max']
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

                case 'info_checkbox':
                    // Cancellation policy — agree karna zaroori hai agar required hai
                    if ($module->is_required && empty($data)) {
                        $errors[$name] = trans('checkout.validation.must_agree_policy');
                    }
                    break;

                case 'textarea':
                    $maxLength = $config['max_length'] ?? 500;
                    if (mb_strlen($data) > $maxLength) {
                        $errors[$name] = trans('checkout.validation.max_length', [
                            'max' => $maxLength
                        ]);
                    }
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
     */
    public function getOrgModuleSettings(int $orgId): array
    {
        // Saare active modules
        $allModules = CheckoutModule::where('is_active', true)
            ->orderBy('order_index')
            ->get();

        // Is org ke enabled modules
        $orgSettings = OrgCheckoutModule::where('org_id', $orgId)
            ->pluck('enabled', 'module_id'); // [module_id => true/false]

        // Merge karke return karo
        return $allModules->map(function ($module) use ($orgSettings) {
            return [
                'id'               => $module->id,
                'name'             => $module->name,
                'label'            => $module->translated_label,
                'input_type'       => $module->input_type,
                'order_index'      => $module->order_index,
                'is_required'      => $module->is_required,
                'enabled'          => (bool) ($orgSettings[$module->id] ?? false),
            ];
        })->toArray();
    }

    // =========================================================

    /**
     * METHOD 7: saveOrgModuleSettings()
     *
     * Org/Instructor ki module preferences save karta hai.
     * $moduleSettings = ['days' => true, 'hours' => false, ...]
     */
    public function saveOrgModuleSettings(int $orgId, array $moduleSettings): void
    {
        foreach ($moduleSettings as $moduleName => $isEnabled) {

            // Module ka ID dhundo name se
            $module = CheckoutModule::where('name', $moduleName)->first();

            if (!$module) {
                continue; // Unknown module — skip karo
            }

            OrgCheckoutModule::updateOrCreate(
                [
                    'org_id'    => $orgId,
                    'module_id' => $module->id,
                ],
                [
                    'enabled' => (bool) $isEnabled,
                ]
            );
        }
    }
}