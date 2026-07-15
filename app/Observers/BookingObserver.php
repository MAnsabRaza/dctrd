<?php

namespace App\Observers;

use App\Models\Booking;

class BookingObserver
{
    use DispatchesErpSync;

    public function created(Booking $booking): void
    {
        $this->sync($booking);
    }

    public function updated(Booking $booking): void
    {
        $this->sync($booking);
    }

    protected function sync(Booking $booking): void
    {
        $vendorId = $this->resolveVendorId($booking);

        if (!$vendorId) {
            return;
        }

        $this->dispatchErpPush($vendorId, 'booking', $booking->id, $booking->toArray());
    }

    protected function resolveVendorId(Booking $booking): ?int
    {
        return $booking->vendor_id ?? $booking->user_id ?? null;
    }
}
