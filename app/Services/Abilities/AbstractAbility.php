<?php

namespace App\Services\Abilities;

use App\Models\VendorAbility;

abstract class AbstractAbility implements AbilityInterface
{
    protected VendorAbility $vendorAbility;
    protected array $config;

    public function __construct(VendorAbility $vendorAbility)
    {
        $this->vendorAbility = $vendorAbility;
        $this->config = $vendorAbility->config_json ?? [];
    }

    /**
     * ability_field_mappings ke hisaab se local data ko remote field names mein convert karna
     */
    protected function mapToRemote(string $entity, array $localData): array
    {
        $mappings = $this->vendorAbility->fieldMappings()
            ->where('entity', $entity)
            ->whereIn('direction', ['export', 'both'])
            ->get();

        if ($mappings->isEmpty()) {
            return $localData; // koi mapping nahi to as-is bhejo
        }

        $mapped = [];
        foreach ($mappings as $map) {
            if (array_key_exists($map->local_field, $localData)) {
                $mapped[$map->remote_field] = $localData[$map->local_field];
            }
        }

        return $mapped;
    }

    /**
     * Remote se aaye data ko local field names mein convert karna
     */
    protected function mapToLocal(string $entity, array $remoteData): array
    {
        $mappings = $this->vendorAbility->fieldMappings()
            ->where('entity', $entity)
            ->whereIn('direction', ['import', 'both'])
            ->get();

        if ($mappings->isEmpty()) {
            return $remoteData;
        }

        $mapped = [];
        foreach ($mappings as $map) {
            if (array_key_exists($map->remote_field, $remoteData)) {
                $mapped[$map->local_field] = $remoteData[$map->remote_field];
            }
        }

        return $mapped;
    }
}