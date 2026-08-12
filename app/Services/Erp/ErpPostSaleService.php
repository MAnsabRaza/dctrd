<?php

namespace App\Services\Erp;

use App\Models\ErpCredential;
use App\Models\ErpPostSaleSync;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class ErpPostSaleService
{
    public function syncSale(Sale $sale, OrderItem $orderItem): ?ErpPostSaleSync
    {
        $product = $orderItem->product ?: optional($orderItem->productOrder)->product;

        if (!$product instanceof Product || !$product->erp_post_sale_enabled) {
            return null;
        }

        $order = $orderItem->order ?: $sale->order;
        $buyer = $orderItem->user ?: $sale->buyer ?: optional($order)->user;
        $vendorId = (int) ($product->creator_id ?: $sale->seller_id);

        if (empty($order) || empty($buyer) || empty($vendorId)) {
            Log::warning('ERP post-sale skipped because order, buyer, or vendor is missing.', [
                'sale_id' => $sale->id,
                'order_item_id' => $orderItem->id,
            ]);

            return null;
        }

        $invoiceNumber = $this->invoiceNumber($order->id);
        $payload = $this->buildPayload($sale, $orderItem, $product, $invoiceNumber);

        $sync = ErpPostSaleSync::firstOrCreate([
            'vendor_id' => $vendorId,
            'order_id' => $order->id,
            'invoice_number' => $invoiceNumber,
            'product_id' => $product->id,
        ], [
            'sale_id' => $sale->id,
            'status' => 'pending',
            'request_payload' => $payload,
        ]);

        if ($sync->status === 'success') {
            return $sync;
        }

        $credential = ErpCredential::where('vendor_id', $vendorId)
            ->where('type', 'import_export')
            ->where('is_active', true)
            ->first();

        if (empty($credential) || empty($credential->base_url) || empty($credential->api_key)) {
            return $this->markFailed($sync, $payload, 'Active Perfex ERP credential is not configured for this seller.');
        }

        $sync->update([
            'sale_id' => $sale->id,
            'status' => 'pending',
            'attempts' => $sync->attempts + 1,
            'request_payload' => $payload,
            'last_attempted_at' => now(),
            'error_message' => null,
        ]);

        $result = (new ErpClient($credential))->createProjectFromOrder($payload);

        if (!empty($result['success'])) {
            $body = $result['body'] ?? [];

            $sync->update([
                'status' => 'success',
                'remote_project_id' => Arr::get($body, 'project_id'),
                'response_payload' => $body,
                'error_message' => null,
            ]);

            return $sync;
        }

        return $this->markFailed(
            $sync,
            $payload,
            $result['error'] ?? ('Perfex HTTP ' . ($result['status'] ?? 0)),
            $result['body'] ?? []
        );
    }

    public function buildPayload(Sale $sale, OrderItem $orderItem, Product $product, string $invoiceNumber): array
    {
        $order = $orderItem->order ?: $sale->order;
        $buyer = $orderItem->user ?: $sale->buyer ?: optional($order)->user;
        $category = $product->erp_category_name ?: optional($product->category)->title;
        $subcategory = $product->erp_subcategory_name ?: '';

        return [
            'invoice_number' => $invoiceNumber,
            'lms_order_id' => optional($order)->id,
            'product_id' => $product->id,
            'product_variant' => optional($orderItem->productOrder)->id,
            'purchase_date' => date('Y-m-d', $sale->created_at ?: time()),
            'product_title' => $product->title,
            'category' => $category ?: 'Rocket LMS',
            'subcategory' => $subcategory,
            'amount' => (float) ($orderItem->total_amount ?? $sale->total_amount ?? 0),
            'description' => trim(strip_tags($product->summary ?: $product->description ?: '')),
            'customer_email' => optional($buyer)->email,
            'tasks' => $this->buildTasks($product),
        ];
    }

    protected function buildTasks(Product $product): array
    {
        $staffIds = collect($product->erp_staff_ids ?: [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();

        $templateTitles = collect($product->erp_task_templates ?: [])
            ->map(fn ($title) => trim((string) $title))
            ->filter()
            ->values();

        if ($templateTitles->isEmpty()) {
            $templateTitles = collect([$product->title]);
        }

        if ($staffIds->isEmpty()) {
            return $templateTitles->map(fn ($title) => ['title' => $title, 'assigned_to' => null])->all();
        }

        $tasks = [];
        foreach ($templateTitles as $index => $title) {
            $tasks[] = [
                'title' => $title,
                'assigned_to' => $staffIds[$index % $staffIds->count()],
            ];
        }

        return $tasks;
    }

    protected function invoiceNumber(int $orderId): string
    {
        return 'INV-' . $orderId;
    }

    protected function markFailed(ErpPostSaleSync $sync, array $payload, string $message, array $response = []): ErpPostSaleSync
    {
        $sync->update([
            'status' => 'failed',
            'request_payload' => $payload,
            'response_payload' => $response,
            'error_message' => $message,
            'last_attempted_at' => now(),
        ]);

        Log::error('ERP post-sale sync failed', [
            'sync_id' => $sync->id,
            'message' => $message,
            'response' => $response,
        ]);

        return $sync;
    }
}
