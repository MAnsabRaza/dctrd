<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ability;
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
        // field_required[index]) -- rateplan.blade.php condition_key[]/condition_value[]
        // ke pattern jaisa. Yahan unhe ek structured "fields" array mein zip karte hain.
        $keys     = $request->input('field_key', []);
        $labels   = $request->input('field_label', []);
        $types    = $request->input('field_type', []);
        $required = $request->input('field_required', []); // associative: [index => "1"]

        $fields = [];
        foreach ($keys as $index => $key) {
            $key = trim($key);
            if ($key === '') {
                continue; // khali rows skip
            }
            $fields[] = [
                'key'      => $key,
                'label'    => trim($labels[$index] ?? $key),
                'type'     => $types[$index] ?? 'text',
                'required' => !empty($required[$index]),
            ];
        }

        $request->merge(['fields' => $fields]);

        return $request->validate([
            'name'              => 'required|string|max:255',
            'type'              => 'required|in:import,export,booking,dropshipping',
            'driver_class'      => 'required|string|max:255',
            'description'       => 'nullable|string',
            'fields'            => 'required|array|min:1',
            'fields.*.key'      => 'required|string',
            'fields.*.label'    => 'required|string',
            'fields.*.type'     => 'required|in:text,password,boolean,select,textarea',
            'fields.*.required' => 'nullable|boolean',
        ]);
    }
}