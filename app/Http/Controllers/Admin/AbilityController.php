<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ability;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AbilityController extends Controller
{
    public function index()
    {
        $abilities = Ability::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.abilities.index', compact('abilities'));
    }

    public function create()
    {
        return view('admin.abilities.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                    => 'required|string|max:255',
            'type'                    => 'required|in:import,export,booking,dropshipping',
            'driver_class'            => 'required|string|max:255',
            'description'             => 'nullable|string',
            'fields'                  => 'required|array|min:1',
            'fields.*.key'            => 'required|string',
            'fields.*.label'         => 'required|string',
            'fields.*.type'           => 'required|in:text,password,boolean,select,textarea',
            'fields.*.required'       => 'nullable|boolean',
        ]);

        Ability::create([
            'key'          => Str::slug($validated['name'], '_'),
            'name'         => $validated['name'],
            'type'         => $validated['type'],
            'driver_class' => $validated['driver_class'],
            'description'  => $validated['description'] ?? null,
            'schema_json'  => ['fields' => $validated['fields']],
            'is_active'    => true,
        ]);

        return redirect()
            ->route('admin.abilities.index')
            ->with('success', 'Ability created successfully.');
    }

    public function edit(Ability $ability)
    {
        return view('admin.abilities.edit', compact('ability'));
    }

    public function update(Request $request, Ability $ability)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'driver_class' => 'required|string|max:255',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
            'fields'       => 'required|array|min:1',
            'fields.*.key' => 'required|string',
            'fields.*.label' => 'required|string',
            'fields.*.type'  => 'required|in:text,password,boolean,select,textarea',
        ]);

        $ability->update([
            'name'         => $validated['name'],
            'driver_class' => $validated['driver_class'],
            'description'  => $validated['description'] ?? null,
            'is_active'    => $request->boolean('is_active'),
            'schema_json'  => ['fields' => $validated['fields']],
        ]);

        return redirect()
            ->route('admin.abilities.index')
            ->with('success', 'Ability updated successfully.');
    }

    public function destroy(Ability $ability)
    {
        $ability->delete();
        return back()->with('success', 'Ability deleted.');
    }
}