<?php

namespace App\Models\Traits;

use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Traits\HasSpatial;

trait HasNearbyLocation
{
    use HasSpatial;

    public function initializeHasNearbyLocation(): void
    {
        $this->casts = array_merge($this->casts ?? [], [
            'location' => Point::class,
        ]);
    }

    public function scopeNearby($query, float $lat, float $lng, float $radiusKm = 50)
    {
        $point = new Point($lat, $lng, 4326);

        return $query
            ->whereNotNull($this->qualifyColumn('location'))
            ->withDistance('location', $point)
            ->orderByDistance('location', $point)
            ->having('distance', '<=', $radiusKm * 1000);
    }
}
