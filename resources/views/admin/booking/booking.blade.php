@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
@endpush
@section('content')
    <section class="section">

        <div class="section-header">
            <h1>{{ trans('admin/main.booking') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="{{ getAdminPanelUrl('/booking') }}">{{ trans('admin/main.booking') }}</a>
                </div>
                @if(!empty($editBooking))
                    <div class="breadcrumb-item">{{ trans('admin/main.edit') }}</div>
                @endif
            </div>
        </div>

        <div class="section-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert">
                            <span>×</span>
                        </button>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <div class="row">

                {{-- ══════════════════════════════════════════
                     LEFT: Bookings List
                ══════════════════════════════════════════ --}}
                @can('admin_booking')
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h4>{{ trans('admin/main.bookings') }}</h4>
                            @can('admin_booking_create')
                                <div class="card-header-action">
                                    <a href="{{ getAdminPanelUrl('/booking') }}" class="btn btn-primary btn-sm">
                                        <i class="fa fa-plus mr-1"></i>
                                        {{ trans('admin/main.create_booking') }}
                                    </a>
                                </div>
                            @endcan
                        </div>
                        <div class="card-body p-0">
                            @if(!empty($bookings) && $bookings->count())
                                <div class="table-responsive">
                                    <table class="table table-striped custom-table font-14">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="40">#</th>
                                                <th>{{ trans('admin/main.title') }}</th>
                                                <th>Type</th>
                                                <th>{{ trans('admin/main.categories') }}</th>
                                                <th>Price</th>
                                                <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                <th width="100">{{ trans('admin/main.action') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($bookings as $item)
                                                <tr>
                                                    <td class="text-center text-muted">{{ $loop->iteration }}</td>

                                                    <td>
                                                        <div class="font-weight-500">{{ $item->title }}</div>
                                                        @if($item->featured)
                                                            <span class="badge badge-warning badge-sm">
                                                                <i class="fa fa-star mr-1"></i>Featured
                                                            </span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <span class="badge badge-primary">{{ $item->booking_type }}</span>
                                                        @if($item->sub_type)
                                                            <div class="text-muted font-12 mt-1">{{ $item->sub_type }}</div>
                                                        @endif
                                                    </td>

                                                    <td class="text-muted">
                                                        {{ $item->category->title ?? '—' }}
                                                    </td>

                                                    <td>
                                                        <span class="font-weight-bold">
                                                            {{ $item->currency ?? 'USD' }} {{ number_format($item->full_price, 2) }}
                                                        </span>
                                                        @if($item->discount_price && $item->discount_price < $item->price)
                                                            <div class="text-muted font-12">
                                                                <del>{{ number_format($item->price, 2) }}</del>
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td class="text-center">
                                                        @if($item->status === 'published')
                                                            <span class="badge badge-success">{{ ucfirst($item->status) }}</span>
                                                        @elseif($item->status === 'pending')
                                                            <span class="badge badge-warning">{{ ucfirst($item->status) }}</span>
                                                        @elseif($item->status === 'rejected')
                                                            <span class="badge badge-danger">{{ ucfirst($item->status) }}</span>
                                                        @else
                                                            <span class="badge badge-secondary">{{ ucfirst($item->status) }}</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <div class="btn-group dropdown table-actions">
                                                            <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                            </button>
                                                            <div class="dropdown-menu dropdown-menu-right">

                                                                @can('admin_booking_edit')
                                                                    <a href="{{ getAdminPanelUrl('/booking/' . $item->id . '/edit') }}"
                                                                       class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                        <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                        <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                    </a>
                                                                @endcan

                                                                @can('admin_booking_delete')
                                                                    @include('admin.includes.delete_button', [
                                                                        'url'       => getAdminPanelUrl('/booking/' . $item->id . '/delete'),
                                                                        'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                        'btnText'   => trans('admin/main.delete'),
                                                                        'btnIcon'   => 'trash',
                                                                        'iconType'  => 'lin',
                                                                        'iconClass' => 'text-danger mr-2',
                                                                    ])
                                                                @endcan

                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="text-center text-muted mt-30 mb-30">
                                    {{ trans('admin/main.no_result') }}
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endcan


                {{-- ══════════════════════════════════════════
                     RIGHT: Create / Edit Form
                ══════════════════════════════════════════ --}}
                @canany(['admin_booking_create', 'admin_booking_edit'])
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h4>
                                {{ !empty($editBooking)
                                    ? trans('admin/main.edit') . ' ' . trans('admin/main.booking')
                                    : trans('admin/main.create_booking') }}
                            </h4>
                        </div>
                        <div class="card-body">

                         {{-- BROKEN --}}
{{-- FIXED --}}
<form method="POST" action="{{ getAdminPanelUrl() }}/booking/{{ !empty($editBooking) ? $editBooking->id . '/update' : 'store' }}">
                                {{-- ── Basic Info ── --}}
                                <h6 class="text-primary font-weight-bold mb-3 mt-2">
                                    <i class="fa fa-info-circle mr-1"></i> Basic Information
                                </h6>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.title') }} <span class="text-danger">*</span></label>
                                    <input type="text"
                                           name="title"
                                           class="form-control @error('title') is-invalid @enderror"
                                           value="{{ !empty($editBooking) ? $editBooking->title : old('title') }}"
                                           placeholder="{{ trans('admin/main.choose_title') }}">
                                    @error('title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.url') }} / Slug</label>
                                    <input type="text"
                                           name="slug"
                                           class="form-control @error('slug') is-invalid @enderror"
                                           value="{{ !empty($editBooking) ? $editBooking->slug : old('slug') }}"
                                           placeholder="auto-generated-if-empty">
                                    @error('slug')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.categories') }}</label>
                                    <select name="category_id"
                                            class="form-control @error('category_id') is-invalid @enderror">
                                        <option value="">— Select Category —</option>
                                        @if(!empty($categories))
                                            @foreach($categories as $cat)
                                                <option value="{{ $cat->id }}"
                                                    {{ (!empty($editBooking) && $editBooking->category_id == $cat->id)
                                                        || old('category_id') == $cat->id ? 'selected' : '' }}>
                                                    {{ $cat->title }}
                                                </option>
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('category_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Booking Type <span class="text-danger">*</span></label>
                                            <select name="booking_type"
                                                    class="form-control @error('booking_type') is-invalid @enderror">
                                                <option value="">— Select —</option>
                                                @foreach(['tour','activity','rental','event','service','accommodation'] as $type)
                                                    <option value="{{ $type }}"
                                                        {{ (!empty($editBooking) && $editBooking->booking_type === $type)
                                                            || old('booking_type') === $type ? 'selected' : '' }}>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('booking_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Sub Type</label>
                                            <input type="text"
                                                   name="sub_type"
                                                   class="form-control"
                                                   value="{{ !empty($editBooking) ? $editBooking->sub_type : old('sub_type') }}"
                                                   placeholder="Optional">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.description') }}</label>
                                    <textarea name="description"
                                              rows="3"
                                              class="form-control"
                                              placeholder="Short description...">{{ !empty($editBooking) ? $editBooking->description : old('description') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Requirements</label>
                                    <textarea name="requirements"
                                              rows="2"
                                              class="form-control"
                                              placeholder="What does the customer need?">{{ !empty($editBooking) ? $editBooking->requirements : old('requirements') }}</textarea>
                                </div>

                                <hr>

                                {{-- ── Pricing ── --}}
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fa fa-dollar mr-1"></i> Pricing
                                </h6>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Price <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   name="price"
                                                   step="0.01"
                                                   min="0"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   value="{{ !empty($editBooking) ? $editBooking->price : old('price') }}"
                                                   placeholder="0.00">
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Discount Price</label>
                                            <input type="number"
                                                   name="discount_price"
                                                   step="0.01"
                                                   min="0"
                                                   class="form-control"
                                                   value="{{ !empty($editBooking) ? $editBooking->discount_price : old('discount_price') }}"
                                                   placeholder="0.00">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Currency</label>
                                            <select name="currency" class="form-control">
                                                @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                                                    <option value="{{ $cur }}"
                                                        {{ (!empty($editBooking) && $editBooking->currency === $cur)
                                                            || old('currency', 'USD') === $cur ? 'selected' : '' }}>
                                                        {{ $cur }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>Price Per</label>
                                            <select name="price_per" class="form-control">
                                                <option value="">— None —</option>
                                                @foreach(['person','group','hour','day','night','session','item'] as $pp)
                                                    <option value="{{ $pp }}"
                                                        {{ (!empty($editBooking) && $editBooking->price_per === $pp)
                                                            || old('price_per') === $pp ? 'selected' : '' }}>
                                                        {{ ucfirst($pp) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Price Unit / Label</label>
                                    <input type="text"
                                           name="price_unit"
                                           class="form-control"
                                           value="{{ !empty($editBooking) ? $editBooking->price_unit : old('price_unit') }}"
                                           placeholder="e.g. per night, per adult">
                                </div>

                                <hr>

                                {{-- ── Status ── --}}
                                <h6 class="text-primary font-weight-bold mb-3">
                                    <i class="fa fa-toggle-on mr-1"></i> Status & Visibility
                                </h6>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.status') }}</label>
                                    <select name="status" class="form-control">
                                        @foreach(['draft','published','pending','rejected'] as $s)
                                            <option value="{{ $s }}"
                                                {{ (!empty($editBooking) && $editBooking->status === $s)
                                                    || old('status', 'draft') === $s ? 'selected' : '' }}>
                                                {{ ucfirst($s) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox"
                                               name="featured"
                                               id="featured"
                                               value="on"
                                               class="custom-control-input"
                                               {{ (!empty($editBooking) && $editBooking->featured) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="featured">
                                            {{ trans('admin/main.featured') }}
                                        </label>
                                    </div>
                                </div>

                                {{-- ── Form Actions ── --}}
                                <div class="text-right mt-3">
                                    @if(!empty($editBooking))
                                        <a href="{{ getAdminPanelUrl('/booking') }}"
                                           class="btn btn-secondary mr-2">
                                            {{ trans('admin/main.cancel') }}
                                        </a>
                                    @endif
                                    <button type="submit" class="btn btn-primary">
                                        {{ !empty($editBooking)
                                            ? trans('admin/main.save_change')
                                            : trans('admin/main.create_booking') }}
                                    </button>
                                </div>

                            </form>

                        </div>
                    </div>
                </div>
                @endcanany

            </div>{{-- end .row --}}
        </div>{{-- end .section-body --}}
    </section>
@endsection
@push('scripts_bottom')
    <script src="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
@endpush