<?php

namespace App\Services\Abilities;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PerfexExportAbility extends AbstractAbility
{
    public function push(string $entity, array $data): array
    {
        $endpoint = rtrim($this->config['api_base_url'], '/') . "/{$entity}";
        $payload = $this->mapToRemote($entity, $data);

        $response = Http::withHeaders([
            'Authorization' => $this->config['api_key'] ?? '',
            'Accept'        => 'application/json',
        ])->timeout(15)->post($endpoint, $payload);

        if ($response->failed()) {
            Log::error("PerfexExportAbility push failed", [
                'entity' => $entity,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException("Perfex push failed: " . $response->status());
        }

        return $response->json();
    }

    public function pull(string $entity, array $filters = []): array
    {
        $endpoint = rtrim($this->config['api_base_url'], '/') . "/{$entity}";

        $response = Http::withHeaders([
            'Authorization' => $this->config['api_key'] ?? '',
            'Accept'        => 'application/json',
        ])->timeout(15)->get($endpoint, $filters);

        if ($response->failed()) {
            throw new \RuntimeException("Perfex pull failed: " . $response->status());
        }

        $items = $response->json('data', []);

        return array_map(fn($item) => $this->mapToLocal($entity, $item), $items);
    }

    public function testConnection(): bool
    {
        try {
            $endpoint = rtrim($this->config['api_base_url'], '/') . "/ping";
            $response = Http::withHeaders([
                'Authorization' => $this->config['api_key'] ?? '',
            ])->timeout(10)->get($endpoint);

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}