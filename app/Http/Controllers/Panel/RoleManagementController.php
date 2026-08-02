<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\UserRoleRequest;
use App\User;
use App\Services\RoleEligibilityService;
use Illuminate\Http\Request;

class RoleManagementController extends Controller
{
  public function index(RoleEligibilityService $eligibilityService)
{
    $authUser = auth()->user();

    $users = collect([$authUser]);

    $roleCatalogs = $eligibilityService->eligibleRoles($authUser);

    $roleRequests = UserRoleRequest::where('user_id', $authUser->id)
        ->with(['user', 'roleCatalog'])
        ->orderByDesc('requested_at')
        ->orderByDesc('id')
        ->paginate(15);

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
                ->withErrors(['role_catalog_id' => $eligibilityService->ineligibilityMessage($targetUser, (int) $data['role_catalog_id'])]);
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
            ->with('toast', [
                'title' => trans('public.request_success'),
                'msg' => 'Role request submitted successfully. Admin will review it soon.',
                'status' => 'success',
            ])
            ->with('success', 'Role request submitted successfully. Admin will review it soon.');
    }
}
