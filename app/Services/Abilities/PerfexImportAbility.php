<?php

namespace App\Services\Abilities;

use Illuminate\Support\Facades\Http;

class PerfexImportAbility extends AbstractAbility
{
    public function pull(string $entity, array $filters = []): array
    {
        if (empty($this->config['import_dropshipping_enabled'])) {
            return [];
        }

        $endpoint = rtrim($this->config['api_base_url'], '/') . "/{$entity}";

        $response = Http::withHeaders([
            'Authorization' => $this->config['api_key'] ?? '',
        ])->timeout(15)->get($endpoint, $filters);

        if ($response->failed()) {
            throw new \RuntimeException("Perfex import pull failed: " . $response->status());
        }

        $items = $response->json('data', []);

        return array_map(fn($item) => $this->mapToLocal($entity, $item), $items);
    }

    public function push(string $entity, array $data): array
    {
        // Import ability generally sirf pull karti hai, lekin interface fulfill karna zaroori
        throw new \BadMethodCallException("PerfexImportAbility does not support push()");
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