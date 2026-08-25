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

        $authUser = auth()->user();

        if ((int) $data['user_id'] !== (int) $authUser->id) {
            abort(403, 'You are not allowed to request a role on behalf of another user.');
        }

        $targetUser = $authUser;

        $eligible = $eligibilityService->eligibleRoles($targetUser)->pluck('id')->toArray();

        if (!in_array((int) $data['role_catalog_id'], $eligible)) {
            $message = $eligibilityService->ineligibilityMessage($targetUser, (int) $data['role_catalog_id']);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['role_catalog_id' => $message]);
        }

        $roleRequest = $eligibilityService->requestRole($targetUser, (int) $data['role_catalog_id']);

        $roleRequest->loadMissing('roleCatalog');

        if ($roleRequest->status === UserRoleRequest::STATUS_PENDING) {
            sendNotification('role_request_created', [
                '[u.name]'    => $targetUser->full_name,
                '[role.name]' => $roleRequest->roleCatalog->label ?? '',
                '[request.id]' => $roleRequest->id,
                '[link]'      => route('admin.role_requests.index'),
            ], 1);
        }

        // AJAX (settings tab se) => JSON, jahan se aaya wahin rahega.
        // Normal form (standalone /panel/roles page) => purana redirect behavior.
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $roleRequest->status === UserRoleRequest::STATUS_ACTIVE
                    ? 'Role activated successfully.'
                    : 'Role request submitted successfully. Admin will review it soon.',
            ]);
        }

        $message = $roleRequest->status === UserRoleRequest::STATUS_ACTIVE
            ? 'Role activated successfully.'
            : 'Role request submitted successfully. Admin will review it soon.';

        return redirect()
            ->route('panel.roles.index')
            ->with('toast', [
                'title' => trans('public.request_success'),
                'msg' => $message,
                'status' => 'success',
            ])
            ->with('success', $message);
    }
}
