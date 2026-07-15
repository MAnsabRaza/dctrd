<?php

namespace App\Jobs;

use App\Models\ErpWebhookEvent;
use App\Models\Product;
use App\Services\Erp\ErpFieldMapper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessErpWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public array $backoff = [10, 30, 60, 300, 900];

    public function __construct(protected int $webhookEventId)
    {
    }

    public function handle(ErpFieldMapper $mapper): void
    {
        $event = ErpWebhookEvent::find($this->webhookEventId);

        if (!$event || $event->processed) {
            return;
        }

        try {
            match ($event->event_type) {
                'product.updated', 'product.created' => $this->handleProductEvent($event, $mapper),
                default => Log::info('ERP webhook: unhandled event_type', ['type' => $event->event_type]),
            };

            $event->markProcessed();
        } catch (Throwable $e) {
            $event->markFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Dropshipping product update webhook — same upsert pattern as the
     * SyncErpDropshipging command uses for the polling path.
     */
    protected function handleProductEvent(ErpWebhookEvent $event, ErpFieldMapper $mapper): void
    {
        $vendorAbility = $event->vendorAbility;
        $remoteRecord  = $event->payload['data'] ?? $event->payload;

        $local = $mapper->mapForImport($vendorAbility, 'product', $remoteRecord);

        if (empty($local)) {
            return;
        }

        $mapping = \App\Models\ErpIdMapping::forRemote($vendorAbility->id, 'product', (string) ($remoteRecord['id'] ?? ''))->first();

        if ($mapping) {
            Product::where('id', $mapping->local_id)->update($local);
        } else {
            $product = Product::create(array_merge($local, ['vendor_id' => $vendorAbility->vendor_id]));

            \App\Models\ErpIdMapping::create([
                'vendor_id'         => $vendorAbility->vendor_id,
                'vendor_ability_id' => $vendorAbility->id,
                'entity'            => 'product',
                'local_id'          => $product->id,
                'remote_id'         => (string) ($remoteRecord['id'] ?? ''),
                'sync_hash'         => md5(json_encode($remoteRecord)),
                'last_synced_at'    => now(),
            ]);
        }
    }
}
