<?php

namespace App\Services;

use App\Models\Ability;
use App\Models\VendorAbility;
use App\Models\AbilitySyncLog;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AbilityService
{
    /**
     * Admin side: naya ability template banana
     */
    public function createAbility(array $data): Ability
    {
        return Ability::create($data);
    }

    /**
     * schema_json['fields'] se clean array — panel view isi ko loop karti hai
     */
    public function renderFieldSchema(Ability $ability): array
    {
        return $ability->getConfigFields();
    }

    /**
     * Vendor ko panel mein jo abilities dikhni chahiye (sirf active), har ek
     * ke sath uski vendor_ability row (agar exist karti hai) attach ki hui.
     */
    public function getAvailableAbilitiesForVendor(int $vendorId)
    {
        $vendorAbilities = VendorAbility::where('vendor_id', $vendorId)
            ->get()
            ->keyBy('ability_id');

        return Ability::where('is_active', true)
            ->orderBy('name')
            ->get()
            ->map(function (Ability $ability) use ($vendorAbilities) {
                $vendorAbility = $vendorAbilities->get($ability->id);

                return [
                    'ability'       => $ability,
                    'fields'        => $this->renderFieldSchema($ability),
                    'vendorAbility' => $vendorAbility,
                    'enabled'       => (bool) ($vendorAbility->enabled ?? false),
                    'config'        => $vendorAbility->config_json ?? [],   // getConfigJsonAttribute auto-decrypt karega
                    'sync_status'   => $vendorAbility->sync_status ?? 'idle',
                ];
            });
    }

    /**
     * field_schema ke "required" fields ko enforce karna (Acceptance Criteria #4)
     */
    public function validateConfig(Ability $ability, array $configValues): array
    {
        $rules = [];

        foreach ($ability->getConfigFields() as $field) {
            $rule = [!empty($field['required']) ? 'required' : 'nullable'];
            $rule[] = $field['type'] === 'boolean' ? 'boolean' : 'string';
            $rules[$field['key']] = implode('|', $rule);
        }

        $validator = Validator::make($configValues, $rules);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    /**
     * Vendor apni ability enable kare (ya config update kare)
     */
    public function enableForVendor(Ability $ability, int $vendorId, array $configValues): VendorAbility
    {
        $validated = $this->validateConfig($ability, $configValues);

        $vendorAbility = VendorAbility::updateOrCreate(
            ['ability_id' => $ability->id, 'vendor_id' => $vendorId],
            [
                'config_json' => $validated, // model accessor/mutator sensitive fields encrypt karega
                'enabled'     => true,
                'sync_status' => 'idle',
            ]
        );

        $this->log($vendorAbility, 'enable', 'success');

        return $vendorAbility;
    }

    /**
     * Vendor apni ability disable kare
     */
    public function disableForVendor(Ability $ability, int $vendorId): ?VendorAbility
    {
        $vendorAbility = VendorAbility::where('ability_id', $ability->id)
            ->where('vendor_id', $vendorId)
            ->first();

        if (!$vendorAbility) {
            return null;
        }

        $vendorAbility->update(['enabled' => false]);

        $this->log($vendorAbility, 'disable', 'success');

        return $vendorAbility;
    }

    /**
     * ability_sync_logs mein record — Acceptance Criteria #6
     * NOTE: model ka column naam "entity" hai, isliye action name usi mein daal rahe hain.
     */
    public function log(VendorAbility $vendorAbility, string $action, string $status, ?array $responsePayload = null, ?string $errorMessage = null): AbilitySyncLog
    {
        return AbilitySyncLog::create([
            'vendor_ability_id' => $vendorAbility->id,
            'entity'            => $action,
            'status'            => $status,
            'response_payload'  => $responsePayload,
            'error_message'     => $errorMessage,
        ]);
    }
}