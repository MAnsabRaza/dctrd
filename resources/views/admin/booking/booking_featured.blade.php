@extends('admin.layouts.app')

@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css" rel="stylesheet"/>
@endpush

@section('content')
@php $activeTab = $activeTab ?? ((!empty($editItem) || old('title') || old('booking_id') || old('page')) ? 'new' : 'list'); @endphp
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle ?? trans('admin/main.booking_featured') }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <section class="card mb-4">
                        <div class="card-body">
                            <form action="{{ getAdminPanelUrl('/booking/featured') }}" method="get" class="row align-items-end">
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.page') }}</label>
                                        <select name="page" class="form-control">
                                            <option value="">{{ trans('admin/main.select_page') }}</option>
                                            <option value="home" {{ request()->get('page') == 'home' ? 'selected' : '' }}>{{ trans('admin/main.home') }}</option>
                                            <option value="home_categories" {{ request()->get('page') == 'home_categories' ? 'selected' : '' }}>{{ trans('admin/main.home_categories') }}</option>
                                            <option value="categories" {{ request()->get('page') == 'categories' ? 'selected' : '' }}>{{ trans('admin/main.categories') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-md-2">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.status') }}</label>
                                        <select name="status" class="form-control">
                                            <option value="">{{ trans('admin/main.status') }}</option>
                                            <option value="active" {{ request()->get('status') == 'active' ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                                            <option value="inactive" {{ request()->get('status') == 'inactive' ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.booking_title') }}</label>
                                        <input type="text" name="booking_title" value="{{ request()->get('booking_title') }}" class="form-control" placeholder="{{ trans('admin/main.search') }}" />
                                    </div>
                                </div>

                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label class="input-label">{{ trans('public.category') }}</label>
                                        <select name="category_id" class="form-control">
                                            <option value="">{{ trans('admin/main.all') }}</option>
                                            @foreach($categories ?? [] as $category)
                                                @if(!empty($category->subCategories) && count($category->subCategories))
                                                    <optgroup label="{{ $category->title }}">
                                                        @foreach($category->subCategories as $subCategory)
                                                            <option value="{{ $subCategory->id }}" {{ request()->get('category_id') == $subCategory->id ? 'selected' : '' }}>{{ $subCategory->title }}</option>
                                                        @endforeach
                                                    </optgroup>
                                                @else
                                                    <option value="{{ $category->id }}" {{ request()->get('category_id') == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 col-md-1">
                                    <button type="submit" class="btn btn-primary btn-block">{{ trans('admin/main.show_results') }}</button>
                                </div>
                            </form>
                        </div>
                    </section>

                    <div class="card">
                        <div class="card-header justify-content-between">
                            <div>
                                <h5 class="font-14 mb-0">{{ $pageTitle ?? trans('admin/main.booking_featured') }}</h5>
                                <p class="font-12 mt-2 mb-0 text-gray-500">{{ trans('update.manage_all_items_in_a_single_place') }}</p>
                            </div>
                            @can('admin_booking_featured_create')
                                <a href="{{ getAdminPanelUrl('/booking/featured/create') }}" class="btn btn-primary">{{ trans('admin/main.add_new') }}</a>
                            @endcan
                        </div>
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
                                <div id="listTab" class="tab-pane {{ $activeTab === 'list' ? 'active' : '' }}">
                                    <div class="table-responsive">
                                        <table class="table custom-table">
                                            <thead>
                                                <tr>
                                                    <th>{{ trans('admin/main.title') }}</th>
                                                    <th>{{ trans('admin/main.booking') }}</th>
                                                    <th>{{ trans('admin/main.user') }}</th>
                                                    <th>{{ trans('admin/main.page') }}</th>
                                                    <th>{{ trans('admin/main.status') }}</th>
                                                    <th>{{ trans('admin/main.action') }}</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($items as $item)
                                                    <tr>
                                                        <td>{{ $item->title }}</td>
                                                        <td>{{ optional($item->booking)->title ?? '-' }}</td>
                                                        <td>{{ optional($item->user)->full_name ?? '-' }}</td>
                                                        <td>{{ $item->page }}</td>
                                                        <td>{{ $item->status ? trans('admin/main.active') : trans('admin/main.inactive') }}</td>
                                                        <td>
                                                            <a href="{{ getAdminPanelUrl('/booking/featured/'.$item->id.'/edit') }}" class="btn btn-sm btn-primary">{{ trans('admin/main.edit') }}</a>
                                                            <a href="{{ getAdminPanelUrl('/booking/featured/'.$item->id.'/delete') }}" class="btn btn-sm btn-danger">{{ trans('admin/main.delete') }}</a>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="6" class="text-center text-gray-500">{{ trans('admin/main.no_result') }}</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>

                                        {{ $items->links() }}
                                    </div>
                                </div>

                                <div id="newTab" class="tab-pane {{ $activeTab === 'new' ? 'active' : '' }}">
                                    <div class="row">
                                        <div class="col-12 col-md-8 col-lg-6">
                                            <form action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/featured/'.$editItem->id.'/update') : getAdminPanelUrl('/booking/featured/store') }}" method="post">
                                                {{ csrf_field() }}

                                                @if(!empty(getGeneralSettings('content_translate')))
                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('auth.language') }}</label>
                                                        <select name="language" class="form-control {{ !empty($editItem) ? 'js-edit-content-locale' : '' }}">
                                                            @foreach($userLanguages as $lang => $language)
                                                                <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                @else
                                                    <input type="hidden" name="language" value="{{ getDefaultLocale() }}">
                                                @endif

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.booking') }}</label>
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror" required>
                                                        <option disabled selected>{{ trans('admin/main.choose') }}</option>
                                                        @foreach($bookings ?? [] as $id => $title)
                                                            <option value="{{ $id }}" @if(!empty($editItem) && $editItem->booking_id == $id) selected @endif>{{ $title }}</option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.title') }}</label>
                                                    <input type="text" name="title" class="form-control" value="{{ old('title', $editItem->title ?? '') }}" required />
                                                </div>

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.page') }}</label>
                                                    <select name="page" class="form-control">
                                                        <option value="home" @if((old('page', $editItem->page ?? '')) == 'home') selected @endif>{{ trans('admin/main.home') }}</option>
                                                        <option value="home_categories" @if((old('page', $editItem->page ?? '')) == 'home_categories') selected @endif>{{ trans('admin/main.home_categories') }}</option>
                                                        <option value="categories" @if((old('page', $editItem->page ?? '')) == 'categories') selected @endif>{{ trans('admin/main.categories') }}</option>
                                                    </select>
                                                </div>

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('admin/main.description') }}</label>
                                                    <textarea name="description" class="form-control">{{ old('description', $editItem->description ?? '') }}</textarea>
                                                </div>

                                                <div class="form-group d-flex align-items-center">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="status" id="featuredStatus" class="custom-control-input" @if(old('status', $editItem->status ?? false)) checked @endif>
                                                        <label class="custom-control-label" for="featuredStatus"></label>
                                                    </div>
                                                    <label class="mb-0 ml-2">{{ trans('admin/main.active') }}</label>
                                                </div>

                                                <div class="text-right mt-4">
                                                    <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
                                                </div>
                                            </form>
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
@endpush
