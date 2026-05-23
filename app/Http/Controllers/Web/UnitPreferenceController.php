<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\UnitConversionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class UnitPreferenceController extends Controller
{
    public function update(Request $request, UnitConversionService $unitService)
    {
        $data = $request->validate([
            'preferred_length_unit' => 'nullable|string|max:10',
            'preferred_mass_unit' => 'nullable|string|max:10',
            'preferred_area_unit' => 'nullable|string|max:10',
            'previous_url' => 'nullable|string',
        ]);

        $preferences = [];

        foreach (['length', 'mass', 'area'] as $type) {
            $key = "preferred_{$type}_unit";

            if (!empty($data[$key]) && $unitService->isValidUnit($type, $data[$key])) {
                $preferences[$key] = $data[$key];
                Cookie::queue($key, $data[$key], 30 * 24 * 60);
                session([$key => $data[$key]]);
            }
        }

        if (auth()->check() && !empty($preferences)) {
            auth()->user()->update($preferences);
        }

        return !empty($data['previous_url'])
            ? redirect($data['previous_url'])
            : redirect()->back();
    }
}
