<?php

namespace App\Services\Erp;

use App\Models\AbilityFieldMappings;
use App\Models\VendorAbility;
use Illuminate\Support\Arr;

/**
 * ability_field_mappings table se per-vendor field mapping padhta hai aur
 * local <-> remote payload build karta hai. Agar vendor ne koi custom mapping
 * define nahi ki, to default mapping (core fields) fallback ke taur par use
 * hoti hai — is se admin ko har vendor ke liye mapping row banana zaroori nahi.
 */
class ErpFieldMapper
{
    /**
     * Default core mappings — jab tak vendor apni ability_field_mappings row
     * se override na kare. Requirement doc: Customer->Client, Product->Item,
     * Order->Invoice, Booking->Appointment, Payment->Payment.
     */
    protected const DEFAULT_MAPPINGS = [
        'customer' => [
            'id' => 'external_id', 'full_name' => 'name', 'email' => 'email', 'phone' => 'phone',
        ],
        'product' => [
            'id' => 'external_id', 'title' => 'name', 'price' => 'price', 'sku' => 'sku', 'description' => 'description',
        ],
        'order' => [
            'id' => 'external_id', 'total' => 'total', 'status' => 'status', 'customer_id' => 'client_id',
        ],
        'booking' => [
            'id' => 'external_id', 'start_at' => 'start_time', 'end_at' => 'end_time', 'customer_id' => 'client_id',
        ],
        'payment' => [
            'id' => 'external_id', 'amount' => 'amount', 'method' => 'method', 'order_id' => 'invoice_id',
        ],
    ];

    /**
     * Local model array -> remote payload (export direction).
     */
    public function mapForExport(VendorAbility $vendorAbility, string $entity, array $localData): array
    {
        $mapping = $this->resolveMapping($vendorAbility, $entity, 'export');

        $payload = [];
        foreach ($mapping as $localField => $remoteField) {
            if (Arr::has($localData, $localField)) {
                $payload[$remoteField] = Arr::get($localData, $localField);
            }
        }

        return $payload;
    }

    /**
     * Remote record -> local attribute array (import direction, dropshipping/pull).
     */
    public function mapForImport(VendorAbility $vendorAbility, string $entity, array $remoteData): array
    {
        $mapping = $this->resolveMapping($vendorAbility, $entity, 'import');

        // import direction ke liye mapping ulta chalta hai: local_field <= remote_field
        $local = [];
        foreach ($mapping as $localField => $remoteField) {
            if (Arr::has($remoteData, $remoteField)) {
                $local[$localField] = Arr::get($remoteData, $remoteField);
            }
        }

        return $local;
    }

    /**
     * @return array<string,string> local_field => remote_field
     */
    protected function resolveMapping(VendorAbility $vendorAbility, string $entity, string $direction): array
    {
        $custom = AbilityFieldMappings::where('vendor_ability_id', $vendorAbility->id)
            ->where('entity', $entity)
            ->whereIn('direction', [$direction, 'both'])
            ->get();

        if ($custom->isNotEmpty()) {
            return $custom->pluck('remote_field', 'local_field')->toArray();
        }

        return self::DEFAULT_MAPPINGS[$entity] ?? [];
    }
}
