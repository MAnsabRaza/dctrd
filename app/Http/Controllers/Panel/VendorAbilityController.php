<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\VendorAbility;
use App\Services\AbilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class VendorAbilityController extends Controller
{
    public function __construct(protected AbilityService $abilityService)
    {
    }

    /**
     * Vendor "Enable" karta hai (ya already-enabled ability ki config update karta hai)
     */
    public function saveConfig(Request $request, int $abilityId): JsonResponse
    {
         $this->authorize('panel_others_abilities');

        $ability = Ability::where('is_active', true)->findOrFail($abilityId);
        $user    = auth()->user();

        try {
            $vendorAbility = $this->abilityService->enableForVendor(
                $ability,
                $user->id,
                $request->input('config', [])
            );
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => collect($e->errors())->flatten()->first(),
                'errors'  => $e->errors(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => trans('panel.ability_enabled_successfully') ?? 'Saved.',
            'data'    => [
                'enabled'     => $vendorAbility->enabled,
                'sync_status' => $vendorAbility->sync_status,
            ],
        ]);
    }

    /**
     * Vendor ability disable karta hai
     */
    public function disable(int $abilityId): JsonResponse
    {
         $this->authorize('panel_others_abilities');

        $ability = Ability::findOrFail($abilityId);
        $user    = auth()->user();

        $vendorAbility = VendorAbility::where('ability_id', $ability->id)
            ->where('vendor_id', $user->id)
            ->first();

        // Policy check — safety net, agar kabhi id spoof karke aaye
        if ($vendorAbility) {
            $this->authorize('manage', $vendorAbility);
        }

        $this->abilityService->disableForVendor($ability, $user->id);

        return response()->json([
            'success' => true,
            'message' => trans('panel.ability_disabled_successfully') ?? 'Disabled.',
        ]);
    }
}