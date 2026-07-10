<?php

namespace App\Services\Abilities;

interface AbilityInterface
{
    /**
     * Rocket LMS se external system ko data bhejna
     */
    public function push(string $entity, array $data): array;

    /**
     * External system se data khinchna
     */
    public function pull(string $entity, array $filters = []): array;

    /**
     * Connection test karna (config save karte waqt "Test Connection" button ke liye)
     */
    public function testConnection(): bool;
}