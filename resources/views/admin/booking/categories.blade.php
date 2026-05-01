{{-- resources/views/admin/booking/categories.blade.php --}}

@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.booking_categories') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.booking_categories') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <ul class="nav nav-pills" id="myTab3" role="tablist">
                                @can('admin_booking_categories')
                                    @if(!empty($bookingCategories) && $bookingCategories->count())
                                        <li class="nav-item">
                                            <a class="nav-link {{ (!empty($errors) and $errors->has('title')) ? '' : 'active' }}"
                                               id="categories-tab" data-toggle="tab" href="#categories"
                                               role="tab" aria-controls="categories" aria-selected="true">
                                                {{ trans('admin/main.categories') }}
                                            </a>
                                        </li>
                                    @endif
                                @endcan

                                @can('admin_booking_categories_create')
                                    <li class="nav-item">
                                        <a class="nav-link {{ ((!empty($errors) and $errors->has('title')) or !empty($editCategory) or (empty($bookingCategories) || !$bookingCategories->count())) ? 'active' : '' }}"
                                           id="newCategory-tab" data-toggle="tab" href="#newCategory"
                                           role="tab" aria-controls="newCategory" aria-selected="true">
                                            {{ trans('admin/main.create_booking_category') }}
                                        </a>
                                    </li>
                                @endcan
                            </ul>

                            <div class="tab-content" id="myTabContent2">

                                @can('admin_booking_categories')
                                    @if(!empty($bookingCategories) && $bookingCategories->count())
                                        <div class="tab-pane mt-3 fade {{ (!empty($errors) and $errors->has('title')) ? '' : 'active show' }}"
                                             id="categories" role="tabpanel" aria-labelledby="categories-tab">
                                            <div class="table-responsive">
                                                <table class="table custom-table font-14">
                                                    <tr>
                                                        <th class="text-left">{{ trans('admin/main.title') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.bookings') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>

                                                    @foreach($bookingCategories as $category)
                                                        <tr>
                                                            <td class="text-left">{{ $category->title }}</td>
                                                            <td class="text-center">{{ $category->bookings_count ?? 0 }}</td>
                                                            <td class="text-center">
                                                                @if($category->status)
                                                                    <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                @else
                                                                    <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
                                                                @endif
                                                            </td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>

                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_categories_create')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/categories/{{ $category->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan

                                                                        @can('admin_booking_categories_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'      => getAdminPanelUrl() . '/booking/categories/' . $category->id . '/delete',
                                                                                'btnClass' => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                'btnText'  => trans('admin/main.delete'),
                                                                                'btnIcon'  => 'trash',
                                                                                'iconType' => 'lin',
                                                                                'iconClass'=> 'text-danger mr-2'
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
                                    @endif
                                @endcan

                                @can('admin_booking_categories_create')
                                    <div class="tab-pane mt-3 fade {{ ((!empty($errors) and $errors->has('title')) or !empty($editCategory) or (empty($bookingCategories) || !$bookingCategories->count())) ? 'active show' : '' }}"
                                         id="newCategory" role="tabpanel" aria-labelledby="newCategory-tab">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <form action="{{ getAdminPanelUrl() }}/booking/categories/{{ !empty($editCategory) ? $editCategory->id . '/update' : 'store' }}"
                                                      method="post">
                                                    {{ csrf_field() }}

                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.title') }}</label>
                                                        <input type="text" name="title"
                                                               class="form-control @error('title') is-invalid @enderror"
                                                               value="{{ !empty($editCategory) ? $editCategory->title : old('title') }}"
                                                               placeholder="{{ trans('admin/main.choose_title') }}"/>
                                                        @error('title')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label>{{ trans('update.subtitle') }}</label>
                                                        <input type="text" name="subtitle"
                                                               class="form-control @error('subtitle') is-invalid @enderror"
                                                               value="{{ !empty($editCategory) ? $editCategory->subtitle : old('subtitle') }}"
                                                               placeholder="{{ trans('admin/main.choose_subtitle') }}"/>
                                                        @error('subtitle')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.url') }}</label>
                                                        <input type="text" name="slug"
                                                               class="form-control @error('slug') is-invalid @enderror"
                                                               value="{{ !empty($editCategory) ? $editCategory->slug : old('slug') }}"/>
                                                        <div class="text-gray-500 text-small mt-1">{{ trans('update.category_url_hint') }}</div>
                                                        @error('slug')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.description') }}</label>
                                                        <textarea name="description" rows="4"
                                                                  class="form-control @error('description') is-invalid @enderror"
                                                                  placeholder="{{ trans('admin/main.description') }}">{{ !empty($editCategory) ? $editCategory->description : old('description') }}</textarea>
                                                        @error('description')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                  

                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('admin/main.icon') }}</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="input-group-text admin-file-manager"
                                                                        data-input="icon" data-preview="holder">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" name="icon" id="icon"
                                                                   value="{{ !empty($editCategory) ? $editCategory->icon : old('icon') }}"
                                                                   class="form-control @error('icon') is-invalid @enderror"/>
                                                            <div class="invalid-feedback">@error('icon') {{ $message }} @enderror</div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.order') }}</label>
                                                        <input type="number" name="order" min="0"
                                                               class="form-control @error('order') is-invalid @enderror"
                                                               value="{{ !empty($editCategory) ? $editCategory->order : old('order', 0) }}"/>
                                                        @error('order')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('admin/main.parent_category') }}</label>
                                                        <select name="parent_id" class="form-control">
                                                            <option value="">{{ trans('admin/main.no_parent') }}</option>
                                                            @foreach($allCategories as $cat)
                                                                @if(empty($editCategory) || $editCategory->id !== $cat->id)
                                                                    <option value="{{ $cat->id }}"
                                                                        {{ (!empty($editCategory) && $editCategory->parent_id == $cat->id) ? 'selected' : '' }}>
                                                                        {{ $cat->title }}
                                                                    </option>
                                                                @endif
                                                            @endforeach
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" name="status"
                                                                   class="custom-control-input" id="status"
                                                                   {{ (!empty($editCategory) && $editCategory->status) || empty($editCategory) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status">
                                                                {{ trans('admin/main.active') }}
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="text-right col-12 mt-3">
                                                        @if(!empty($editCategory))
                                                            <a href="{{ getAdminPanelUrl() }}/booking/categories"
                                                               class="btn btn-secondary mr-2">
                                                                {{ trans('admin/main.cancel') }}
                                                            </a>
                                                        @endif
                                                        <button type="submit" class="btn btn-primary">
                                                            {{ trans('admin/main.save_change') }}
                                                        </button>
                                                    </div>

                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endcan

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