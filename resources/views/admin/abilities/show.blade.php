@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $ability->name }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">
                <a href="{{ getAdminPanelUrl() }}/abilities">{{ trans('admin/main.abilities') ?? 'Abilities' }}</a>
            </div>
            <div class="breadcrumb-item">{{ $ability->name }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <span class="badge badge-info text-capitalize mr-2">{{ $ability->type }}</span>
                                @if($ability->is_active)
                                    <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                @else
                                    <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
                                @endif
                                <span class="text-gray-500 font-13 ml-2">
                                    <code>{{ $ability->key }}</code> &middot; <code>{{ $ability->driver_class }}</code>
                                </span>
                            </div>

                            @can('admin_abilities_edit')
                                <a href="{{ getAdminPanelUrl() }}/abilities/{{ $ability->id }}/edit" class="btn btn-sm btn-outline-primary">
                                    {{ trans('admin/main.edit') }}
                                </a>
                            @endcan
                        </div>

                        @if($ability->description)
                            <p class="text-gray-500 mb-4">{{ $ability->description }}</p>
                        @endif

                        <h5 class="mb-3">Vendor Assignments</h5>

                        @if($vendorAbilities->count())
                            <div class="table-responsive">
                                <table class="table custom-table font-14">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th class="text-left">Vendor</th>
                                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                                            <th class="text-center">Sync Status</th>
                                            <th class="text-center">Last Synced</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($vendorAbilities as $va)
                                            <tr>
                                                <td>{{ $va->id }}</td>
                                                <td class="text-left">
                                                    {{ $va->vendor->full_name ?? ('#' . $va->vendor_id) }}
                                                    <div class="text-gray-500 font-12">{{ $va->vendor->email ?? '' }}</div>
                                                </td>
                                                <td class="text-center">
                                                    @if($va->enabled)
                                                        <span class="badge badge-success">Enabled</span>
                                                    @else
                                                        <span class="badge badge-secondary">Disabled</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @php
                                                        $statusColors = [
                                                            'idle'     => 'secondary',
                                                            'syncing'  => 'warning',
                                                            'success'  => 'success',
                                                            'failed'   => 'danger',
                                                        ];
                                                        $color = $statusColors[$va->sync_status] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge badge-{{ $color }} text-capitalize">{{ $va->sync_status }}</span>
                                                </td>
                                                <td class="text-center">
                                                    {{ $va->last_synced_at ? $va->last_synced_at->diffForHumans() : '-' }}
                                                </td>
                                                <td class="text-center">
                                                    @can('admin_abilities_edit')
                                                        <form action="{{ getAdminPanelUrl() }}/abilities/{{ $ability->id }}/vendor/{{ $va->id }}/toggle"
                                                              method="POST" class="d-inline">
                                                            @csrf
                                                            <input type="hidden" name="enabled" value="{{ $va->enabled ? '0' : '1' }}">
                                                            <button type="submit"
                                                                    class="btn btn-sm {{ $va->enabled ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                                {{ $va->enabled ? 'Disable' : 'Enable' }}
                                                            </button>
                                                        </form>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-3">{{ $vendorAbilities->links() }}</div>
                        @else
                            <div class="text-center text-gray-500 mt-30">
                                {{ trans('admin/main.no_result') }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection