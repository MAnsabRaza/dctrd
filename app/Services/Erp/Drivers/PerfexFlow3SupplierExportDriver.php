<?php

namespace App\Services\Erp\Drivers;

/**
 * Flow 3: Rocket LMS -> ERP -> External Marketplace
 * Customer (jo khud ek business hai) apna data doosre marketplace ko as a
 * supplier bhejta hai. Alag endpoint (supplier-feed) aur alag API key
 * (config_json['api_key'] per-vendor already isolate karta hai).
 */
class PerfexFlow3SupplierExportDriver extends AbstractPerfexDriver
{
    protected function entityPathMap(): array
    {
        // Requirement doc: Flow 3 sab entities ek hi 'supplier-feed' endpoint
        // se guzarti hain (external marketplace ka single ingestion point).
        return [
            'customer' => 'supplier-feed',
            'product'  => 'supplier-feed',
            'order'    => 'supplier-feed',
            'booking'  => 'supplier-feed',
            'payment'  => 'supplier-feed',
        ];
    }

    public function pushEntity(\App\Models\VendorAbility $vendorAbility, string $entity, array $payload): array
    {
        // Marketplace ko entity type pata hona chahiye kyunke endpoint shared hai
        $payload = array_merge($payload, ['entity_type' => $entity]);

        return parent::pushEntity($vendorAbility, $entity, $payload);
    }
}
