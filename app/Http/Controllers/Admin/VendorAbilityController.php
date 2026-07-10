<?php

namespace App\Http\Controllers;

use App\Models\Ability;
use App\Models\VendorAbility;
use App\Services\Abilities\AbilityFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorAbilityController extends Controller
{
    public function index()
    {
        $vendorId = Auth::id();

        $abilities = Ability::where('is_active', true)->get();
        $vendorAbilities = VendorAbility::where('vendor_id', $vendorId)
            ->get()
            ->keyBy('ability_id');

        return view('vendor.abilities.index', compact('abilities', 'vendorAbilities'));
    }

    public function configure(Ability $ability)
    {
        $vendorId = Auth::id();

        $vendorAbility = VendorAbility::firstOrNew([
            'vendor_id'  => $vendorId,
            'ability_id' => $ability->id,
        ]);

        return view('vendor.abilities.configure', compact('ability', 'vendorAbility'));
    }

    public function save(Request $request, Ability $ability)
    {
        $vendorId = Auth::id();
        $fields = $ability->getConfigFields();

        // Dynamic validation schema_json ke fields ke hisaab se
        $rules = [];
        foreach ($fields as $field) {
            $rules["config.{$field['key']}"] = ($field['required'] ?? false) ? 'required' : 'nullable';
        }
        $validated = $request->validate($rules);

        $vendorAbility = VendorAbility::updateOrCreate(
            ['vendor_id' => $vendorId, 'ability_id' => $ability->id],
            ['config_json' => $validated['config'] ?? []]
        );

        return redirect()
            ->route('vendor.abilities.index')
            ->with('success', "{$ability->name} configuration saved.");
    }

    public function toggle(Request $request, VendorAbility $vendorAbility)
    {
        // security: sirf apni ability toggle kare
        abort_unless($vendorAbility->vendor_id === Auth::id(), 403);

        $vendorAbility->update(['enabled' => !$vendorAbility->enabled]);

        return back()->with('success', $vendorAbility->enabled ? 'Ability enabled.' : 'Ability disabled.');
    }

    public function testConnection(VendorAbility $vendorAbility)
    {
        abort_unless($vendorAbility->vendor_id === Auth::id(), 403);

        try {
            $driver = AbilityFactory::make($vendorAbility);
            $ok = $driver->testConnection();

            return response()->json([
                'success' => $ok,
                'message' => $ok ? 'Connection successful.' : 'Connection failed. Check credentials.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function syncNow(VendorAbility $vendorAbility)
    {
        abort_unless($vendorAbility->vendor_id === Auth::id(), 403);
        abort_unless($vendorAbility->enabled, 422);

        // manual pull trigger karna (import type ke liye)
        \App\Jobs\PullEntityFromAbilityJob::dispatch($vendorAbility, 'products');

        return back()->with('success', 'Sync started in background.');
    }
}