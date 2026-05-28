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
        $rules = [
            'preferred_date_format' => 'nullable|string|max:30',
            'preferred_custom_date_format' => 'nullable|string|max:30',
            'preferred_time_format' => 'nullable|string|max:30',
            'preferred_custom_time_format' => 'nullable|string|max:30',
            'preferred_week_start' => 'nullable|string|max:10',
            'previous_url' => 'nullable|string',
        ];

        foreach ($unitService->getUnitTypes() as $type) {
            $rules["preferred_{$type}_unit"] = 'nullable|string|max:10';
        }

        $data = $request->validate($rules);

        $preferences = [];

        $dateFormat = ($data['preferred_date_format'] ?? null) === 'custom'
            ? ($data['preferred_custom_date_format'] ?? null)
            : ($data['preferred_date_format'] ?? null);

        $timeFormat = ($data['preferred_time_format'] ?? null) === 'custom'
            ? ($data['preferred_custom_time_format'] ?? null)
            : ($data['preferred_time_format'] ?? null);

        foreach ([
            'preferred_date_format' => $dateFormat,
            'preferred_time_format' => $timeFormat,
            'preferred_week_start' => $data['preferred_week_start'] ?? null,
        ] as $key => $value) {
            if (!empty($value)) {
                $preferences[$key] = $value;
                Cookie::queue($key, $value, 30 * 24 * 60);
                session([$key => $value]);
            }
        }

        foreach ($unitService->getUnitTypes() as $type) {
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
