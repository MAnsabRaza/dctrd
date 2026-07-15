<?php

namespace App\Console\Commands;

use App\Models\ErpIdMapping;
use App\Models\Product;
use App\Models\VendorAbility;
use App\Services\Erp\ErpApiException;
use App\Services\Erp\ErpFieldMapper;
use App\Services\Erp\ErpSyncService;
use Illuminate\Console\Command;

class SyncErpDropshipping extends Command
{
    protected $signature = 'erp:sync-dropshipping';

    protected $description = 'Flow 2: ERP se supplier products pull karo aur naye/updated Products Rocket mein create/update karo';

    public function handle(ErpSyncService $syncService, ErpFieldMapper $mapper): int
    {
        $vendorAbilities = VendorAbility::where('enabled', true)
            ->whereHas('ability', function ($q) {
                $q->where('key', 'erp-flow2-dropship-import')->where('is_active', true);
            })
            ->get();

        $this->info("Found {$vendorAbilities->count()} enabled dropshipping vendor abilities.");

        foreach ($vendorAbilities as $vendorAbility) {
            $this->syncOne($vendorAbility, $syncService, $mapper);
        }

        return self::SUCCESS;
    }

    protected function syncOne(VendorAbility $vendorAbility, ErpSyncService $syncService, ErpFieldMapper $mapper): void
    {
        try {
            $remoteProducts = $syncService->pull($vendorAbility, 'product');
        } catch (ErpApiException $e) {
            $this->warn("[vendor_ability={$vendorAbility->id}] pull failed: {$e->getMessage()}");
            return;
        }

        foreach ($remoteProducts as $remote) {
            $remoteId = (string) ($remote['id'] ?? '');

            if ($remoteId === '') {
                continue;
            }

            $hash    = md5(json_encode($remote));
            $mapping = ErpIdMapping::forRemote($vendorAbility->id, 'product', $remoteId)->first();

            // Dedup: content nahi badla to skip
            if ($mapping && $mapping->sync_hash === $hash) {
                continue;
            }

            $localAttrs = $mapper->mapForImport($vendorAbility, 'product', $remote);

            if ($mapping) {
                Product::where('id', $mapping->local_id)->update($localAttrs);
                $product = Product::find($mapping->local_id);
            } else {
                $product = Product::create(array_merge($localAttrs, ['vendor_id' => $vendorAbility->vendor_id]));
            }

            ErpIdMapping::updateOrCreate(
                ['vendor_ability_id' => $vendorAbility->id, 'entity' => 'product', 'local_id' => $product->id],
                [
                    'vendor_id'      => $vendorAbility->vendor_id,
                    'remote_id'      => $remoteId,
                    'sync_hash'      => $hash,
                    'last_synced_at' => now(),
                ]
            );
        }

        $this->info("[vendor_ability={$vendorAbility->id}] synced " . count($remoteProducts) . " product(s).");
    }
}
