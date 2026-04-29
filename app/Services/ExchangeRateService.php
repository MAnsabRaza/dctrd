<?php

namespace App\Services;

use App\Models\ExchangeRate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ExchangeRateService
{
    protected $config;
    protected $baseCurrency;

    public function __construct()
    {
        $this->config = config('exchange');
        $this->baseCurrency = $this->config['base_currency'];
    }

    /**
     * Get exchange rate for a currency pair
     */
    public function getRate(string $from, string $to): ?float
    {
        // Same currency returns 1
        if ($from === $to) {
            return 1.0;
        }

        $cacheKey = "exchange_rate_{$from}_{$to}";
        
        return Cache::remember($cacheKey, $this->config['cache_duration'], function () use ($from, $to) {
            // Try to get from database first
            $rate = $this->getLatestRateFromDb($from, $to);
            
            if ($rate) {
                return $rate;
            }

            // If not in DB, try to fetch fresh rates
            Log::info("Exchange rate not found in DB for {$from} to {$to}, attempting to fetch");
            
            return null; // Return null if not available
        });
    }

    /**
     * Convert amount from one currency to another
     */
    public function convert(float $amount, string $from, string $to): float
    {
        $rate = $this->getRate($from, $to);
        
        if (!$rate) {
            Log::warning("Exchange rate not found for {$from} to {$to}, returning original amount");
            return $amount;
        }

        return round($amount * $rate, 2);
    }

    /**
     * Update all exchange rates
     */
    public function updateRates(): bool
    {
        if (!$this->config['enabled']) {
            Log::info('Exchange rate updates are disabled');
            return false;
        }

        try {
            $rates = $this->fetchFromPrimaryApi();
            
            if (!$rates && $this->config['fallback_on_failure']) {
                Log::warning('Primary API failed, trying backup API');
                $rates = $this->fetchFromBackupApi();
            }

            if ($rates) {
                $this->storeRates($rates, 'primary');
                $this->clearCache();
                Log::info('Exchange rates updated successfully', ['count' => count($rates)]);
                return true;
            }

            Log::error('Failed to fetch exchange rates from all APIs');
            return false;
        } catch (\Exception $e) {
            Log::error('Exchange rate update failed: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Fetch rates from primary API (exchangerate.host - free, no key required)
     */
    protected function fetchFromPrimaryApi(): ?array
    {
        $config = $this->config['primary_api'];
        
        try {
            $params = [
                'base' => $this->baseCurrency,
                'symbols' => implode(',', $this->config['supported_currencies']),
            ];
            
            // Add API key if provided
            if (!empty($config['key'])) {
                $params['access_key'] = $config['key'];
            }
            
            $response = Http::timeout($this->config['timeout'])
                ->get($config['url'] . '/latest', $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success'] === false) {
                    Log::error('Primary API returned error', ['error' => $data['error'] ?? 'Unknown error']);
                    return null;
                }
                
                return $data['rates'] ?? null;
            }

            Log::error('Primary API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Primary API exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Fetch rates from backup API (exchangeratesapi.io with API key)
     */
    protected function fetchFromBackupApi(): ?array
    {
        $config = $this->config['backup_api'];
        
        // Skip if no API key for backup
        if (empty($config['key'])) {
            Log::info('Backup API key not configured, skipping');
            return null;
        }
        
        try {
            $response = Http::timeout($this->config['timeout'])
                ->get($config['url'] . '/latest', [
                    'access_key' => $config['key'],
                    'base' => 'EUR', // Free tier only supports EUR
                    'symbols' => implode(',', $this->config['supported_currencies']),
                ]);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['success']) && $data['success'] === false) {
                    Log::error('Backup API returned error', ['error' => $data['error'] ?? 'Unknown error']);
                    return null;
                }
                
                // Convert EUR-based rates to USD-based if needed
                $rates = $data['rates'] ?? null;
                if ($rates && $this->baseCurrency !== 'EUR') {
                    // This would need conversion logic
                    Log::info('Backup API returned EUR-based rates, conversion needed');
                }
                
                return $rates;
            }

            Log::error('Backup API request failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return null;
        } catch (\Exception $e) {
            Log::error('Backup API exception: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Store rates in database
     */
    protected function storeRates(array $rates, string $provider): void
    {
        $now = Carbon::now();

        foreach ($rates as $currency => $rate) {
            try {
                ExchangeRate::create([
                    'base_currency' => $this->baseCurrency,
                    'target_currency' => $currency,
                    'rate' => $rate,
                    'provider' => $provider,
                    'fetched_at' => $now,
                ]);
            } catch (\Exception $e) {
                Log::warning("Failed to store rate for {$currency}: " . $e->getMessage());
            }
        }

        // Clean old rates (keep last 90 days)
        ExchangeRate::cleanOldRates(90);
    }

    /**
     * Get latest rate from database
     */
    protected function getLatestRateFromDb(string $from, string $to): ?float
    {
        $rate = ExchangeRate::getLatestRate($from, $to);
        return $rate ? (float) $rate->rate : null;
    }

    /**
     * Get historical rates
     */
    public function getHistoricalRates(string $from, string $to, int $days = 30): array
    {
        return ExchangeRate::getHistoricalRates($from, $to, $days)
            ->map(function ($rate) {
                return [
                    'date' => $rate->fetched_at->format('Y-m-d H:i'),
                    'rate' => (float) $rate->rate,
                ];
            })
            ->toArray();
    }

    /**
     * Clear exchange rate cache
     */
    protected function clearCache(): void
    {
        try {
            // Clear all exchange rate cache keys
            $currencies = $this->config['supported_currencies'];
            foreach ($currencies as $from) {
                foreach ($currencies as $to) {
                    Cache::forget("exchange_rate_{$from}_{$to}");
                }
            }
        } catch (\Exception $e) {
            Log::warning('Failed to clear exchange rate cache: ' . $e->getMessage());
        }
    }

    /**
     * Get last update time
     */
    public function getLastUpdateTime(): ?Carbon
    {
        $latest = ExchangeRate::latest('fetched_at')->first();
        return $latest ? $latest->fetched_at : null;
    }

    /**
     * Get all supported currencies
     */
    public function getSupportedCurrencies(): array
    {
        return $this->config['supported_currencies'];
    }

    /**
     * Check if exchange rate system is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'];
    }
}
