<?php

namespace App\Services\Erp;

use App\Models\ErpCredential;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Perfex ERP ke sath low-level HTTP communication.
 * Har call ek vendor ke ErpCredential (type=import_export) ke base_url + api_key use karti hai.
 */
class ErpClient
{
    protected ErpCredential $credential;

    public function __construct(ErpCredential $credential)
    {
        $this->credential = $credential;
    }

    protected function http()
    {
        return Http::withHeaders([
                'X-API-KEY' => $this->credential->api_key,
                'Accept'    => 'application/json',
            ])
            ->baseUrl(rtrim($this->credential->base_url, '/'))
            ->timeout(15)
            ->retry(2, 300); // network-level retry, queue job retry alag se (sync-level)
    }

    /**
     * Client (Customer) create/update — Rocket customer → Perfex client
     */
    public function pushClient(array $payload, ?string $remoteId = null)
    {
        return $this->call($remoteId ? 'PUT' : 'POST', '/api/v1/clients' . ($remoteId ? "/{$remoteId}" : ''), $payload);
    }

    public function pushItem(array $payload, ?string $remoteId = null)
    {
        return $this->call($remoteId ? 'PUT' : 'POST', '/api/v1/items' . ($remoteId ? "/{$remoteId}" : ''), $payload);
    }

    public function pushInvoice(array $payload, ?string $remoteId = null)
    {
        return $this->call($remoteId ? 'PUT' : 'POST', '/api/v1/invoices' . ($remoteId ? "/{$remoteId}" : ''), $payload);
    }

    public function pushAppointment(array $payload, ?string $remoteId = null)
    {
        return $this->call($remoteId ? 'PUT' : 'POST', '/api/v1/appointments' . ($remoteId ? "/{$remoteId}" : ''), $payload);
    }

    public function pushPayment(array $payload, ?string $remoteId = null)
    {
        return $this->call($remoteId ? 'PUT' : 'POST', '/api/v1/payments' . ($remoteId ? "/{$remoteId}" : ''), $payload);
    }

    public function getPostSaleCategories()
    {
        return $this->call('GET', '/api/post_sale_categories');
    }

    public function getStaff()
    {
        $result = $this->call('GET', '/api/post_sale_staff');

        if (!empty($result['success'])) {
            return $result;
        }

        return $this->call('GET', '/api/staff');
    }

    public function createProjectFromOrder(array $payload)
    {
        return $this->call('POST', '/api/create_project_from_order', $payload);
    }

    /**
     * Flow 2: supplier feed pull — dusre supplier ka inventory
     */
    public function pullSupplierFeed(array $query = [])
    {
        return $this->call('GET', '/api/v1/supplier-feed', $query);
    }

    protected function call(string $method, string $uri, array $payload = [])
    {
        try {
            $response = match (strtoupper($method)) {
                'GET'   => $this->http()->get($uri, $payload),
                'PUT'   => $this->http()->put($uri, $payload),
                'DELETE'=> $this->http()->delete($uri, $payload),
                default => $this->http()->post($uri, $payload),
            };

            return [
                'success' => $response->successful(),
                'status'  => $response->status(),
                'body'    => $response->json() ?? [],
            ];
        } catch (\Throwable $e) {
       

            return [
                'success' => false,
                'status'  => 0,
                'body'    => [],
                'error'   => $e->getMessage(),
            ];
        }
    }
}
