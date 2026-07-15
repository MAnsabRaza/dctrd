<?php

namespace App\Observers;

use App\Models\Payment;

class PaymentObserver
{
    use DispatchesErpSync;

    public function created(Payment $payment): void
    {
        $this->sync($payment);
    }

    public function updated(Payment $payment): void
    {
        $this->sync($payment);
    }

    protected function sync(Payment $payment): void
    {
        $vendorId = $this->resolveVendorId($payment);

        if (!$vendorId) {
            return;
        }

        $this->dispatchErpPush($vendorId, 'payment', $payment->id, $payment->toArray());
    }

    protected function resolveVendorId(Payment $payment): ?int
    {
        return $payment->vendor_id ?? $payment->user_id ?? null;
    }
}
