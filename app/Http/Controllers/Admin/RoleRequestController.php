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

        // ---- TAB 1: Request (pending queue) — jaisa tha waisa hi hai ----
        $query = UserRoleRequest::with(['user', 'roleCatalog'])
            ->pending();

        if ($request->filled('family')) {
            $query->whereHas('roleCatalog', fn ($q) => $q->where('family', $request->family));
        }

        $requests = $query->orderBy('requested_at', 'desc')
            ->paginate(15, ['*'], 'requests_page');

        // ---- TAB 2: Request List (sab statuses, sab user roles) ----
        $listQuery = UserRoleRequest::with(['user', 'roleCatalog']);

        if ($request->filled('family')) {
            $listQuery->whereHas('roleCatalog', fn ($q) => $q->where('family', $request->family));
        }

        $allRequests = $listQuery->orderBy('requested_at', 'desc')
            ->paginate(15, ['*'], 'list_page');

        // ---- Edit se aayi hui specific request (Request tab mein highlight hogi) ----
        $editingRequest = null;
        if ($request->filled('edit')) {
            $editingRequest = UserRoleRequest::with(['user', 'roleCatalog'])->find($request->query('edit'));
        }

        // Kaunsa tab active rahega
        $activeTab = $request->filled('edit') || $request->query('tab') === 'request'
            ? 'request'
            : ($request->query('tab') === 'list' ? 'list' : 'request');

        return view('admin.role_requests.index', [
            'pageTitle'      => 'Role Requests',
            'requests'       => $requests,
            'allRequests'    => $allRequests,
            'editingRequest' => $editingRequest,
            'activeTab'      => $activeTab,
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

    /**
     * Request List se "Edit" click -> Request tab pe le jata hai
     * taake wahi record approve/reject kiya ja sake.
     */
    public function edit($id)
    {
        $this->authorize('admin_role_requests');

        UserRoleRequest::findOrFail($id); // sirf existence check

        return redirect()->route('admin.role_requests.index', [
            'tab'  => 'request',
            'edit' => $id,
        ]);
    }

    /**
     * Request List se user role request delete karna.
     */
    public function delete($id)
    {
        $this->authorize('admin_role_requests');

        $roleRequest = UserRoleRequest::findOrFail($id);
        $roleRequest->delete();

        return back()->with('success', 'Role request deleted.');
    }
}