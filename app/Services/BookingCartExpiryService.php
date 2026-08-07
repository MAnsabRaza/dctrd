<?php

namespace App\Services;

use App\Models\BookingOrder;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingCartExpiryService
{
    private const DEFAULT_HOLD_MINUTES = 30;

    public function holdMinutes(): int
    {
        return max(1, (int) config('booking.cart_hold_minutes', self::DEFAULT_HOLD_MINUTES));
    }

    public function expiresAt(?BookingOrder $bookingOrder): ?Carbon
    {
        if (empty($bookingOrder) || empty($bookingOrder->created_at)) {
            return null;
        }

        return Carbon::createFromTimestamp((int) $bookingOrder->created_at)
            ->addMinutes($this->holdMinutes());
    }

    public function isExpired(?BookingOrder $bookingOrder): bool
    {
        $expiresAt = $this->expiresAt($bookingOrder);

        return !empty($expiresAt) && $expiresAt->lte(now());
    }

    public function expiryCutoffTimestamp(): int
    {
        return now()->subMinutes($this->holdMinutes())->timestamp;
    }

    public function expireAbandonedPendingBookings(): int
    {
        $expiredOrderIds = BookingOrder::query()
            ->where('status', BookingOrder::$pending)
            ->whereNull('sale_id')
            ->where('created_at', '<=', $this->expiryCutoffTimestamp())
            ->pluck('id');

        if ($expiredOrderIds->isEmpty()) {
            return 0;
        }

        return DB::transaction(function () use ($expiredOrderIds) {
            Cart::query()
                ->whereIn('booking_order_id', $expiredOrderIds)
                ->delete();

            $pendingOrderIds = OrderItem::query()
                ->whereIn('booking_order_id', $expiredOrderIds)
                ->pluck('order_id')
                ->unique()
                ->values();

            OrderItem::query()
                ->whereIn('booking_order_id', $expiredOrderIds)
                ->delete();

            if ($pendingOrderIds->isNotEmpty()) {
                $emptyPendingOrderIds = Order::query()
                    ->whereIn('id', $pendingOrderIds)
                    ->where('status', Order::$pending)
                    ->whereDoesntHave('orderItems')
                    ->pluck('id');

                if ($emptyPendingOrderIds->isNotEmpty()) {
                    Order::query()
                        ->whereIn('id', $emptyPendingOrderIds)
                        ->delete();
                }
            }

            return BookingOrder::query()
                ->whereIn('id', $expiredOrderIds)
                ->delete();
        });
    }
}
