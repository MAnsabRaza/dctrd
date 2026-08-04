@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Role Requests</h1>
    </div>

    <div class="section-body">

        @if(session('success'))
            <div class="alert alert-success mb-16">{{ session('success') }}</div>
        @endif

        <ul class="nav nav-pills roles-tab-pills mb-16" id="roleRequestsTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'request' ? 'active' : '' }}" id="request-tab"
                   data-toggle="tab" href="#requestTab" role="tab">
                    Request
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'list' ? 'active' : '' }}" id="requestList-tab"
                   data-toggle="tab" href="#requestListTab" role="tab">
                    Request List
                </a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ============================================================ --}}
            {{-- TAB 1: REQUEST — pending queue (UNCHANGED existing logic)    --}}
            {{-- ============================================================ --}}
            <div class="tab-pane fade {{ $activeTab === 'request' ? 'active show' : '' }}" id="requestTab" role="tabpanel">

                {{-- Agar "Request List" se Edit karke aaye hain to us specific record ko highlight karo --}}
                @if($editingRequest)
                    @php
                        $isAlreadyActive = $editingRequest->status === \App\Models\UserRoleRequest::STATUS_ACTIVE;
                    @endphp
                    <div class="card mb-16 border-primary">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <strong>Editing Request #{{ $editingRequest->id }}</strong>
                            <a href="{{ route('admin.role_requests.index', ['tab' => 'request']) }}" class="btn btn-sm btn-light">Close</a>
                        </div>
                        <div class="card-body">
                            <table class="table custom-table font-14 mb-16">
                                <tr>
                                    <th>User</th>
                                    <th>Requested Role</th>
                                    <th>Family</th>
                                    <th>Status</th>
                                    <th>Requested At</th>
                                </tr>
                                <tr>
                                    <td>{{ $editingRequest->user->full_name ?? '-' }}</td>
                                    <td>{{ $editingRequest->roleCatalog->label ?? '-' }}</td>
                                    <td>{{ ucfirst($editingRequest->roleCatalog->family ?? '-') }}</td>
                                    <td>
                                        @if($editingRequest->status === \App\Models\UserRoleRequest::STATUS_ACTIVE)
                                            <span class="badge badge-success">Active</span>
                                        @elseif($editingRequest->status === \App\Models\UserRoleRequest::STATUS_PENDING)
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($editingRequest->status === \App\Models\UserRoleRequest::STATUS_REJECTED)
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($editingRequest->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $editingRequest->requested_at ? $editingRequest->requested_at->format('j M Y | H:i') : '-' }}</td>
                                </tr>
                            </table>

                            <div class="d-flex align-items-center">
                                {{-- Approve: agar already active hai to disabled --}}
                                <form method="POST" action="{{ route('admin.role_requests.approve', $editingRequest->id) }}" class="d-inline mr-2">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" {{ $isAlreadyActive ? 'disabled' : '' }}>
                                        {{ $isAlreadyActive ? 'Already Approved' : 'Approve' }}
                                    </button>
                                </form>

                                {{-- Reject: kabhi bhi disabled nahi hoga --}}
                                <form method="POST" action="{{ route('admin.role_requests.reject', $editingRequest->id) }}" class="d-inline-flex align-items-center">
                                    @csrf
                                    <input type="text" name="reason" class="form-control form-control-sm mx-1" style="width: 220px;" required placeholder="Rejection reason">
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        <table class="table custom-table font-14">
                            <tr>
                                <th>User</th>
                                <th>Requested Role</th>
                                <th>Family</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                            @forelse($requests as $req)
                                <tr>
                                    <td>{{ $req->user->full_name ?? '-' }}</td>
                                    <td>{{ $req->roleCatalog->label ?? '-' }}</td>
                                    <td>{{ ucfirst($req->roleCatalog->family ?? '-') }}</td>
                                    <td>{{ $req->requested_at ? $req->requested_at->format('j M Y | H:i') : '-' }}</td>
                                    <td>
                                        <form method="POST" action="{{ getAdminPanelUrl('/role-requests/'.$req->id.'/approve') }}" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ getAdminPanelUrl('/role-requests/'.$req->id.'/reject') }}" class="d-inline-flex align-items-center">
                                            @csrf
                                            <input type="text" name="reason" class="form-control form-control-sm mx-1" style="width: 220px;" required placeholder="Rejection reason">
                                            <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-gray-500">No pending requests.</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        {{ $requests->links() }}
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- TAB 2: REQUEST LIST — sab statuses, Edit + Delete            --}}
            {{-- ============================================================ --}}
            <div class="tab-pane fade {{ $activeTab === 'list' ? 'active show' : '' }}" id="requestListTab" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <table class="table custom-table font-14">
                            <tr>
                                <th>User</th>
                                <th>Requested Role</th>
                                <th>Family</th>
                                <th>Status</th>
                                <th>Requested At</th>
                                <th>Actions</th>
                            </tr>
                            @forelse($allRequests as $item)
                                <tr>
                                    <td>{{ $item->user->full_name ?? '-' }}</td>
                                    <td>{{ $item->roleCatalog->label ?? '-' }}</td>
                                    <td>{{ ucfirst($item->roleCatalog->family ?? '-') }}</td>
                                    <td>
                                        @if($item->status === \App\Models\UserRoleRequest::STATUS_ACTIVE)
                                            <span class="badge badge-success">Active</span>
                                        @elseif($item->status === \App\Models\UserRoleRequest::STATUS_PENDING)
                                            <span class="badge badge-warning">Pending</span>
                                        @elseif($item->status === \App\Models\UserRoleRequest::STATUS_REJECTED)
                                            <span class="badge badge-danger">Rejected</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($item->status) }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->requested_at ? $item->requested_at->format('j M Y | H:i') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.role_requests.edit', $item->id) }}" class="btn btn-sm btn-primary">Edit</a>
                                        <a href="{{ route('admin.role_requests.delete', $item->id) }}"
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Is role request ko delete karna hai?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-gray-500">No role requests found.</td>
                                </tr>
                            @endforelse
                        </table>
                    </div>
                    <div class="card-footer text-center">
                        {{ $allRequests->links() }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endsection

@push('styles_bottom')
<style>
    .roles-tab-pills {
        display: flex;
        flex-wrap: nowrap;
        gap: 8px;
    }

    .roles-tab-pills .nav-item {
        flex: 0 0 auto;
    }

    .roles-tab-pills .nav-link {
        background-color: #f1f2f6;
        color: #6b7280;
        border-radius: 8px;
        padding: 8px 18px;
        font-weight: 500;
        white-space: nowrap;
        transition: background-color .15s ease, color .15s ease;
    }

    .roles-tab-pills .nav-link:hover {
        background-color: #e5e7eb;
    }

    .roles-tab-pills .nav-link.active {
        background-color: #2563eb;
        color: #ffffff;
    }
</style>
@endpush