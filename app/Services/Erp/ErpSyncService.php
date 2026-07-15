<?php

namespace App\Services\Erp;

use App\Contracts\ErpDriverInterface;
use App\Models\AbilitySyncLog;
use App\Models\Ability;
use App\Models\ErpIdMapping;
use App\Models\VendorAbility;
use Illuminate\Support\Facades\Log;

/**
 * Har push/pull isi service se guzarta hai:
 *   1. payload build (ErpFieldMapper)
 *   2. dedup check (md5 hash erp_id_mappings.sync_hash ke against)
 *   3. driver call (push/pull)
 *   4. erp_id_mappings upsert + ability_sync_logs entry + vendor_abilities.sync_status
 *
 * Success/fail dono cases mein log banta hai — sirf silent skip (dedup) log
 * nahi banata, kyunke wo koi actual API call hi nahi tha.
 */
class ErpSyncService
{
    public function __construct(
        protected ErpApiClient $apiClient,
        protected ErpFieldMapper $mapper,
    ) {
    }

    /**
     * Ek local record ko ERP par push karo (Flow 1 / Flow 3).
     *
     * @return array{status: string, remote_id: ?string} status: success|failed|skipped
     */
    public function push(VendorAbility $vendorAbility, string $entity, int $localId, array $localData): array
    {
        $driver = $this->resolveDriver($vendorAbility->ability);

        $payload = $this->mapper->mapForExport($vendorAbility, $entity, $localData);
        $hash    = md5(json_encode($payload));

        $existing = ErpIdMapping::forLocal($vendorAbility->id, $entity, $localId)->first();

        // Dedup: agar pehle se sync ho chuka hai aur data nahi badla, to skip karo
        if ($existing && $existing->sync_hash === $hash) {
            return ['status' => 'skipped', 'remote_id' => $existing->remote_id];
        }

        try {
            $result   = $driver->pushEntity($vendorAbility, $entity, $payload);
            $remoteId = (string) ($result['remote_id'] ?? '');

            $this->upsertMapping($vendorAbility, $entity, $localId, $remoteId, $hash);
            $this->log($vendorAbility, $entity, $localId, $remoteId, 'success', $result['response'] ?? null);
            $this->touchVendorAbilityStatus($vendorAbility, 'success');

            return ['status' => 'success', 'remote_id' => $remoteId];
        } catch (ErpApiException $e) {
            // Missing config (api key na ho) = silent skip, error nahi
            if (str_contains($e->getMessage(), 'missing api_base_url or api_key')) {
                return ['status' => 'skipped', 'remote_id' => null];
            }

            $this->log($vendorAbility, $entity, $localId, null, 'failed', null, $e->getMessage());
            $this->touchVendorAbilityStatus($vendorAbility, 'failed', $e->getMessage());

            Log::error('ERP push failed', [
                'vendor_ability_id' => $vendorAbility->id,
                'entity'            => $entity,
                'local_id'          => $localId,
                'error'             => $e->getMessage(),
                'context'           => $e->context(),
            ]);

            throw $e; // job ke retry/backoff mechanism ko trigger karne ke liye re-throw
        }
    }

    /**
     * ERP se entities pull karo (Flow 2 dropshipping).
     *
     * @return array<int, array> raw remote records
     */
    public function pull(VendorAbility $vendorAbility, string $entity, array $params = []): array
    {
        $driver = $this->resolveDriver($vendorAbility->ability);

        try {
            $records = $driver->pullEntities($vendorAbility, $entity, $params);
            $this->touchVendorAbilityStatus($vendorAbility, 'success');

            return $records;
        } catch (ErpApiException $e) {
            if (str_contains($e->getMessage(), 'missing api_base_url or api_key')) {
                return [];
            }

            $this->touchVendorAbilityStatus($vendorAbility, 'failed', $e->getMessage());
            Log::error('ERP pull failed', [
                'vendor_ability_id' => $vendorAbility->id,
                'entity'            => $entity,
                'error'             => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function resolveDriver(Ability $ability): ErpDriverInterface
    {
        $class = $ability->driver_class;

        if (!class_exists($class)) {
            throw new ErpApiException("Driver class [{$class}] not found for ability [{$ability->key}]");
        }

        $driver = app($class);

        if (!$driver instanceof ErpDriverInterface) {
            throw new ErpApiException("Driver class [{$class}] must implement ErpDriverInterface");
        }

        return $driver;
    }

    protected function upsertMapping(VendorAbility $vendorAbility, string $entity, int $localId, string $remoteId, string $hash): ErpIdMapping
    {
        return ErpIdMapping::updateOrCreate(
            [
                'vendor_ability_id' => $vendorAbility->id,
                'entity'            => $entity,
                'local_id'          => $localId,
            ],
            [
                'vendor_id'      => $vendorAbility->vendor_id,
                'remote_id'      => $remoteId,
                'sync_hash'      => $hash,
                'last_synced_at' => now(),
            ]
        );
    }

    protected function log(
        VendorAbility $vendorAbility,
        string $entity,
        ?int $localId,
        ?string $remoteId,
        string $status,
        ?array $responsePayload = null,
        ?string $errorMessage = null
    ): AbilitySyncLog {
        return AbilitySyncLog::create([
            'vendor_ability_id' => $vendorAbility->id,
            'entity'            => $entity,
            'local_id'          => $localId,
            'remote_id'         => $remoteId,
            'status'            => $status === 'success' ? 'success' : 'failed',
            'response_payload'  => $responsePayload,
            'error_message'     => $errorMessage,
        ]);
    }

    protected function touchVendorAbilityStatus(VendorAbility $vendorAbility, string $status, ?string $error = null): void
    {
        $vendorAbility->update([
            'sync_status'     => $status === 'success' ? 'success' : 'failed',
            'last_synced_at'  => now(),
            'last_error'      => $status === 'success' ? null : $error,
        ]);
    }
}
