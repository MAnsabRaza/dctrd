<?php

namespace App\Services\Erp\Drivers;

use App\Contracts\ErpDriverInterface;
use App\Models\VendorAbility;
use App\Services\Erp\ErpApiClient;
use App\Services\Erp\ErpApiException;

abstract class AbstractPerfexDriver implements ErpDriverInterface
{
    public function __construct(protected ErpApiClient $client)
    {
    }

    /**
     * entity name -> Perfex ERP resource path. Har flow apna map override kar
     * sakta hai (e.g. Flow 3 sab kuch supplier-feed se guzarta hai).
     */
    protected function entityPathMap(): array
    {
        return [
            'customer' => 'clients',
            'product'  => 'items',
            'order'    => 'invoices',
            'booking'  => 'appointments',
            'payment'  => 'payments',
        ];
    }

    protected function pathFor(string $entity): string
    {
        $map = $this->entityPathMap();

        if (!isset($map[$entity])) {
            throw new ErpApiException("Unsupported entity [{$entity}] for driver " . static::class);
        }

        return $map[$entity];
    }

    public function pushEntity(VendorAbility $vendorAbility, string $entity, array $payload): array
    {
        $response = $this->client->post($vendorAbility, $this->pathFor($entity), $payload);

        return [
            'remote_id' => (string) ($response['id'] ?? $response['data']['id'] ?? ''),
            'response'  => $response,
        ];
    }

    public function pullEntities(VendorAbility $vendorAbility, string $entity, array $params = []): array
    {
        $response = $this->client->get($vendorAbility, $this->pathFor($entity), $params);

        return $response['data'] ?? $response ?? [];
    }
}
