@extends('admin.layouts.app')

@push('styles_top')
    <link href="/assets/default/vendors/sortable/jquery-ui.min.css" rel="stylesheet"/>
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle ?? trans('admin/main.booking_filters') }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#listTab">{{ trans('admin/main.list') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#newTab">{{ trans('admin/main.new') }}</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <div id="listTab" class="tab-pane active">
                                    <div class="table-responsive">
                                        <table class="table custom-table">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('admin/main.title') }}</th>
                                                <th>{{ trans('admin/main.category') }}</th>
                                                <th>{{ trans('admin/main.options') }}</th>
                                                <th>{{ trans('admin/main.action') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($filters as $filter)
                                                <tr>
                                                    <td>{{ $filter->title }}</td>
                                                    <td>{{ optional($filter->category)->title }}</td>
                                                    <td>
                                                        @foreach($filter->options as $opt)
                                                            <div>{{ $opt->name }}</div>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a href="{{ getAdminPanelUrl('/booking/filters/'.$filter->id.'/edit') }}" class="btn btn-sm btn-primary">{{ trans('admin/main.edit') }}</a>
                                                        <a href="{{ getAdminPanelUrl('/booking/filters/'.$filter->id.'/delete') }}" class="btn btn-sm btn-danger">{{ trans('admin/main.delete') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>

                                        {{ $filters->links() }}
                                    </div>
                                </div>

                                <div id="newTab" class="tab-pane">
                                    <form action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/filters/'.$editItem->id.'/update') : getAdminPanelUrl('/booking/filters/store') }}" method="post">
                                        {{ csrf_field() }}

                                        @if(!empty(getGeneralSettings('content_translate')))
                                            <div class="form-group">
                                                <label class="input-label">{{ trans('auth.language') }}</label>
                                                <select name="language" class="form-control {{ !empty($editItem) ? 'js-edit-content-locale' : '' }}">
                                                    @foreach($userLanguages as $lang => $language)
                                                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                                @error('language')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @else
                                            <input type="hidden" name="language" value="{{ getDefaultLocale() }}">
                                        @endif

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.category') }}</label>
                                            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                                <option {{ !empty($trend) ? '' : 'selected' }} disabled>{{ trans('admin/main.choose_category') }}</option>

                                                @foreach($categories as $category)
                                                    @if(!empty($category->subCategories) and count($category->subCategories))
                                                        <optgroup label="{{  $category->title }}">
                                                            @foreach($category->subCategories as $subCategory)
                                                                <option value="{{ $subCategory->id }}" @if(!empty($editItem) and $editItem->category_id == $subCategory->id) selected="selected" @endif>{{ $subCategory->title }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @else
                                                        <option value="{{ $category->id }}" class="font-weight-bold" @if(!empty($editItem) and $editItem->category_id == $category->id) selected="selected" @endif>{{ $category->title }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="title" class="form-control  @error('title') is-invalid @enderror" value="{{ !empty($editItem) ? $editItem->title : old('title') }}" placeholder="{{ trans('admin/main.choose_title') }}"/>
                                            @error('title')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div id="filterOptions" class="ml-1">
                                            <div class="d-flex align-items-center justify-content-between mb-4">
                                                <strong class="d-block">{{ trans('admin/main.add_options') }}</strong>

                                                <button type="button" class="btn btn-success add-btn "><i class="fa fa-plus"></i> {{ trans('admin/main.add') }}</button>
                                            </div>

                                            <ul class="draggable-lists list-group">
                                                @if(!empty($filterOptions))
                                                    @foreach($filterOptions as $key => $filterOption)

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

                                        <div class="text-right mt-4">
                                            <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
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
    </section>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/admin/js/parts/filters.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // init add/remove buttons for the draggable list
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
