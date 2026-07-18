<?php

namespace App\Jobs;

use App\Services\Erp\ErpSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEntityToErpJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    protected int $vendorId;
    protected string $entityType;
    protected int $localId;
    protected array $payload;
    protected string $action;

    public function __construct(int $vendorId, string $entityType, int $localId, array $payload, string $action = 'create')
    {
        $this->vendorId   = $vendorId;
        $this->entityType = $entityType;
        $this->localId    = $localId;
        $this->payload    = $payload;
        $this->action     = $action;
        $this->onQueue('erp-sync');
    }

    /**
     * Real-time sync ke liye tez retry: 10s, 30s, 1m, 5m, 15m
     */
    public function backoff(): array
    {
        return [10, 30, 60, 300, 900];
    }

    public function handle(ErpSyncService $service): void
    {
        $log = $service->sync($this->vendorId, $this->entityType, $this->localId, $this->payload, $this->action);

        if ($log->status === 'failed' && $this->attempts() < $this->tries) {
            $this->release($this->backoff()[$this->attempts() - 1] ?? 900);
        }
    }

    public function failed(\Throwable $exception): void
    {
        \Log::error('SyncEntityToErpJob permanently failed', [
            'vendor_id'   => $this->vendorId,
            'entity_type' => $this->entityType,
            'local_id'    => $this->localId,
            'error'       => $exception->getMessage(),
        ]);
    }
}
