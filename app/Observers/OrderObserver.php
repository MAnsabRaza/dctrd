<?php

namespace App\Observers;

use App\Models\Order;

/**
 * NOTE: Order model ka vendor-owner column naam project mein alag ho sakta hai
 * (vendor_id / user_id) — resolveVendorId() mein adjust kar lena agar zaroorat ho.
 */
class OrderObserver
{
    use DispatchesErpSync;

    public function created(Order $order): void
    {
        $this->sync($order);
    }

    public function updated(Order $order): void
    {
        $this->sync($order);
    }

    /**
     * "Cancel" ka matlab yahan order status change hai, hard-delete nahi —
     * isliye alag deleted() handler nahi, updated() hi status change catch kar leta hai.
     */
    protected function sync(Order $order): void
    {
        $vendorId = $this->resolveVendorId($order);

        if (!$vendorId) {
            return;
        }

        $this->dispatchErpPush($vendorId, 'order', $order->id, $order->toArray());
    }

    protected function resolveVendorId(Order $order): ?int
    {
        return $order->vendor_id ?? $order->user_id ?? null;
    }
}
