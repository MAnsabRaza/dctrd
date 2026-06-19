@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @include('admin.includes.alerts.success')
                            @include('admin.includes.alerts.errors')

                            <ul class="nav nav-pills" id="topCategoryTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ (!$errors->any() and !isset($editItem)) ? 'active' : '' }}"
                                       id="list-tab" data-toggle="tab" href="#list" role="tab" aria-controls="list" aria-selected="true">
                                        {{ trans('admin/main.list') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ ($errors->any() or isset($editItem)) ? 'active' : '' }}"
                                       id="new-tab" data-toggle="tab" href="#new" role="tab" aria-controls="new" aria-selected="true">
                                        {{ trans('admin/main.add_top_category') }}
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" id="topCategoryTabContent">

                                {{-- List Tab --}}
                                <div class="tab-pane mt-3 fade {{ (!$errors->any() and !isset($editItem)) ? 'active show' : '' }}"
                                     id="list" role="tabpanel" aria-labelledby="list-tab">
                                    <div class="table-responsive">
                                        <table class="table custom-table font-14">
                                            <tr>
                                                <th class="text-left">{{ trans('admin/main.category') }}</th>
                                                <th>{{ trans('admin/main.action') }}</th>
                                            </tr>
                                            @forelse($items as $item)
                                                <tr>
                                                    <td class="text-left">
                                                        <div class="d-flex align-items-center">
                                                            <div class="size-32 rounded-sm">
                                                                <img src="{{ $item->image }}" alt="" class="img-cover rounded-16">
                                                            </div>
                                                            <div class="ml-2">
                                                                @if($item->category)
                                                                    {{ $item->category->title }}
                                                                @else
                                                                    <span class="text-danger">{{ trans('admin/main.deleted') }}</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td width="80px">
                                                        <div class="btn-group dropdown table-actions position-relative">
                                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">
                                                                <a href="{{ getAdminPanelUrl() }}/booking/top-categories/{{ $item->id }}/edit"
                                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                    <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                </a>
                                                                @include('admin.includes.delete_button', [
                                                                    'url'       => getAdminPanelUrl().'/booking/top-categories/'.$item->id.'/delete',
                                                                    'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                    'btnText'   => trans('admin/main.delete'),
                                                                    'btnIcon'   => 'trash',
                                                                    'iconType'  => 'lin',
                                                                    'iconClass' => 'text-danger mr-2'
                                                                ])
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="2" class="text-center text-gray-500 py-3">{{ trans('admin/main.no_result') }}</td>
                                                </tr>
                                            @endforelse
                                        </table>
                                        {{ $items->links() }}
                                    </div>
                                </div>

                                {{-- New / Edit Tab --}}
                                <div class="tab-pane mt-3 fade {{ ($errors->any() or isset($editItem)) ? 'active show' : '' }}"
                                     id="new" role="tabpanel" aria-labelledby="new-tab">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form method="post"
                                                  action="{{ isset($editItem) ? getAdminPanelUrl().'/booking/top-categories/'.$editItem->id.'/update' : getAdminPanelUrl().'/booking/top-categories/store' }}"
                                                  enctype="multipart/form-data">
                                                @csrf

                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.category') }}</label>
                                                    <select name="category_id" class="form-control @error('category_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select_category') }}</option>
                                                        @foreach($categories as $id => $title)
                                                            <option value="{{ $id }}"
                                                                {{ ((isset($editItem) and $editItem->category_id == $id) or old('category_id') == $id) ? 'selected' : '' }}>
                                                                {{ $title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('category_id')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label class="input-label">{{ trans('public.thumbnail_image') }}</label>
                                                    <div class="input-group">
                                                        <div class="input-group-prepend">
                                                            <button type="button" class="input-group-text admin-file-manager" data-input="top_image" data-preview="holder">
                                                                <i class="fa fa-upload"></i>
                                                            </button>
                                                        </div>
                                                        <input type="text" name="image" id="top_image"
                                                               value="{{ isset($editItem) ? $editItem->image : old('image') }}"
                                                               class="form-control @error('image') is-invalid @enderror"/>
                                                        <div class="invalid-feedback">@error('image') {{ $message }} @enderror</div>
                                                    </div>
                                                </div>

                                                <div class="text-right mt-4">
                                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                                    @if(isset($editItem))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/top-categories" class="btn btn-secondary ml-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
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
    <script src="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
@endpush