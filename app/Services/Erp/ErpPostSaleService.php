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
                'remote_project_id' => Arr::get($body, 'project_id') ?: Arr::get($body, 'id') ?: Arr::get($body, 'data.project_id') ?: Arr::get($body, 'data.id'),
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
        $invoiceTitle = collect([$category ?: 'Rocket LMS', $subcategory])->filter()->implode(' / ');
        $projectName = $invoiceTitle . ' - ' . $invoiceNumber . ' - ' . $product->title;
        $staffIds = $this->staffIds($product)->all();

        return [
            'invoice_number' => $invoiceNumber,
            'lms_order_id' => optional($order)->id,
            'product_id' => $product->id,
            'product_variant' => optional($orderItem->productOrder)->id,
            'purchase_date' => date('Y-m-d', $sale->created_at ?: time()),
            'project_name' => $projectName,
            'product_title' => $product->title,
            'category' => $category ?: 'Rocket LMS',
            'subcategory' => $subcategory,
            'amount' => (float) ($orderItem->total_amount ?? $sale->total_amount ?? 0),
            'description' => trim(strip_tags($product->summary ?: $product->description ?: '')),
            'customer_email' => optional($buyer)->email,
            'customer_name' => optional($buyer)->full_name,
            'due_date' => $this->dueDate($sale, $product),
            'staff_ids' => $staffIds,
            'tasks' => $this->buildTasks($product),
        ];
    }

    protected function dueDate(Sale $sale, Product $product): ?string
    {
        $days = $product->delivery_estimated_time ?? null;

        if (empty($days) || !is_numeric($days)) {
            return null;
        }

        $purchaseTimestamp = $sale->created_at ?: time();
        $base = is_numeric($purchaseTimestamp) ? (int) $purchaseTimestamp : strtotime((string) $purchaseTimestamp);

        return date('Y-m-d', strtotime('+' . (int) $days . ' days', $base));
    }

    protected function buildTasks(Product $product): array
    {
        $staffIds = $this->staffIds($product);

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
                'assigned' => [$staffIds[$index % $staffIds->count()]],
            ];
        }

        return $tasks;
    }

    protected function staffIds(Product $product)
    {
        return collect($product->erp_staff_ids ?: [])
            ->filter(fn ($id) => is_numeric($id))
            ->map(fn ($id) => (int) $id)
            ->values();
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


        return $sync;
    }
}
