<?php

namespace App\Observers;

use App\Models\Product;

class ProductObserver
{
    use DispatchesErpSync;

    public function created(Product $product): void
    {
        $this->sync($product);
    }

    public function updated(Product $product): void
    {
        $this->sync($product);
    }

    protected function sync(Product $product): void
    {
        $vendorId = $this->resolveVendorId($product);

        if (!$vendorId) {
            return;
        }

        $this->dispatchErpPush($vendorId, 'product', $product->id, $product->toArray());
    }

    protected function resolveVendorId(Product $product): ?int
    {
        return $product->vendor_id ?? $product->user_id ?? null;
    }
}
