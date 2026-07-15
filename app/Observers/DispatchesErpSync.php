<?php

namespace App\Observers;

use App\Jobs\PushEntityToErpJob;
use App\Models\VendorAbility;

/**
 * Common logic jo har observer use karta hai: vendor ki saari enabled +
 * "export" type vendor_abilities dhoondo aur har ek ke liye alag job dispatch
 * karo (ek vendor ke paas ek se zyada ERP ability enabled ho sakti hai —
 * e.g. Flow 1 aur Flow 3 dono).
 */
trait DispatchesErpSync
{
    protected function dispatchErpPush(int $vendorId, string $entity, int $localId, array $localData): void
    {
        $vendorAbilities = VendorAbility::where('vendor_id', $vendorId)
            ->where('enabled', true)
            ->whereHas('ability', function ($q) {
                $q->where('is_active', true)->where('type', 'export');
            })
            ->get();

        foreach ($vendorAbilities as $vendorAbility) {
            PushEntityToErpJob::dispatch($vendorAbility->id, $entity, $localId, $localData);
        }
    }
}
