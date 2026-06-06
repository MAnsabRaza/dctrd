<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Product;
use App\Models\Webinar;
use App\Services\LocationService;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function save(Request $request, LocationService $locationService)
    {
        $user = auth()->user();

        if (empty($user)) {
            abort(403);
        }

        $data = $request->validate([
            'address_line' => 'nullable|string|max:255',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'lat' => 'nullable|numeric',
            'lng' => 'nullable|numeric',
        ]);

        $locationService->saveLocation($user, $data);

        return response()->json(['success' => true]);
    }

    public function suggestions(Request $request, LocationService $locationService)
    {
        return response()->json($locationService->getAddressSuggestions((string) $request->get('q', '')));
    }

    public function detect(Request $request, LocationService $locationService)
    {
        return response()->json($locationService->detectLocationFromIp($request->ip()));
    }

    public function courses(Request $request)
    {
        return $this->nearbyResponse(Webinar::query()->where('status', Webinar::$active), $request, 'title');
    }

    public function products(Request $request)
    {
        return $this->nearbyResponse(Product::query()->where('status', Product::$active), $request, 'title');
    }

    public function bookings(Request $request)
    {
        return $this->nearbyResponse(Booking::query()->published(), $request, 'title');
    }

    private function nearbyResponse($query, Request $request, string $titleField)
    {
        if ($request->filled(['lat', 'lng', 'radius_km'])) {
            $query->nearby((float) $request->lat, (float) $request->lng, (float) $request->radius_km);
        }

        $items = $query->limit(50)->get();

        return response()->json($items->map(function ($item) use ($titleField) {
            return [
                'id' => $item->id,
                'title' => $item->{$titleField},
                'city' => $item->city,
                'distance_km' => isset($item->distance) ? round($item->distance / 1000, 2) : null,
            ];
        })->values());
    }
}
