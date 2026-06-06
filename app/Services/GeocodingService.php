<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeocodingService
{
    public function geocode(string $address): ?array
    {
        $address = trim($address);

        if ($address === '') {
            return null;
        }

        return Cache::remember('geocode:' . md5($address), now()->addDay(), function () use ($address) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'RocketLMS/1.0',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'limit' => 1,
                ]);

                if (!$response->ok() || empty($response->json(0))) {
                    return null;
                }

                $item = $response->json(0);

                return [
                    'lat' => isset($item['lat']) ? (float) $item['lat'] : null,
                    'lng' => isset($item['lon']) ? (float) $item['lon'] : null,
                    'display_name' => $item['display_name'] ?? null,
                    'address' => $item['address'] ?? [],
                ];
            } catch (\Throwable $e) {
                return null;
            }
        });
    }
}
