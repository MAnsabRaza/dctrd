<?php

namespace App\Console\Commands;

use App\Services\BookingCartExpiryService;
use Illuminate\Console\Command;

class ExpireBookingCartHolds extends Command
{
    protected $signature = 'bookings:expire-cart-holds';

    protected $description = 'Expire abandoned pending booking cart holds.';

    public function handle(BookingCartExpiryService $expiryService): int
    {
        $expiredCount = $expiryService->expireAbandonedPendingBookings();

        $this->info("Expired {$expiredCount} abandoned booking cart hold(s).");

        return self::SUCCESS;
    }
}
