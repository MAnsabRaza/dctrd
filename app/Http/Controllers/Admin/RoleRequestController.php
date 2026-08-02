<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRoleRequest;
use Illuminate\Http\Request;

class RoleRequestController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_role_requests'); // permission seeder mein yeh key add karo

        $query = UserRoleRequest::with(['user', 'roleCatalog'])
            ->pending();

        if ($request->filled('family')) {
            $query->whereHas('roleCatalog', fn ($q) => $q->where('family', $request->family));
        }

        $requests = $query->orderBy('requested_at', 'desc')->paginate(15);

        return view('admin.role_requests.index', [
            'pageTitle' => 'Role Requests',
            'requests'  => $requests,
        ]);
    }

    public function approve($id)
    {
        $this->authorize('admin_role_requests');

        $roleRequest = UserRoleRequest::findOrFail($id);
        $roleRequest->approve(auth()->id());
        $roleRequest->loadMissing(['user', 'roleCatalog']);

        sendNotification('role_request_approved', [
            '[u.name]' => $roleRequest->user->full_name ?? '',
            '[role.name]' => $roleRequest->roleCatalog->label ?? '',
            '[request.id]' => $roleRequest->id,
            '[link]' => route('panel.roles.index'),
        ], $roleRequest->user_id);

        return back()->with('success', 'Role request approved.');
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('admin_role_requests');

        $data = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $roleRequest = UserRoleRequest::findOrFail($id);
        $roleRequest->reject(auth()->id(), $data['reason'] ?? null);
        $roleRequest->loadMissing(['user', 'roleCatalog']);

        sendNotification('role_request_rejected', [
            '[u.name]' => $roleRequest->user->full_name ?? '',
            '[role.name]' => $roleRequest->roleCatalog->label ?? '',
            '[reason]' => $data['reason'],
            '[request.id]' => $roleRequest->id,
            '[link]' => route('panel.roles.index'),
        ], $roleRequest->user_id);

        return back()->with('success', 'Role request rejected.');
    }
}
