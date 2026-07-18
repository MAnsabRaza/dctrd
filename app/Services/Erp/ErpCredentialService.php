<?php

namespace App\Services\Erp;

use App\Models\ErpCredential;

class ErpCredentialService
{
    /**
     * Vendor ka credential row (import_export ya dropshipping) — agar exist nahi karti to blank instance
     */
    public function getOrNew(int $vendorId, string $type): ErpCredential
    {
        return ErpCredential::firstOrNew([
            'vendor_id' => $vendorId,
            'type'      => $type,
        ]);
    }

    /**
     * Admin ya vendor donon isi function se save karte hain.
     * $data: base_url, is_active, export_ability_enabled, import_dropshipping_enabled,
     *        rate_limit_per_minute, checklist (array of bool keyed by ErpCredential::CHECKLIST_KEYS)
     */
    public function save(int $vendorId, string $type, array $data): ErpCredential
    {
        $checklist = [];
        foreach (ErpCredential::CHECKLIST_KEYS as $key) {
            $checklist[$key] = !empty($data['checklist'][$key]);
        }

        $credential = ErpCredential::updateOrCreate(
            ['vendor_id' => $vendorId, 'type' => $type],
            [
                'base_url'                    => $data['base_url'] ?? null,
                'is_active'                   => !empty($data['is_active']),
                'export_ability_enabled'      => !empty($data['export_ability_enabled']),
                'import_dropshipping_enabled' => !empty($data['import_dropshipping_enabled']),
                'rate_limit_per_minute'       => $data['rate_limit_per_minute'] ?? 60,
                'checklist'                   => $checklist,
            ]
        );

        // Agar abhi tak API key nahi bani aur vendor subscribe kar raha hai to auto-generate
        if ($credential->is_active && empty($credential->api_key)) {
            $this->regenerateKey($credential);
        }

        return $credential->fresh();
    }

    public function regenerateKey(ErpCredential $credential): ErpCredential
    {
        $credential->update([
            'api_key'              => ErpCredential::generateApiKey(),
            'last_regenerated_at'  => now(),
        ]);

        return $credential->fresh();
    }

    public function toggleStatus(ErpCredential $credential, bool $active): ErpCredential
    {
        $credential->update(['is_active' => $active]);

        if ($active && empty($credential->api_key)) {
            $this->regenerateKey($credential);
        }

        return $credential->fresh();
    }

    /**
     * Vendor subscribe na kare (is_active=false) to bhi data silently sync hota rahe —
     * sirf ERP API access (regenerate/keys dikhana) hide hoga, sync job hamesha chalegi.
     */
    public function isErpAccessAllowed(int $vendorId, string $type = 'import_export'): bool
    {
        $credential = ErpCredential::where('vendor_id', $vendorId)->where('type', $type)->first();

        return (bool) ($credential->is_active ?? false);
    }
}
