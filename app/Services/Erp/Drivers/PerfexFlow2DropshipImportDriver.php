<?php

namespace App\Services\Erp\Drivers;

use App\Models\VendorAbility;
use App\Services\Erp\ErpApiException;

/**
 * Flow 2: Supplier/ERP Feed -> ERP -> Rocket LMS (dropshipping)
 * Doosre producers ke products/inventory ko ERP se import karke Rocket LMS
 * mein dikhana. Requirement doc ke mutabiq Flow 1 & 2 same base endpoint
 * (.../clients) + toggle 'import_dropshipping_enabled' share karte hain, is
 * liye pull se pehle vendor_abilities.config_json mein wo toggle check karte hain.
 */
class PerfexFlow2DropshipImportDriver extends AbstractPerfexDriver
{
    protected function entityPathMap(): array
    {
        return array_merge(parent::entityPathMap(), [
            'product' => 'clients', // requirement doc ke mutabiq shared endpoint
        ]);
    }

    public function pullEntities(VendorAbility $vendorAbility, string $entity, array $params = []): array
    {
        $config = $vendorAbility->config_json ?? [];

        if (empty($config['import_dropshipping_enabled'])) {
            throw new ErpApiException('Dropshipping import not enabled for this vendor ability', [
                'vendor_ability_id' => $vendorAbility->id,
            ]);
        }

        $params = array_merge($params, ['dropshipping' => 1]);

        return parent::pullEntities($vendorAbility, $entity, $params);
    }

    /**
     * Flow 2 sirf import direction hai — accidental push ko explicitly block karo.
     */
    public function pushEntity(\App\Models\VendorAbility $vendorAbility, string $entity, array $payload): array
    {
        throw new ErpApiException('PerfexFlow2DropshipImportDriver is pull-only (import direction)');
    }
}
