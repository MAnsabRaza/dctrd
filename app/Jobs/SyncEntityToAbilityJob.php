<?php

namespace App\Jobs;

use App\Models\VendorAbility;
use App\Models\AbilitySyncLog;
use App\Models\ErpIdMapping;
use App\Services\Abilities\AbilityFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEntityToAbilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900]; // 1min, 5min, 15min

    public function __construct(
        protected VendorAbility $vendorAbility,
        protected string $entity,
        protected int $localId,
        protected array $data
    ) {}

    public function handle(): void
    {
        $this->vendorAbility->update(['sync_status' => 'running']);

        $ability = AbilityFactory::make($this->vendorAbility);
        $result = $ability->push($this->entity, $this->data);

        ErpIdMapping::updateOrCreate(
            [
                'vendor_id' => $this->vendorAbility->vendor_id,
                'module'    => $this->entity,
                'local_id'  => $this->localId,
            ],
            [
                'erp_id'         => $result['id'] ?? null,
                'last_synced_at' => now(),
            ]
        );

        AbilitySyncLog::create([
            'vendor_ability_id' => $this->vendorAbility->id,
            'entity'            => $this->entity,
            'local_id'          => $this->localId,
            'remote_id'         => $result['id'] ?? null,
            'status'            => 'success',
            'response_payload'  => json_encode($result),
        ]);

        $this->vendorAbility->update([
            'sync_status'    => 'success',
            'last_synced_at' => now(),
            'last_error'     => null,
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        AbilitySyncLog::create([
            'vendor_ability_id' => $this->vendorAbility->id,
            'entity'            => $this->entity,
            'local_id'          => $this->localId,
            'status'            => 'failed',
            'error_message'     => $exception->getMessage(),
        ]);

        $this->vendorAbility->update([
            'sync_status' => 'failed',
            'last_error'  => $exception->getMessage(),
        ]);
    }
}