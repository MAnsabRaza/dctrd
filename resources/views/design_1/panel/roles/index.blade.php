@extends('design_1.panel.layouts.panel')

@section('content')
<div class="section-header d-flex justify-content-between align-items-center mb-16">
    <h1 class="font-20 font-weight-bold">{{ trans('panel.my_roles') }}</h1>
 
</div>

<div class="bg-white p-16 rounded-16 border-gray-200">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @php
        $createActive = $errors->any() || old('user_id');
    @endphp

    <ul class="nav nav-pills roles-tab-pills mb-16" id="rolesTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $createActive ? '' : 'active' }}" id="rolesList-tab"
               data-toggle="tab" href="#rolesList" role="tab">
                {{ trans('panel.my_roles') }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $createActive ? 'active' : '' }}" id="newRole-tab"
               data-toggle="tab" href="#newRole" role="tab">
                {{ trans('panel.add_role') }}
            </a>
        </li>
    </ul>

    <div class="tab-content">
        {{-- LIST --}}
        <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}" id="rolesList" role="tabpanel">

            @if($roleRequests->count())
                <div class="table-responsive">
                    <table class="table custom-table font-14">
                        <tr>
                            <th class="text-left">{{ trans('panel.user') }}</th>
                            <th class="text-center">{{ trans('panel.role') }}</th>
                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                            <th class="text-center">{{ trans('panel.requested_at') }}</th>
                        </tr>

                        @foreach($roleRequests as $userRole)
                            <tr>
                                <td class="text-left">{{ $userRole->user->full_name ?? '-' }}</td>
                                <td class="text-center">{{ $userRole->roleCatalog->label ?? '-' }}</td>
                                <td class="text-center">
                                    @if($userRole->status === \App\Models\UserRoleRequest::STATUS_ACTIVE)
                                        <span class="badge badge-success">{{ trans('panel.active') }}</span>
                                    @elseif($userRole->status === \App\Models\UserRoleRequest::STATUS_PENDING)
                                        <span class="badge badge-warning">{{ trans('panel.pending') }}</span>
                                    @elseif($userRole->status === \App\Models\UserRoleRequest::STATUS_REJECTED)
                                        <span class="badge badge-danger">{{ trans('panel.rejected') }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ ucfirst($userRole->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    {{ optional($userRole->requested_at ?? $userRole->created_at)->format('Y-m-d H:i') }}
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                {{ $roleRequests->links() }}
            @else
                <div class="text-center text-gray-500 mt-30">
                    {{ trans('panel.no_roles_yet') }}
                </div>
            @endif

        </div>

        {{-- CREATE NEW --}}
        <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="newRole" role="tabpanel">
            <div class="row">
                <div class="col-12 col-md-6">
                    <form action="{{ route('panel.roles.request') }}" method="post">
                        @csrf

                        <div class="form-group">
                            <label class="input-label">{{ trans('panel.user') }}</label>
                            <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror">
                                <option value="">{{ trans('public.select') }}</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}" {{ (string) old('user_id') === (string) $u->id ? 'selected' : '' }}>
                                        {{ $u->full_name }} ({{ $u->email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('user_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label class="input-label">{{ trans('panel.role') }}</label>
                            <select name="role_catalog_id" class="form-control select2 @error('role_catalog_id') is-invalid @enderror">
                                <option value="">{{ trans('public.select') }}</option>
                                @foreach($roleCatalogs as $role)
                                    <option value="{{ $role->id }}" {{ (string) old('role_catalog_id') === (string) $role->id ? 'selected' : '' }}>
                                        [{{ ucfirst($role->family) }}] {{ $role->label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role_catalog_id')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <input type="hidden" name="status" value="pending">

                        <div class="text-right col-12 mt-3 px-0">
                            <button type="submit" class="btn btn-primary">
                                {{ trans('public.save') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
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