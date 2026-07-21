<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RoleCatalog;
use App\Services\RoleEligibilityService;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
    public function index(RoleEligibilityService $eligibilityService)
    {
        $user = auth()->user();

        $data = [
            'pageTitle'      => trans('update.my_roles') ?? 'My Roles',
            'currentRoles'   => $eligibilityService->userRolesWithStatus($user),
            'eligibleRoles'  => $eligibilityService->eligibleRoles($user),
        ];

        return view('design_1.panel.roles.index', $data);
    }

    public function requestRole(Request $request, RoleEligibilityService $eligibilityService)
    {
        $data = $request->validate([
            'role_catalog_id' => 'required|exists:role_catalog,id',
        ]);

        $user = auth()->user();

        // Confirm yeh role abhi bhi eligible list mein hai (race-condition/tampering se bachao)
        $eligible = $eligibilityService->eligibleRoles($user)->pluck('id')->toArray();

        if (!in_array((int) $data['role_catalog_id'], $eligible)) {
            return response()->json([
                'code' => 422,
                'msg'  => 'This role is not eligible for you to request at this time.',
            ], 422);
        }

       $roleRequest = $eligibilityService->requestRole($user, (int) $data['role_catalog_id']);

// Admin ko notify karo taake woh Role Requests queue check kare
sendNotification('role_request_created', [
    '[u.name]'    => $user->full_name,
    '[role.name]' => $roleRequest->roleCatalog->label ?? '',
], 1); // 1 = admin user id, jaisa baaki jagah pattern hai

return response()->json([
            'code'   => 200,
            'msg'    => 'Role request submit ho gayi — admin approval ka intezar hai.',
            'status' => $roleRequest->status,
        ]);
    }
}