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

        // Optional: user ko notification bhejo
        sendNotification('role_request_approved', [
            '[role.name]' => $roleRequest->roleCatalog->label ?? '',
        ], $roleRequest->user_id);

        return back()->with('success', 'Role request approved.');
    }

    public function reject(Request $request, $id)
    {
        $this->authorize('admin_role_requests');

        $data = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $roleRequest = UserRoleRequest::findOrFail($id);
        $roleRequest->reject(auth()->id(), $data['reason'] ?? null);

        sendNotification('role_request_rejected', [
            '[role.name]' => $roleRequest->roleCatalog->label ?? '',
        ], $roleRequest->user_id);

        return back()->with('success', 'Role request rejected.');
    }
}