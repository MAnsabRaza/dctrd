<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoleCatalog;
use Illuminate\Http\Request;

class RoleCatalogController extends Controller
{
    public function index()
    {
        $this->authorize('admin_role_catalog');

        $roles = RoleCatalog::orderBy('family')->orderBy('sort_order')->get();

        return view('admin.role_catalog.index', [
            'pageTitle' => 'Role Catalog',
            'roles'     => $roles,
        ]);
    }

    public function edit($id)
    {
        $this->authorize('admin_role_catalog');

        $role     = RoleCatalog::findOrFail($id);
        $allRoles = RoleCatalog::where('id', '!=', $id)->orderBy('family')->orderBy('sort_order')->get();

        return view('admin.role_catalog.edit', [
            'pageTitle' => 'Edit Role: ' . $role->label,
            'role'      => $role,
            'allRoles'  => $allRoles,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_role_catalog');

        $role = RoleCatalog::findOrFail($id);

        $data = $request->validate([
            'label'       => 'required|string|max:255',
            'active'      => 'nullable',
            'supersedes'  => 'nullable|array', // "role bundle" — yeh role kin roles ko already cover karta hai
        ]);

        $role->update([
            'label'      => $data['label'],
            'active'     => $request->boolean('active'),
            'supersedes' => $data['supersedes'] ?? [],
        ]);

        return redirect(getAdminPanelUrl('/role-catalog'))->with('success', 'Role updated.');
    }
}