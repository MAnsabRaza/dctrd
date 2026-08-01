@extends('design_1.panel.layouts.panel')

@section('content')
<div class="bg-white p-16 rounded-16 border-gray-200">

    @php
        $createActive = $errors->any() || old('user_id');
    @endphp

    <ul class="nav nav-pills mb-16" id="rolesTab" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ $createActive ? '' : 'active' }}" id="rolesList-tab"
               data-toggle="tab" href="#rolesList" role="tab">
                {{ trans('update.my_roles') ?? 'My Roles' }}
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $createActive ? 'active' : '' }}" id="newRole-tab"
               data-toggle="tab" href="#newRole" role="tab">
                {{ trans('update.add_role') ?? 'Create New' }}
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
                            <th class="text-left">{{ trans('admin/main.user') ?? 'User' }}</th>
                            <th class="text-center">{{ trans('update.role') ?? 'Role' }}</th>
                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                            <th class="text-center">{{ trans('admin/main.date') ?? 'Requested At' }}</th>
                        </tr>

                        @foreach($roleRequests as $userRole)
                            <tr>
                                <td class="text-left">{{ $userRole->user->full_name ?? '-' }}</td>
                                <td class="text-center">{{ $userRole->roleCatalog->label ?? '-' }}</td>
                                <td class="text-center">
                                    @if($userRole->status === 'active')
                                        <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                    @elseif($userRole->status === 'pending')
                                        <span class="badge badge-warning">Pending</span>
                                    @else
                                        <span class="badge badge-danger">{{ ucfirst($userRole->status) }}</span>
                                    @endif
                                </td>
                                <td class="text-center">{{ optional($userRole->created_at)->format('Y-m-d H:i') }}</td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                {{ $roleRequests->links() }}
            @else
                <div class="text-center text-gray-500 mt-30">
                    {{ trans('update.no_roles_yet') ?? 'Abhi koi role nahi hai.' }}
                </div>
            @endif

        </div>

        {{-- CREATE NEW --}}
        <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="newRole" role="tabpanel">
            <div class="row">
                <div class="col-12 col-md-6">
                    <form action="{{ route('panel.roles.request') }}" method="post">
                        @csrf

                        {{-- User dropdown --}}
                        <div class="form-group">
                            <label class="input-label">{{ trans('admin/main.user') ?? 'User' }}</label>
                            <select name="user_id" class="form-control select2 @error('user_id') is-invalid @enderror">
                                <option value="">{{ trans('admin/main.select') }}</option>
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

                        {{-- Role catalog dropdown --}}
                        <div class="form-group">
                            <label class="input-label">{{ trans('update.role') ?? 'Role' }}</label>
                            <select name="role_catalog_id" class="form-control select2 @error('role_catalog_id') is-invalid @enderror">
                                <option value="">{{ trans('admin/main.select') }}</option>
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

                        {{-- Status hidden — always pending on create --}}
                        <input type="hidden" name="status" value="pending">

                        <div class="text-right col-12 mt-3 px-0">
                            <button type="submit" class="btn btn-primary">
                                {{ trans('admin/main.save_change') ?? 'Save' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection