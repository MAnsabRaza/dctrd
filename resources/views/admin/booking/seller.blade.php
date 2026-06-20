@extends('admin.layouts.app')

@push('libraries_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle ?? trans('update.booking_sellers') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle ?? trans('update.booking_sellers') }}</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Filters --}}
            <section class="card">
                <div class="card-body">
                    <form method="get" class="mb-0">
                        <div class="row">

                            {{-- Search --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.search') }}</label>
                                    <input name="full_name" type="text" class="form-control"
                                           value="{{ request()->get('full_name') }}">
                                </div>
                            </div>

                            {{-- Role --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.role') }}</label>
                                    <select name="role_name" class="form-control">
                                        <option value="">{{ trans('public.all') }}</option>
                                        <option value="{{ \App\Models\Role::$teacher }}"
                                            @if(request()->get('role_name') == \App\Models\Role::$teacher) selected @endif>
                                            {{ trans('home.instructors') }}
                                        </option>
                                        <option value="{{ \App\Models\Role::$organization }}"
                                            @if(request()->get('role_name') == \App\Models\Role::$organization) selected @endif>
                                            {{ trans('home.organizations') }}
                                        </option>
                                        <option value="{{ \App\Models\Role::$admin }}"
                                            @if(request()->get('role_name') == \App\Models\Role::$admin) selected @endif>
                                            {{ trans('admin/main.admin') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            {{-- User Group --}}
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.users_group') }}</label>
                                    <select name="group_id" data-plugin-selectTwo class="form-control populate">
                                        <option value="">{{ trans('admin/main.select_users_group') }}</option>
                                        @foreach($userGroups as $userGroup)
                                            <option value="{{ $userGroup->id }}"
                                                @if(request()->get('group_id') == $userGroup->id) selected @endif>
                                                {{ $userGroup->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-md-3 d-flex align-items-center">
                                <button type="submit" class="btn btn-primary btn-block btn-lg">
                                    {{ trans('admin/main.show_results') }}
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </section>

            {{-- Table --}}
            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table custom-table font-14">
                                    <tr>
                                        <th>{{ trans('admin/main.id') }}</th>
                                        <th class="text-left">{{ trans('admin/main.name') }}</th>

                                        {{-- Dynamic parent category columns --}}
                                        @foreach($parentCategories as $parent)
                                            <th class="text-center">{{ $parent->title }}</th>
                                        @endforeach

                                        <th class="text-center">{{ trans('update.total_bookings') }}</th>
                                        <th width="120">{{ trans('admin/main.actions') }}</th>
                                    </tr>

                                    @foreach($users as $user)
                                        <tr>
                                            <td>{{ $user->id }}</td>

                                            {{-- Name + Avatar --}}
                                            <td class="text-left">
                                                <div class="d-flex align-items-center">
                                                    <figure class="avatar mr-2">
                                                        <img src="{{ $user->getAvatar() }}" alt="{{ $user->full_name }}">
                                                    </figure>
                                                    <div class="media-body ml-1">
                                                        <div class="mt-0 mb-1 text-dark font-weight-bold">
                                                            {{ $user->full_name }}
                                                        </div>
                                                        @if($user->mobile)
                                                            <div class="text-gray-500 text-small">{{ $user->mobile }}</div>
                                                        @endif
                                                        @if($user->email)
                                                            <div class="text-gray-500 text-small">{{ $user->email }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            {{-- Per-parent-category booking counts --}}
                                            @foreach($parentCategories as $parent)
                                                <td class="text-center">
                                                    <span class="d-block font-14">
                                                        {{ $user->category_booking_counts[$parent->id] ?? 0 }}
                                                    </span>
                                                </td>
                                            @endforeach

                                            {{-- Total bookings --}}
                                            <td class="text-center">
                                                <span class="d-block font-14">{{ $user->total_bookings }}</span>
                                            </td>

                                            {{-- Actions dropdown --}}
                                            <td>
                                                <div class="btn-group dropdown table-actions position-relative">
                                                    <button type="button" class="btn-transparent dropdown-toggle"
                                                            data-toggle="dropdown">
                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                    </button>
                                                    <div class="dropdown-menu dropdown-menu-right">

                                                        @can('admin_users_impersonate')
                                                            <a href="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/impersonate"
                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                <x-iconsax-lin-user-tag class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.login') }}</span>
                                                            </a>
                                                        @endcan

                                                        @can('admin_users_edit')
                                                            <a href="{{ getAdminPanelUrl('/users/'.$user->id.'/edit') }}"
                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                            </a>
                                                        @endcan

                                                        <a href="{{ getAdminPanelUrl('/booking?seller_ids[]='.$user->id) }}"
                                                           class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                            <x-iconsax-lin-calendar class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                            <span class="text-gray-500 font-14">{{ trans('update.view_bookings') }}</span>
                                                        </a>

                                                        @can('admin_users_delete')
                                                            @include('admin.includes.delete_button', [
                                                                'url'      => getAdminPanelUrl().'/users/'.$user->id.'/delete',
                                                                'btnClass' => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                'btnText'  => trans('admin/main.delete'),
                                                                'btnIcon'  => 'trash',
                                                                'iconType' => 'lin',
                                                                'iconClass'=> 'text-danger mr-2',
                                                            ])
                                                        @endcan

                                                    </div>
                                                </div>
                                            </td>

                                        </tr>
                                    @endforeach

                                </table>
                            </div>
                        </div>

                        <div class="card-footer text-center">
                            {{ $users->appends(request()->input())->links() }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts_bottom')

@endpush