<?php

namespace App\Jobs;

use App\Models\VendorAbility;
use App\Models\AbilitySyncLog;
use App\Services\Abilities\AbilityFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PullEntityFromAbilityJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 900];

    public function __construct(
        protected VendorAbility $vendorAbility,
        protected string $entity
    ) {}

    public function handle(): void
    {
        $this->vendorAbility->update(['sync_status' => 'running']);

        $ability = AbilityFactory::make($this->vendorAbility);
        $items = $ability->pull($this->entity);

        foreach ($items as $item) {
            // TODO: yahan actual Product/Booking model create/update karna hai
            // Product::updateOrCreate(['erp_ref' => $item['id']], $item);
        }

        AbilitySyncLog::create([
            'vendor_ability_id' => $this->vendorAbility->id,
            'entity'            => $this->entity,
            'status'            => 'success',
            'response_payload'  => json_encode(['count' => count($items)]),
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
            'status'            => 'failed',
            'error_message'     => $exception->getMessage(),
        ]);

        $this->vendorAbility->update([
            'sync_status' => 'failed',
            'last_error'  => $exception->getMessage(),
        ]);
    }
}