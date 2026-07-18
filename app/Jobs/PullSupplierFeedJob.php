<?php

namespace App\Jobs;

use App\Models\ErpCredential;
use App\Services\Erp\ErpClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Flow 2: Supplier feed → ERP → Rocket LMS
 * Vendor "import_dropshipping_enabled" ho to supplier ka feed periodically pull karke
 * apne products table mein dropshipped items create/update karta hai.
 */
class PullSupplierFeedJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    protected int $vendorId;

    public function __construct(int $vendorId)
    {
        $this->vendorId = $vendorId;
        $this->onQueue('erp-sync');
    }

    public function handle(): void
    {
        $credential = ErpCredential::where('vendor_id', $this->vendorId)
            ->where('type', 'import_export')
            ->where('is_active', true)
            ->where('import_dropshipping_enabled', true)
            ->first();

        if (empty($credential)) {
            return;
        }

        $client = new ErpClient($credential);
        $result = $client->pullSupplierFeed();

        if (empty($result['success'])) {
            \Log::warning('PullSupplierFeedJob failed', ['vendor_id' => $this->vendorId, 'result' => $result]);
            return;
        }

        $items = $result['body']['data'] ?? [];

        foreach ($items as $item) {
            // TODO: yahan apne Product/Webinar model ke mutuabiq map karke
            // updateOrCreate karo (external_supplier_ref, dropship_price, stock_availability, etc.)
            // Placeholder chhoda hai kyunke exact Product schema is document mein nahi diya gaya.
        }
    }
}
