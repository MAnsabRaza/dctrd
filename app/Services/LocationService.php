<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use MatanYadaev\EloquentSpatial\Objects\Point;

class LocationService
{
    public function saveLocation(Model $model, array $data): Model
    {
        $table = $model->getTable();

        // ── Address field ─────────────────────────────────────────────────────
        // Pehle check karo table mein konsa column hai: address_line ya address
        $addressColumn = Schema::hasColumn($table, 'address_line')
            ? 'address_line'
            : (Schema::hasColumn($table, 'address') ? 'address' : null);

        if ($addressColumn) {
            // Form se jo bhi value aayi — address_line ya address — woh save karo
            // Agar dono empty hain toh purani DB value rakho (NULL mat karo)
            $incoming = $data['address_line'] ?? $data['address'] ?? null;

            if ($incoming !== null && $incoming !== '') {
                $model->{$addressColumn} = $incoming;
            }
            // agar incoming empty/null hai toh model ki purani value chhod do — overwrite mat karo
        }

        // ── City, State, Country, Postal Code, Lat, Lng ───────────────────────
        foreach (['city', 'state', 'country', 'postal_code', 'lat', 'lng', 'location_enabled'] as $column) {
            if (!Schema::hasColumn($table, $column)) {
                continue;
            }

            if (!array_key_exists($column, $data)) {
                continue;
            }

            $value = $data[$column];

            // FIX: pehle ?: use ho raha tha jo "0" aur "" dono ko null bana deta tha
            // Ab sirf empty string ko null banao — baaki values as-is save karo
            $model->{$column} = ($value !== '' && $value !== null) ? $value : null;
        }

        // ── Spatial Point (lat/lng -> location column) ────────────────────────
        if (Schema::hasColumn($table, 'location')) {
            if (!empty($data['lat']) && !empty($data['lng'])) {
                $model->location = new Point((float) $data['lat'], (float) $data['lng'], 4326);
            } elseif (array_key_exists('location_enabled', $data) && !$data['location_enabled']) {
                $model->location = null;
            }
        }

        $model->save();

        return $model;
    }

    public function getAddressSuggestions(string $query): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 3) {
            return [];
        }

        return Cache::remember('location_suggestions:' . md5($query), now()->addDay(), function () use ($query) {
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'RocketLMS/1.0',
                ])->get('https://nominatim.openstreetmap.org/search', [
                    'q'              => $query,
                    'format'         => 'json',
                    'addressdetails' => 1,
                    'limit'          => 8,
                ]);

                if (!$response->ok()) {
                    return [];
                }

                return collect($response->json())->map(function ($item) {
                    $address = $item['address'] ?? [];

                    return [
                        'display_name' => $item['display_name'] ?? '',
                        'lat'          => isset($item['lat']) ? (float) $item['lat'] : null,
                        'lng'          => isset($item['lon']) ? (float) $item['lon'] : null,
                        'city'         => $address['city'] ?? $address['town'] ?? $address['village'] ?? null,
                        'state'        => $address['state'] ?? null,
                        'country'      => $address['country'] ?? null,
                        'postal_code'  => $address['postcode'] ?? null,
                    ];
                })->values()->all();

            } catch (\Throwable $e) {
                return [];
            }
        });
    }

    public function detectLocationFromIp(string $ip): array
    {
        try {
            $response = Http::get("http://ip-api.com/json/{$ip}", [
                'fields' => 'status,country,city,lat,lon',
            ]);

            if (!$response->ok() || $response->json('status') !== 'success') {
                return [];
            }

            return [
                'city'    => $response->json('city'),
                'country' => $response->json('country'),
                'lat'     => $response->json('lat'),
                'lng'     => $response->json('lon'),
            ];

        } catch (\Throwable $e) {
            return [];
        }
    }
}