<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RoleCatalog;
use App\Models\User;
use App\Services\RoleEligibilityService;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index(RoleEligibilityService $eligibilityService)
    {
        $authUser = auth()->user();

        // Panel account ke scope mein aane wale users (staff/instructors/students).
        // Apni actual scoping condition (organ_id waghera) ke hisab se yahan adjust kar lena.
        $users = User::where('organ_id', $authUser->organ_id ?? $authUser->id)
            ->orWhere('id', $authUser->id)
            ->get();

        $roleCatalogs = RoleCatalog::where('status', 1)->get();

        // NOTE: assuming eligibilityService method signature accepts a list of user ids
        // to scope the "all requests" table — agar service mein aisa method nahi hai
        // to ye line apne UserRole model se seedha query mein badal dena, e.g.:
        // $roleRequests = \App\Models\UserRole::whereIn('user_id', $users->pluck('id'))
        //     ->with(['user', 'roleCatalog'])->latest()->paginate(15);
        $roleRequests = $eligibilityService->userRolesWithStatus($authUser, $users->pluck('id')->toArray());

        $data = [
            'pageTitle'    => trans('update.my_roles') ?? 'My Roles',
            'users'        => $users,
            'roleCatalogs' => $roleCatalogs,
            'roleRequests' => $roleRequests,
        ];

        return view('design_1.panel.roles.index', $data);
    }

    public function requestRole(Request $request, RoleEligibilityService $eligibilityService)
    {
        $data = $request->validate([
            'user_id'         => 'required|exists:users,id',
            'role_catalog_id' => 'required|exists:role_catalogs,id',
        ]);

        $targetUser = User::findOrFail($data['user_id']);

        // Confirm ye role is (selected) user ke liye abhi bhi eligible hai (tampering se bachao)
        $eligible = $eligibilityService->eligibleRoles($targetUser)->pluck('id')->toArray();

        if (!in_array((int) $data['role_catalog_id'], $eligible)) {
            return back()
                ->withInput()
                ->withErrors(['role_catalog_id' => 'This role is not eligible for the selected user.']);
        }

        $roleRequest = $eligibilityService->requestRole($targetUser, (int) $data['role_catalog_id']);

        // Safety: status hamesha pending force set (agar service ke andar already ho raha ho to ye no-op hai)
        if ($roleRequest->status !== 'pending') {
            $roleRequest->status = 'pending';
            $roleRequest->save();
        }

        // Admin ko notify karo taake woh Role Requests queue check kare
        sendNotification('role_request_created', [
            '[u.name]'    => $targetUser->full_name,
            '[role.name]' => $roleRequest->roleCatalog->label ?? '',
        ], 1); // 1 = admin user id, jaisa baaki jagah pattern hai

        return redirect()
            ->route('panel.roles.index')
            ->with('success', 'Role request submit ho gayi — admin approval ka intezar hai.');
    }
}