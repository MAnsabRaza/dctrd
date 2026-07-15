<?php

namespace App\Jobs;

use App\Models\AbilitySyncLog;
use App\Models\VendorAbility;
use App\Services\Erp\ErpApiException;
use App\Services\Erp\ErpSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PushEntityToErpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    /**
     * Backoff seconds between attempts: 10s, 30s, 1m, 5m, 15m — spec ke mutabiq.
     */
    public array $backoff = [10, 30, 60, 300, 900];

    public function __construct(
        protected int $vendorAbilityId,
        protected string $entity,
        protected int $localId,
        protected array $localData,
    ) {
    }

    public function handle(ErpSyncService $syncService): void
    {
        $vendorAbility = VendorAbility::find($this->vendorAbilityId);

        // Vendor ne ability disable kar di ho to job silently drop karo
        if (!$vendorAbility || !$vendorAbility->enabled) {
            return;
        }

        try {
            $syncService->push($vendorAbility, $this->entity, $this->localId, $this->localData);
        } catch (ErpApiException $e) {
            // ErpSyncService->push() already log/status update kar chuki hai — sirf
            // Laravel ko exception dikhao taake retry/backoff mechanism trigger ho.
            throw $e;
        }
    }

    /**
     * Saari retries khatam hone ke baad final failure record karo.
     */
    public function failed(Throwable $exception): void
    {
        $vendorAbility = VendorAbility::find($this->vendorAbilityId);

        if (!$vendorAbility) {
            return;
        }

        $vendorAbility->update([
            'sync_status' => 'failed',
            'last_error'  => 'Max retries exhausted: ' . $exception->getMessage(),
        ]);

        AbilitySyncLog::create([
            'vendor_ability_id' => $vendorAbility->id,
            'entity'            => $this->entity,
            'local_id'          => $this->localId,
            'remote_id'         => null,
            'status'            => 'failed',
            'error_message'     => 'Max retries exhausted: ' . $exception->getMessage(),
        ]);
    }
}
