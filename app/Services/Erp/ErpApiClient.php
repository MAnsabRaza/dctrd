<?php

namespace App\Services\Erp;

use App\Models\VendorAbility;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * ERP (Perfex) ke saath saari raw HTTP baat-cheet yahan se hoti hai. Drivers
 * ismein sirf endpoint path + payload dete hain — base_url, api_key, headers,
 * timeout, rate-limit retry sab yahan handle hota hai. Koi driver kabhi
 * direct Http:: use nahi karta.
 *
 * vendor_abilities.config_json mein expected keys:
 *   - api_base_url  (e.g. https://domain-name.com/_ERP/api/v1)
 *   - api_key
 */
class ErpApiClient
{
    protected const TIMEOUT_SECONDS = 15;
    protected const MAX_RATE_LIMIT_RETRIES = 3;

    /**
     * @throws ErpApiException
     */
    public function get(VendorAbility $vendorAbility, string $path, array $query = []): array
    {
        return $this->request($vendorAbility, 'get', $path, $query);
    }

    /**
     * @throws ErpApiException
     */
    public function post(VendorAbility $vendorAbility, string $path, array $body = []): array
    {
        return $this->request($vendorAbility, 'post', $path, $body);
    }

    /**
     * @throws ErpApiException
     */
    public function put(VendorAbility $vendorAbility, string $path, array $body = []): array
    {
        return $this->request($vendorAbility, 'put', $path, $body);
    }

    /**
     * @throws ErpApiException
     */
    protected function request(VendorAbility $vendorAbility, string $method, string $path, array $data): array
    {
        [$baseUrl, $apiKey] = $this->credentials($vendorAbility);

        $url = rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
        $attempt = 0;

        while (true) {
            $attempt++;

            /** @var Response $response */
            $response = $this->client($apiKey)->{$method}($url, $data);

            if ($response->status() === 429) {
                if ($attempt > self::MAX_RATE_LIMIT_RETRIES) {
                    throw new ErpApiException(
                        'ERP rate limit exceeded, max retries reached',
                        ['url' => $url, 'status' => 429],
                        429
                    );
                }

                $retryAfter = (int) ($response->header('Retry-After') ?: 2 ** $attempt);
                Log::warning('ERP API 429 — retrying', ['url' => $url, 'retry_after' => $retryAfter, 'attempt' => $attempt]);
                sleep(max(1, $retryAfter));
                continue;
            }

            if ($response->failed()) {
                throw new ErpApiException(
                    'ERP API request failed: ' . $response->status(),
                    ['url' => $url, 'status' => $response->status(), 'body' => $response->json() ?? $response->body()],
                    $response->status()
                );
            }

            return $response->json() ?? [];
        }
    }

    protected function client(string $apiKey): PendingRequest
    {
        return Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept'        => 'application/json',
            ])
            ->timeout(self::TIMEOUT_SECONDS)
            ->connectTimeout(5)
            ->retry(2, 500, function ($exception) {
                // Connection-level errors ko retry karo, 4xx/429 ko nahi (wo upar handle hota hai)
                return $exception instanceof \Illuminate\Http\Client\ConnectionException;
            });
    }

    /**
     * @return array{0: string, 1: string}
     * @throws ErpApiException
     */
    protected function credentials(VendorAbility $vendorAbility): array
    {
        $config = $vendorAbility->config_json ?? [];

        $baseUrl = $config['api_base_url'] ?? null;
        $apiKey  = $config['api_key'] ?? null;

        if (empty($baseUrl) || empty($apiKey)) {
            // Requirement: agar API key/config nahi hai to sync silently skip ho —
            // caller (ErpSyncService) is exception ko catch karke skip treat karega.
            throw new ErpApiException('Vendor ability missing api_base_url or api_key', [
                'vendor_ability_id' => $vendorAbility->id,
            ]);
        }

        return [$baseUrl, $apiKey];
    }
}
