<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingRatePlan;
use App\Models\BookingSeason;
use Carbon\Carbon;

class PricingEngine
{
    /**
     * Calculate total price for a booking.
     *
     * @param  Booking  $booking
     * @param  Carbon   $checkIn
     * @param  Carbon   $checkOut
     * @param  int      $adults
     * @param  int      $children
     * @param  array    $extras   [['label'=>'Breakfast','price'=>10,'qty'=>2], ...]
     * @param  float    $promoDiscount  flat discount amount
     * @return array
     */
    public function calculate(
        Booking $booking,
        Carbon $checkIn,
        Carbon $checkOut,
        int $adults = 1,
        int $children = 0,
        array $extras = [],
        float $promoDiscount = 0
    ): array {
        $nights = max(1, $checkIn->diffInDays($checkOut));
        $persons = $adults + $children;

        // Step 1: Base price per unit
        $basePrice = (float) $booking->price;

        // Step 2: Apply seasonal modifier
        $seasonalModifier = $this->getSeasonalModifier($booking, $checkIn, $checkOut);
        $priceAfterSeason = $basePrice * $seasonalModifier;

        // Step 3: Apply day-of-week rate plan
        $dowModifier = $this->getDowModifier($booking, $checkIn);
        $priceAfterDow = $priceAfterSeason * $dowModifier;

        // Step 4: Multiply by nights/hours
        $subtotal = $priceAfterDow * $nights;

        // Step 5: Apply pax pricing
        $paxModifier = $this->getPaxModifier($booking, $adults, $children);
        $priceAfterPax = $subtotal + $paxModifier;

        // Step 6: Extras
        $extrasTotal = collect($extras)->sum(fn($e) => ($e['price'] ?? 0) * ($e['qty'] ?? 1));

        // Step 7: Promo discount
        $afterPromo = max(0, $priceAfterPax + $extrasTotal - $promoDiscount);

        // Step 8: Tax
        $taxRate = (float) $booking->tax / 100;
        $taxAmount = round($afterPromo * $taxRate, 2);
        $total = round($afterPromo + $taxAmount, 2);

        return [
            'nights'             => $nights,
            'persons'            => $persons,
            'base_price'         => round($basePrice, 2),
            'seasonal_modifier'  => round($seasonalModifier, 4),
            'dow_modifier'       => round($dowModifier, 4),
            'price_per_night'    => round($priceAfterDow, 2),
            'subtotal'           => round($subtotal, 2),
            'pax_modifier'       => round($paxModifier, 2),
            'extras_total'       => round($extrasTotal, 2),
            'promo_discount'     => round($promoDiscount, 2),
            'tax_rate'           => $booking->tax,
            'tax_amount'         => $taxAmount,
            'total'              => $total,
            'currency'           => $booking->currency,
        ];
    }

    // ─── Private Helpers ──────────────────────────────────────────────────

    private function getSeasonalModifier(Booking $booking, Carbon $checkIn, Carbon $checkOut): float
    {
        $seasons = $booking->seasons()
            ->where('status', true)
            ->where('start_date', '<=', $checkIn->toDateString())
            ->where('end_date', '>=', $checkIn->toDateString())
            ->orderByDesc('id')
            ->get();

        if ($seasons->isEmpty()) return 1.0;

        $season = $seasons->first();

        if ($season->modifier_type === 'fixed') {
            // Fixed amount per night — returned as a flat add in step 5 equivalent
            return 1.0; // handled separately if needed
        }

        return (float) $season->price_modifier;
    }

    private function getDowModifier(Booking $booking, Carbon $checkIn): float
    {
        $dayOfWeek = $checkIn->dayOfWeek; // 0=Sun, 6=Sat

        $plan = $booking->ratePlans()
            ->where('type', 'dow')
            ->where('status', true)
            ->get()
            ->first(function ($plan) use ($dayOfWeek) {
                $conditions = is_array($plan->conditions)
                    ? $plan->conditions
                    : json_decode($plan->conditions, true);
                $days = $conditions['days_of_week'] ?? [];
                return in_array($dayOfWeek, $days);
            });

        if (!$plan) return 1.0;

        if ($plan->calculation_type === 'fixed') {
            return 1.0; // fixed handled elsewhere
        }

        return (float) $plan->price / 100; // stored as percentage
    }

    private function getPaxModifier(Booking $booking, int $adults, int $children): float
    {
        if (!$booking->price_unit || $booking->price_unit !== 'person') {
            return 0.0;
        }

        // Extra persons beyond first
        $extraPersons = max(0, ($adults + $children) - 1);
        $pricePerPerson = (float) ($booking->price_per ?? $booking->price);

        return $extraPersons * $pricePerPerson;
    }
}