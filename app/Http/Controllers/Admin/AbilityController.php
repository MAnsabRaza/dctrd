<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use App\Models\VendorAbility;
use App\Services\AbilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbilityController extends Controller
{
    /**
     * List tab + (agar koi edit nahi ho raha to) empty create form
     */
    public function index()
    {
        $this->authorize('admin_abilities');

        $abilities = Ability::withCount('vendorAbilities')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.abilities.index', [
            'abilities'   => $abilities,
            'editAbility' => null,
        ]);
    }

    /**
     * Same view, lekin $editAbility set hone se form "Edit" mode mein khulta hai
     */
    public function edit($id)
    {
        $this->authorize('admin_abilities_edit');

        $editAbility = Ability::findOrFail($id);

        $abilities = Ability::withCount('vendorAbilities')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.abilities.index', [
            'abilities'   => $abilities,
            'editAbility' => $editAbility,
        ]);
    }

    /**
     * Vendor Assignment Page — konsa vendor konsi ability use kar raha hai
     */
    public function show($id)
    {
        $this->authorize('admin_abilities');

        $ability = Ability::findOrFail($id);

        $vendorAbilities = VendorAbility::with('vendor:id,full_name,email')
            ->where('ability_id', $ability->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.abilities.show', [
            'ability'         => $ability,
            'vendorAbilities' => $vendorAbilities,
        ]);
    }

    /**
     * Admin khud kisi vendor ki ability enable/disable kar sake
     */
    public function toggleVendor(Request $request, $id, $vendorAbilityId)
    {
        $this->authorize('admin_abilities_edit');

        $ability = Ability::findOrFail($id);

        $vendorAbility = VendorAbility::where('id', $vendorAbilityId)
            ->where('ability_id', $ability->id)
            ->firstOrFail();

        $enable = $request->boolean('enabled');

        $vendorAbility->update(['enabled' => $enable]);

        app(AbilityService::class)->log(
            $vendorAbility,
            $enable ? 'enable' : 'disable',
            'success'
        );

        return redirect(getAdminPanelUrl() . '/abilities/' . $ability->id . '/show')
            ->with('success', trans('admin/main.save_change'));
    }

    public function store(Request $request)
    {
        $this->authorize('admin_abilities_create');

        $validated = $this->validateAbility($request);

        Ability::create([
            'key'          => Str::slug($validated['name'], '_') . '_' . Str::random(4),
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'driver_class' => $validated['driver_class'],
            'description'  => $validated['description'] ?? null,
            'schema_json'  => ['fields' => $validated['fields']],
            'is_active'    => $request->boolean('status', true),
        ]);

        return redirect(getAdminPanelUrl() . '/abilities')
            ->with('success', trans('admin/main.save_change'));
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_abilities_edit');

        $ability = Ability::findOrFail($id);

        $validated = $this->validateAbility($request);

        $ability->update([
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'driver_class' => $validated['driver_class'],
            'description'  => $validated['description'] ?? null,
            'schema_json'  => ['fields' => $validated['fields']],
            'is_active'    => $request->boolean('status', true),
        ]);

        return redirect(getAdminPanelUrl() . '/abilities')
            ->with('success', trans('admin/main.save_change'));
    }

    public function delete($id)
    {
        $this->authorize('admin_abilities_delete');

        $ability = Ability::findOrFail($id);
        $ability->delete();

        return redirect(getAdminPanelUrl() . '/abilities')
            ->with('success', trans('admin/main.deleted_successfully'));
    }

    protected function validateAbility(Request $request): array
    {
        // Blade form parallel arrays bhejta hai (field_key[], field_label[], field_type[],
        // field_options[], field_required[index])
        $keys     = $request->input('field_key', []);
        $labels   = $request->input('field_label', []);
        $types    = $request->input('field_type', []);
        $options  = $request->input('field_options', []);
        $required = $request->input('field_required', []); // associative: [index => "1"]

        $fields = [];
        foreach ($keys as $index => $key) {
            $key = trim($key);
            if ($key === '') {
                continue; // khali rows skip
            }

            $fieldType = $types[$index] ?? 'text';

            $fieldData = [
                'key'      => $key,
                'label'    => trim($labels[$index] ?? $key),
                'type'     => $fieldType,
                'required' => !empty($required[$index]),
            ];

            // sirf "select" type ke liye options attach karo
            // Admin comma-separated likhega: "hourly,daily,weekly"
            if ($fieldType === 'select') {
                $rawOptions = trim($options[$index] ?? '');
                $fieldData['options'] = $rawOptions !== ''
                    ? array_values(array_filter(array_map('trim', explode(',', $rawOptions))))
                    : [];
            }

            $fields[] = $fieldData;
        }

        $request->merge(['fields' => $fields]);

        return $request->validate([
            'name'                => 'required|string|max:255',
            'type'                => 'required|in:import,export,booking,dropshipping',
            'driver_class'        => 'required|string|max:255',
            'description'         => 'nullable|string',
            'fields'              => 'required|array|min:1',
            'fields.*.key'        => 'required|string',
            'fields.*.label'      => 'required|string',
            'fields.*.type'       => 'required|in:text,password,boolean,select,textarea',
            'fields.*.required'   => 'nullable|boolean',
            'fields.*.options'    => 'nullable|array',
            'fields.*.options.*'  => 'nullable|string',
        ]);
    }
}