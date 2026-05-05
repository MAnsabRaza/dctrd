{{-- resources/views/admin/booking/resources.blade.php --}}

@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.booking_resources') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.booking_resources') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            <ul class="nav nav-pills" id="myTab3" role="tablist">
                                @php
                                    $resourceCreateActive = (
                                        (!empty($errors) && $errors->has('name')) ||
                                        !empty($editResource) ||
                                        ((empty($bookingResources) || !$bookingResources->count()) && auth()->user()->can('admin_booking_resources_create'))
                                    );
                                @endphp

                                @can('admin_booking_resources')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $resourceCreateActive ? '' : 'active' }}"
                                           id="resources-tab" data-toggle="tab" href="#resources"
                                           role="tab" aria-controls="resources" aria-selected="true">
                                            {{ trans('admin/main.booking_resources') }}
                                        </a>
                                    </li>
                                @endcan

                                @can('admin_booking_resources_create')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $resourceCreateActive ? 'active' : '' }}"
                                           id="newResource-tab" data-toggle="tab" href="#newResource"
                                           role="tab" aria-controls="newResource" aria-selected="true">
                                            {{ trans('admin/main.create_booking_resource') }}
                                        </a>
                                    </li>
                                @endcan
                            </ul>

                            <div class="tab-content" id="myTabContent2">

                                @can('admin_booking_resources')
                                    <div class="tab-pane mt-3 fade {{ $resourceCreateActive ? '' : 'active show' }}"
                                         id="resources" role="tabpanel" aria-labelledby="resources-tab">

                                        @if(!empty($bookingResources) && $bookingResources->count())
                                            <div class="table-responsive">
                                                <table class="table custom-table font-14">
                                                    <tr>
                                                        <th class="text-left">{{ trans('admin/main.name') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.type') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.capacity') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.extra_price') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>

                                                    @foreach($bookingResources as $resource)
                                                        <tr>
                                                            <td class="text-left">
                                                                @if($resource->image)
                                                                    <img src="{{ $resource->image }}" alt="{{ $resource->name }}" width="40" height="40" class="rounded mr-2" style="object-fit:cover;">
                                                                @endif
                                                                {{ $resource->name }}
                                                            </td>
                                                            <td class="text-center">{{ $resource->type ?? '-' }}</td>
                                                            <td class="text-center">{{ $resource->capacity ?? '-' }}</td>
                                                            <td class="text-center">{{ $resource->extra_price ?? 0 }}</td>
                                                            <td class="text-center">
                                                                @if($resource->status)
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
                                                                        @can('admin_booking_resources_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/resources/{{ $resource->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan

                                                                        @can('admin_booking_resources_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/resources/' . $resource->id . '/delete',
                                                                                'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                'btnText'   => trans('admin/main.delete'),
                                                                                'btnIcon'   => 'trash',
                                                                                'iconType'  => 'lin',
                                                                                'iconClass' => 'text-danger mr-2'
                                                                            ])
                                                                        @endcan
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </table>
                                            </div>

                                            {{ $bookingResources->links() }}

                                        @else
                                            <div class="text-center text-gray-500 mt-30">
                                                {{ trans('admin/main.no_result') }}
                                            </div>
                                        @endif

                                    </div>
                                @endcan

                                @can('admin_booking_resources_create')
                                    <div class="tab-pane mt-3 fade {{ $resourceCreateActive ? 'active show' : '' }}"
                                         id="newResource" role="tabpanel" aria-labelledby="newResource-tab">
                                        <div class="row">
                                            <div class="col-12 col-md-6">
                                                <form action="{{ getAdminPanelUrl() }}/booking/resources/{{ !empty($editResource) ? $editResource->id . '/update' : 'store' }}"
                                                      method="post">
                                                    {{ csrf_field() }}

                                                    {{-- Name --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.name') }}</label>
                                                        <input type="text" name="name"
                                                               class="form-control @error('name') is-invalid @enderror"
                                                               value="{{ !empty($editResource) ? $editResource->name : old('name') }}"
                                                               placeholder="{{ trans('admin/main.choose_name') }}"/>
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Type --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.type') }}</label>
                                                        <input type="text" name="type"
                                                               class="form-control @error('type') is-invalid @enderror"
                                                               value="{{ !empty($editResource) ? $editResource->type : old('type') }}"
                                                               placeholder="{{ trans('admin/main.type') }}"/>
                                                        @error('type')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Description --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.description') }}</label>
                                                        <textarea name="description" rows="4"
                                                                  class="form-control @error('description') is-invalid @enderror"
                                                                  placeholder="{{ trans('admin/main.description') }}">{{ !empty($editResource) ? $editResource->description : old('description') }}</textarea>
                                                        @error('description')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Capacity --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.capacity') }}</label>
                                                        <input type="number" name="capacity" min="0"
                                                               class="form-control @error('capacity') is-invalid @enderror"
                                                               value="{{ !empty($editResource) ? $editResource->capacity : old('capacity') }}"
                                                               placeholder="0"/>
                                                        @error('capacity')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Extra Price --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.extra_price') }}</label>
                                                        <input type="number" name="extra_price" min="0" step="0.01"
                                                               class="form-control @error('extra_price') is-invalid @enderror"
                                                               value="{{ !empty($editResource) ? $editResource->extra_price : old('extra_price', 0) }}"
                                                               placeholder="0.00"/>
                                                        @error('extra_price')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Attributes --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.attributes') }} <small class="text-gray-500">(JSON)</small></label>
                                                        <textarea name="attributes" rows="3"
                                                                  class="form-control @error('attributes') is-invalid @enderror"
                                                                  placeholder='{"key": "value"}'>{{ !empty($editResource) ? $editResource->attributes : old('attributes') }}</textarea>
                                                        @error('attributes')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Image --}}
                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('admin/main.image') }}</label>
                                                        <div class="input-group">
                                                            <div class="input-group-prepend">
                                                                <button type="button" class="input-group-text admin-file-manager"
                                                                        data-input="image" data-preview="image_holder">
                                                                    <i class="fa fa-upload"></i>
                                                                </button>
                                                            </div>
                                                            <input type="text" name="image" id="image"
                                                                   value="{{ !empty($editResource) ? $editResource->image : old('image') }}"
                                                                   class="form-control @error('image') is-invalid @enderror"/>
                                                            <div class="invalid-feedback">@error('image') {{ $message }} @enderror</div>
                                                        </div>
                                                        @if(!empty($editResource) && $editResource->image)
                                                            <div class="mt-2">
                                                                <img id="image_holder" src="{{ $editResource->image }}" alt="image" width="80" class="rounded"/>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    {{-- Sort Order --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.order') }}</label>
                                                        <input type="number" name="sort_order" min="0"
                                                               class="form-control @error('sort_order') is-invalid @enderror"
                                                               value="{{ !empty($editResource) ? $editResource->sort_order : old('sort_order', 0) }}"/>
                                                        @error('sort_order')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Booking ID (optional) --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.booking_id') }} <small class="text-gray-500">({{ trans('admin/main.optional') }})</small></label>
                                                        <input type="number" name="booking_id" min="1"
                                                               class="form-control @error('booking_id') is-invalid @enderror"
                                                               value="{{ !empty($editResource) ? $editResource->booking_id : old('booking_id') }}"/>
                                                        @error('booking_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Status --}}
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" name="status"
                                                                   class="custom-control-input" id="status"
                                                                   {{ (!empty($editResource) && $editResource->status) || empty($editResource) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status">
                                                                {{ trans('admin/main.active') }}
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="text-right col-12 mt-3">
                                                        @if(!empty($editResource))
                                                            <a href="{{ getAdminPanelUrl() }}/booking/resources"
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