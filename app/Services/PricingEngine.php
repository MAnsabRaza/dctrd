<?php

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;

class PricingEngine
{
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
        $basePrice = (float) ($booking->discount_price ?: $booking->price);

        $nightlyRows = $this->getNightlyRows($booking, $checkIn, $checkOut, $basePrice);
        $subtotal = collect($nightlyRows)->sum('price');
        $averageNightlyPrice = $nights > 0 ? $subtotal / $nights : $basePrice;

        $paxModifier = $this->getPaxModifier($booking, $adults, $children);
        $extrasTotal = collect($extras)->sum(fn($e) => ((float) ($e['price'] ?? 0)) * ((int) ($e['qty'] ?? 1)));

        $afterPromo = max(0, $subtotal + $paxModifier + $extrasTotal - $promoDiscount);

        $taxRate = (float) $booking->tax / 100;
        $taxAmount = round($afterPromo * $taxRate, 2);
        $total = round($afterPromo + $taxAmount, 2);

        return [
            'nights' => $nights,
            'persons' => $persons,
            'base_price' => round($basePrice, 2),
            'price_per_night' => round($averageNightlyPrice, 2),
            'nightly_prices' => $nightlyRows,
            'subtotal' => round($subtotal, 2),
            'pax_modifier' => round($paxModifier, 2),
            'extras_total' => round($extrasTotal, 2),
            'promo_discount' => round($promoDiscount, 2),
            'tax_rate' => $booking->tax,
            'tax_amount' => $taxAmount,
            'total' => $total,
            'currency' => $booking->currency,
        ];
    }

    private function getNightlyRows(Booking $booking, Carbon $checkIn, Carbon $checkOut, float $basePrice): array
    {
        $rows = [];
        $current = $checkIn->copy();

        while ($current->lt($checkOut)) {
            $seasonAdjustment = $this->getSeasonAdjustment($booking, $current);
            $rateAdjustment = $this->getRatePlanAdjustment($booking, $current);

            $price = $this->applyAdjustment($basePrice, $seasonAdjustment);
            $price = $this->applyAdjustment($price, $rateAdjustment);

            $rows[] = [
                'date' => $current->toDateString(),
                'base_price' => round($basePrice, 2),
                'season_adjustment' => $seasonAdjustment,
                'rate_plan_adjustment' => $rateAdjustment,
                'price' => round(max(0, $price), 2),
            ];

            $current->addDay();
        }

        return $rows;
    }

    private function applyAdjustment(float $price, ?array $adjustment): float
    {
        if (empty($adjustment)) {
            return $price;
        }

        if (($adjustment['type'] ?? null) === 'fixed') {
            return $price + (float) ($adjustment['amount'] ?? 0);
        }

        return $price * (float) ($adjustment['multiplier'] ?? 1);
    }

    private function getSeasonAdjustment(Booking $booking, Carbon $date): ?array
    {
        $season = $booking->seasons()
            ->where('status', true)
            ->where('start_date', '<=', $date->toDateString())
            ->where('end_date', '>=', $date->toDateString())
            ->orderByDesc('id')
            ->first();

        if (!$season) {
            return null;
        }

        if ($season->modifier_type === 'fixed') {
            return ['type' => 'fixed', 'amount' => (float) $season->price_modifier];
        }

        return ['type' => 'percent', 'multiplier' => (float) $season->price_modifier];
    }

    private function getRatePlanAdjustment(Booking $booking, Carbon $date): ?array
    {
        $dayOfWeek = $date->dayOfWeek; // 0=Sun, 6=Sat

        $plan = $booking->ratePlans()
            ->where('type', 'dow')
            ->where('status', true)
            ->get()
            ->first(function ($plan) use ($dayOfWeek) {
                $conditions = is_array($plan->conditions)
                    ? $plan->conditions
                    : json_decode($plan->conditions, true);

                $days = $conditions['days_of_week'] ?? [];

                return in_array($dayOfWeek, array_map('intval', $days));
            });

        if (!$plan) {
            return null;
        }

        if ($plan->calculation_type === 'fixed') {
            return ['type' => 'fixed', 'amount' => (float) $plan->price];
        }

        return ['type' => 'percent', 'multiplier' => (float) $plan->price / 100];
    }

    private function getPaxModifier(Booking $booking, int $adults, int $children): float
    {
        if (!$booking->price_unit || $booking->price_unit !== 'person') {
            return 0.0;
        }

        $extraPersons = max(0, ($adults + $children) - 1);
        $pricePerPerson = (float) ($booking->price_per ?? $booking->price);

        return $extraPersons * $pricePerPerson;
    }
}
