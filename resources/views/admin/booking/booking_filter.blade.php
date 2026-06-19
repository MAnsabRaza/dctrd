@extends('admin.layouts.app')

@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css" rel="stylesheet"/>
@endpush

@section('content')
@php $activeTab = !empty($editItem) ? 'new' : 'list'; @endphp
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle ?? trans('admin/main.booking_filters') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl('/booking/filters') }}">{{ trans('admin/main.booking_filters') }}</a></div>
                @if(!empty($editItem))
                    <div class="breadcrumb-item">{{ trans('admin/main.edit') }}</div>
                @endif
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'list' ? 'active' : '' }}" data-toggle="tab" href="#listTab">{{ trans('admin/main.list') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $activeTab === 'new' ? 'active' : '' }}" data-toggle="tab" href="#newTab">{{ !empty($editItem) ? trans('admin/main.edit') : trans('admin/main.new') }}</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <!-- LIST TAB -->
                                <div id="listTab" class="tab-pane {{ $activeTab === 'list' ? 'active' : '' }}">
                                    <div class="table-responsive">
                                        <table class="table custom-table">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('admin/main.title') }}</th>
                                                <th>{{ trans('admin/main.category') }}</th>
                                                <th>{{ trans('admin/main.options') }}</th>
                                                <th>{{ trans('admin/main.status') }}</th>
                                                <th>{{ trans('admin/main.action') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @forelse($filters as $filter)
                                                <tr>
                                                    <td>{{ $filter->title }}</td>
                                                    <td>{{ optional($filter->category)->title ?? '-' }}</td>
                                                    <td>
                                                        @forelse($filter->options as $opt)
                                                            <span class="badge badge-light mr-1">{{ $opt->name }}</span>
                                                        @empty
                                                            -
                                                        @endforelse
                                                    </td>
                                                    <td>
                                                        <span class="{{ $filter->status ? 'badge-status text-success bg-success-30' : 'badge-status text-danger bg-danger-30' }}">
                                                            {{ $filter->status ? trans('admin/main.active') : trans('admin/main.inactive') }}
                                                        </span>
                                                    </td>
                                                    <td width="80px">
                                                        <div class="btn-group dropdown table-actions position-relative">
                                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                <i class="fa fa-ellipsis-v text-gray-500"></i>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                @can('admin_booking_filters_edit')
                                                                    {{-- Full page reload to edit($id). Controller already passes
                                                                         $filters + $editItem + $filterOptions, so both tabs render
                                                                         correctly and the "Edit" tab auto-activates. --}}
                                                                    <a href="{{ getAdminPanelUrl('/booking/filters/'.$filter->id.'/edit') }}" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                        <i class="fa fa-edit text-gray-500 mr-2"></i>
                                                                        <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                    </a>
                                                                @endcan
                                                                @can('admin_booking_filters_delete')
                                                                    <a href="{{ getAdminPanelUrl('/booking/filters/'.$filter->id.'/delete') }}" class="dropdown-item text-danger mb-0 py-3 px-0 font-14" onclick="return confirm('{{ trans('admin/main.delete_confirm_msg') }}');">
                                                                        <i class="fa fa-trash text-danger mr-2"></i>
                                                                        <span>{{ trans('admin/main.delete') }}</span>
                                                                    </a>
                                                                @endcan
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center text-gray-500">{{ trans('admin/main.no_result') }}</td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>

                                        {{ $filters->links() }}
                                    </div>
                                </div>

                                <!-- NEW / EDIT TAB (styled like the product filter form) -->
                                <div id="newTab" class="tab-pane {{ $activeTab === 'new' ? 'active' : '' }}">
                                    <div class="row">
                                        <div class="col-12 col-md-8 col-lg-6">
                                            <form action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/filters/'.$editItem->id.'/update') : getAdminPanelUrl('/booking/filters/store') }}" method="post">
                                                {{ csrf_field() }}

                                                @if(!empty(getGeneralSettings('content_translate')))
                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('auth.language') }}</label>
                                                        <select name="language" class="form-control @error('language') is-invalid @enderror {{ !empty($editItem) ? 'js-edit-content-locale' : '' }}">
                                                            @foreach($userLanguages ?? [] as $lang => $language)
                                                                <option value="{{ $lang }}" {{ old('language', $editItem->language ?? app()->getLocale()) == $lang ? 'selected' : '' }}>{{ $language }}</option>
                                                            @endforeach
                                                        </select>
                                                        @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                    </div>
                                                @else
                                                    <input type="hidden" name="language" value="{{ getDefaultLocale() }}">
                                                @endif

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.category') }} <span class="text-danger">*</span></label>
                                                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                                        <option value="" {{ empty($editItem) ? 'selected' : '' }} disabled>{{ trans('admin/main.choose_category') }}</option>

                                                        @foreach($categories as $category)
                                                            @if(!empty($category->subCategories) && count($category->subCategories))
                                                                <optgroup label="{{ $category->title }}">
                                                                    @foreach($category->subCategories as $subCategory)
                                                                        <option value="{{ $subCategory->id }}" {{ old('category_id', $editItem->category_id ?? '') == $subCategory->id ? 'selected' : '' }}>{{ $subCategory->title }}</option>
                                                                    @endforeach
                                                                </optgroup>
                                                            @else
                                                                <option value="{{ $category->id }}" class="font-weight-bold" {{ old('category_id', $editItem->category_id ?? '') == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.title') }} <span class="text-danger">*</span></label>
                                                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $editItem->title ?? '') }}" placeholder="{{ trans('admin/main.choose_title') }}" required/>
                                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="form-group d-flex align-items-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="status" id="filterStatusSwitch" value="1" class="custom-control-input" {{ old('status', $editItem->status ?? true) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="filterStatusSwitch"></label>
                                                    </div>
                                                    <label for="filterStatusSwitch" class="mb-0 ml-2">{{ trans('admin/main.active') }}</label>
                                                </div>

                                                <div id="filterOptions" class="ml-1">
                                                    <div class="d-flex align-items-center justify-content-between mb-4">
                                                        <strong class="d-block">{{ trans('admin/main.add_options') }}</strong>
                                                        <button type="button" class="btn btn-success add-btn"><i class="fa fa-plus"></i> {{ trans('admin/main.add') }}</button>
                                                    </div>

                                                    <ul class="draggable-lists list-group">
                                                        @if(!empty($filterOptions))
                                                            @foreach($filterOptions as $filterOption)
                                                                <li class="form-group list-group">
                                                                    <div class="input-group">
                                                                        <div class="input-group-prepend">
                                                                            <div class="input-group-text cursor-pointer move-icon">
                                                                                <i class="fa fa-arrows-alt"></i>
                                                                            </div>
                                                                        </div>

                                                                        <input type="text" name="sub_filters[{{ $filterOption->id }}][name]" class="form-control w-auto flex-grow-1" value="{{ $filterOption->name }}" placeholder="{{ trans('admin/main.choose_title') }}"/>

                                                                        <div class="input-group-append">
                                                                            <button type="button" class="btn remove-btn btn-danger"><i class="fa fa-times"></i></button>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </ul>
                                                </div>

                                                <div class="mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                    @if(!empty($editItem))
                                                        <a href="{{ getAdminPanelUrl('/booking/filters') }}" class="btn btn-secondary ml-2">{{ trans('admin/main.cancel') }}</a>
                                                    @endif
                                                </div>
                                            </form>

                                            <li class="form-group main-row list-group d-none">
                                                <div class="input-group">
                                                    <div class="input-group-prepend">
                                                        <div class="input-group-text cursor-pointer move-icon">
                                                            <i class="fa fa-arrows-alt"></i>
                                                        </div>
                                                    </div>

                                                    <input type="text" name="sub_filters[record][name]" class="form-control w-auto flex-grow-1" placeholder="{{ trans('admin/main.choose_title') }}"/>

                                                    <div class="input-group-append">
                                                        <button type="button" class="btn remove-btn btn-danger"><i class="fa fa-times"></i></button>
                                                    </div>
                                                </div>
                                            </li>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/admin/js/parts/filters.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (e) {
                if (e.target.closest('.add-btn')) {
                    const mainRow = document.querySelector('.main-row');
                    const list = document.querySelector('.draggable-lists');
                    if (!mainRow || !list) return;
                    const clone = mainRow.cloneNode(true);
                    clone.classList.remove('d-none', 'main-row');
                    clone.querySelectorAll('input').forEach(i => i.value = '');
                    list.appendChild(clone);
                }

                if (e.target.closest('.remove-btn')) {
                    const row = e.target.closest('.list-group');
                    if (row && !row.classList.contains('main-row')) row.remove();
                }
            });
        });
    </script>
@endpush