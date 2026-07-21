@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Role Requests</h1>
    </div>

    <div class="section-body">
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
                    @foreach($requests as $req)
                        <tr>
                            <td>{{ $req->user->full_name ?? '-' }}</td>
                            <td>{{ $req->roleCatalog->label ?? '-' }}</td>
                            <td>{{ ucfirst($req->roleCatalog->family ?? '-') }}</td>
                            <td>{{ dateTimeFormat($req->requested_at, 'j M Y | H:i') }}</td>
                            <td>
                                <form method="POST" action="{{ getAdminPanelUrl('/role-requests/'.$req->id.'/approve') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">Approve</button>
                                </form>
                                <form method="POST" action="{{ getAdminPanelUrl('/role-requests/'.$req->id.'/reject') }}" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
            <div class="card-footer text-center">
                {{ $requests->links() }}
            </div>
        </div>
    </div>
</section>
@endsection