<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $table = 'exchange_rates';

    protected $fillable = [
        'base_currency',
        'target_currency',
        'rate',
        'provider',
        'fetched_at',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];

    /**
     * Get the latest rate for a currency pair
     */
    public static function getLatestRate($from, $to)
    {
        return self::where('base_currency', $from)
            ->where('target_currency', $to)
            ->latest('fetched_at')
            ->first();
    }

    /**
     * Get historical rates for a currency pair
     */
    public static function getHistoricalRates($from, $to, $days = 30)
    {
        return self::where('base_currency', $from)
            ->where('target_currency', $to)
            ->where('fetched_at', '>=', now()->subDays($days))
            ->orderBy('fetched_at', 'desc')
            ->get();
    }

    /**
     * Clean old rates (keep only last 90 days)
     */
    public static function cleanOldRates($daysToKeep = 90)
    {
        return self::where('fetched_at', '<', now()->subDays($daysToKeep))
            ->delete();
    }
}
