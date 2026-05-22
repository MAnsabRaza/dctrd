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
        $this->baseCurrency = strtoupper($this->config['base_currency'] ?? 'USD');
    }

    /**
     * Get exchange rate for a currency pair
     */
    public function getRate(string $from, string $to): ?float
    {
        $from = strtoupper($from);
        $to = strtoupper($to);

        // Same currency returns 1
        if ($from === $to) {
            return 1.0;
        }

        if (!$this->supportsCurrency($from) || !$this->supportsCurrency($to)) {
            Log::warning("Unsupported currency pair requested: {$from} to {$to}");
            return null;
        }

        $cacheKey = "exchange_rate_{$from}_{$to}";
        
        return Cache::remember($cacheKey, $this->config['cache_duration'], function () use ($from, $to) {
            $rate = $this->getLatestRateFromDb($from, $to, true);
            
            if ($rate) {
                return $rate;
            }

            if ($this->updateRates()) {
                return $this->getLatestRateFromDb($from, $to);
            }

            return $this->getLatestRateFromDb($from, $to);
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
            $provider = $this->config['primary_api']['provider'] ?? 'primary';
            $rates = $this->fetchFromPrimaryApi();
            
            if (!$rates && $this->config['fallback_on_failure']) {
                Log::warning('Primary API failed, trying backup API');
                $provider = $this->config['backup_api']['provider'] ?? 'backup';
                $rates = $this->fetchFromBackupApi();
            }

            if ($rates) {
                $this->storeRates($rates, $provider);
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
                'symbols' => implode(',', $this->getSupportedCurrencies()),
            ];
            
            if (!empty($config['key'])) {
                $params['access_key'] = $config['key'];
                $params['apikey'] = $config['key'];
            }
            
            $response = Http::timeout($this->config['timeout'])
                ->retry($this->config['retry_attempts'] ?? 1, 250)
                ->get($this->makePrimaryUrl($config), $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($this->hasProviderError($data)) {
                    Log::error('Primary API returned error', ['error' => $data['error'] ?? $data['result'] ?? 'Unknown error']);
                    return null;
                }
                
                return $this->normalizeRates($data);
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
        
        try {
            $params = [
                'from' => $this->baseCurrency,
                'base' => $this->baseCurrency,
                'symbols' => implode(',', $this->getSupportedCurrencies()),
            ];

            if (!empty($config['key'])) {
                $params['access_key'] = $config['key'];
                $params['apikey'] = $config['key'];
            }

            $response = Http::timeout($this->config['timeout'])
                ->retry($this->config['retry_attempts'] ?? 1, 250)
                ->get($this->makeBackupUrl($config), $params);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($this->hasProviderError($data)) {
                    Log::error('Backup API returned error', ['error' => $data['error'] ?? $data['result'] ?? 'Unknown error']);
                    return null;
                }
                
                return $this->normalizeRates($data);
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
            $currency = strtoupper($currency);

            if ($currency === $this->baseCurrency || !is_numeric($rate)) {
                continue;
            }

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
    protected function getLatestRateFromDb(string $from, string $to, bool $freshOnly = false): ?float
    {
        $rate = ExchangeRate::getLatestRate($from, $to);

        if ($rate && (!$freshOnly || $this->isFresh($rate->fetched_at))) {
            return (float) $rate->rate;
        }

        $inverseRate = ExchangeRate::getLatestRate($to, $from);

        if ($inverseRate && (!$freshOnly || $this->isFresh($inverseRate->fetched_at)) && (float) $inverseRate->rate > 0) {
            return round(1 / (float) $inverseRate->rate, 8);
        }

        $fromBase = $from === $this->baseCurrency ? 1.0 : optional(ExchangeRate::getLatestRate($this->baseCurrency, $from))->rate;
        $toBase = $to === $this->baseCurrency ? 1.0 : optional(ExchangeRate::getLatestRate($this->baseCurrency, $to))->rate;

        if ($fromBase && $toBase) {
            return round((float) $toBase / (float) $fromBase, 8);
        }

        return null;
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
        return array_values(array_unique(array_map('strtoupper', $this->config['supported_currencies'] ?? [])));
    }

    /**
     * Check if exchange rate system is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'];
    }

    protected function supportsCurrency(string $currency): bool
    {
        return in_array(strtoupper($currency), $this->getSupportedCurrencies(), true);
    }

    protected function isFresh($fetchedAt): bool
    {
        return Carbon::parse($fetchedAt)->gte(now()->subSeconds($this->config['cache_duration']));
    }

    protected function makePrimaryUrl(array $config): string
    {
        $url = rtrim($config['url'], '/');

        if (($config['provider'] ?? null) === 'exchange_rate_api') {
            return "{$url}/latest/{$this->baseCurrency}";
        }

        return "{$url}/latest";
    }

    protected function makeBackupUrl(array $config): string
    {
        return rtrim($config['url'], '/') . '/latest';
    }

    protected function hasProviderError(array $data): bool
    {
        return (isset($data['success']) && $data['success'] === false)
            || (isset($data['result']) && in_array($data['result'], ['error', 'fail'], true));
    }

    protected function normalizeRates(array $data): ?array
    {
        $rates = $data['rates'] ?? null;

        if (empty($rates) || !is_array($rates)) {
            return null;
        }

        $providerBase = strtoupper($data['base_code'] ?? $data['base'] ?? $this->baseCurrency);

        if ($providerBase !== $this->baseCurrency) {
            if (empty($rates[$this->baseCurrency]) || (float) $rates[$this->baseCurrency] <= 0) {
                Log::warning("Exchange API returned {$providerBase}-based rates without {$this->baseCurrency} cross-rate");
                return null;
            }

            $baseRate = (float) $rates[$this->baseCurrency];

            foreach ($rates as $currency => $rate) {
                $rates[$currency] = (float) $rate / $baseRate;
            }
        }

        $rates[$this->baseCurrency] = 1.0;

        return array_intersect_key($rates, array_flip($this->getSupportedCurrencies()));
    }
}
