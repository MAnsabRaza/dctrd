@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>Role Catalog</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <table class="table custom-table font-14">
                    <tr>
                        <th>Family</th>
                        <th>Label</th>
                        <th>Bundle (Covers)</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                    @foreach($roles as $role)
                        <tr>
                            <td>{{ ucfirst($role->family) }}</td>
                            <td>{{ $role->label }}</td>
                            <td>{{ !empty($role->supersedes) ? implode(', ', $role->supersedes) : '-' }}</td>
                            <td>
                                @if($role->active)
                                    <span class="text-success fas fa-check"></span>
                                @else
                                    <span class="text-danger fas fa-times"></span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ getAdminPanelUrl('/role-catalog/'.$role->id.'/edit') }}" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                    @endforeach
                </table>
            </div>
        </div>
    </div>
</section>
@endsection