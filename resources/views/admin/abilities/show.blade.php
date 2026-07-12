@extends('admin.layouts.app')

@php
    // trans() missing key par khud wahi key return karta hai (null nahi),
    // isliye "?? 'Abilities'" fallback kaam nahi karta — manually check karo
    $abilitiesLabel = trans('admin/main.abilities');
    $abilitiesLabel = ($abilitiesLabel === 'admin/main.abilities') ? 'Abilities' : $abilitiesLabel;
@endphp

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $ability->name }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">
                <a href="{{ getAdminPanelUrl() }}/abilities">{{ $abilitiesLabel }}</a>
            </div>
            <div class="breadcrumb-item">{{ $ability->name }}</div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">

                        {{-- ═══════════════════════════════════════════════
                             Scoped styles — global .badge/.badge-info/.badge-success
                             theme mein kahin block-level define hain, isliye yahan
                             independent classes use ki hain taake conflict na ho
                        ═══════════════════════════════════════════════ --}}
                        <style>
                            .ability-header-row {
                                display: flex;
                                align-items: center;
                                justify-content: space-between;
                                flex-wrap: wrap;
                                gap: 12px;
                                margin-bottom: 16px;
                            }
                            .ability-pill {
                                display: inline-flex;
                                align-items: center;
                                width: auto;
                                white-space: nowrap;
                                font-size: 12.5px;
                                font-weight: 600;
                                padding: 4px 12px;
                                border-radius: 999px;
                                text-transform: capitalize;
                                line-height: 1.4;
                            }
                            .ability-pill-type      { background: #eef2ff; color: #4338ca; }
                            .ability-pill-active    { background: #ecfdf3; color: #16a34a; }
                            .ability-pill-inactive  { background: #fef2f2; color: #dc2626; }
                            .ability-meta {
                                font-size: 12.5px;
                                color: #64748b;
                            }
                            .ability-meta code {
                                background: #f1f5f9;
                                color: #334155;
                                padding: 2px 6px;
                                border-radius: 5px;
                                font-size: 11.5px;
                            }
                            .ability-status-badge {
                                display: inline-flex;
                                align-items: center;
                                width: auto;
                                white-space: nowrap;
                                font-size: 12.5px;
                                font-weight: 600;
                                padding: 3px 10px;
                                border-radius: 999px;
                            }
                            .ability-status-enabled   { background: #ecfdf3; color: #16a34a; }
                            .ability-status-disabled  { background: #f1f5f9; color: #64748b; }
                            .ability-status-idle      { background: #f1f5f9; color: #64748b; }
                            .ability-status-syncing   { background: #fffbeb; color: #b45309; }
                            .ability-status-success   { background: #ecfdf3; color: #16a34a; }
                            .ability-status-failed    { background: #fef2f2; color: #dc2626; }
                        </style>

                        <div class="ability-header-row">
                            <div class="d-flex align-items-center flex-wrap" style="gap:8px;">
                                <span class="ability-pill ability-pill-type">{{ $ability->type }}</span>

                                @if($ability->is_active)
                                    <span class="ability-pill ability-pill-active">{{ trans('admin/main.active') }}</span>
                                @else
                                    <span class="ability-pill ability-pill-inactive">{{ trans('admin/main.inactive') }}</span>
                                @endif

                                <span class="ability-meta">
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
                                                        <span class="ability-status-badge ability-status-enabled">Enabled</span>
                                                    @else
                                                        <span class="ability-status-badge ability-status-disabled">Disabled</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="ability-status-badge ability-status-{{ $va->sync_status }}">
                                                        {{ $va->sync_status }}
                                                    </span>
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