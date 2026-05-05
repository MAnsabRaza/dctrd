@extends('admin.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.css">
@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.'.(!empty($editBooking) ? 'edit' : 'create_booking')) }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="{{ getAdminPanelUrl('/booking') }}">{{ trans('admin/main.booking') }}</a>
                </div>
                <div class="breadcrumb-item">
                    {{ !empty($editBooking) ? trans('admin/main.edit') : trans('admin/main.create_booking') }}
                </div>
            </div>
        </div>

        <div class="section-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible show fade">
                    <div class="alert-body">
                        <button class="close" data-dismiss="alert"><span>×</span></button>
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <div class="row">
                <div class="col-12 col-md-12">
                    <div class="card">
                        <div class="card-body">

                            <form action="{{ getAdminPanelUrl() }}/booking/{{ !empty($editBooking) ? $editBooking->id . '/update' : 'store' }}" method="POST">
                                {{ csrf_field() }}

                                <div class="row">
                                    <div class="col-12 col-md-6">

                                        {{-- ── Basic Info ── --}}

                                        <div class="form-group">
                                            <label class="input-label">{{ trans('admin/main.title') }}</label>
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
                                            <label class="input-label">{{ trans('admin/main.url') }} / Slug</label>
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
                                            <label class="input-label">{{ trans('admin/main.categories') }}</label>
                                            <select name="category_id"
                                                    class="form-control @error('category_id') is-invalid @enderror">
                                                <option value="">— {{ trans('admin/main.choose_category') }} —</option>
                                                @if(!empty($categories))
                                                    @foreach($categories as $cat)
                                                        <option value="{{ $cat->id }}"
                                                            {{ (!empty($editBooking) && $editBooking->category_id == $cat->id) || old('category_id') == $cat->id ? 'selected' : '' }}>
                                                            {{ $cat->title }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Booking Type</label>
                                            <select name="booking_type"
                                                    class="form-control @error('booking_type') is-invalid @enderror">
                                                <option value="">— Select —</option>
                                                @foreach(['tour','activity','rental','event','service','accommodation'] as $type)
                                                    <option value="{{ $type }}"
                                                        {{ (!empty($editBooking) && $editBooking->booking_type === $type) || old('booking_type') === $type ? 'selected' : '' }}>
                                                        {{ ucfirst($type) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('booking_type')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Sub Type</label>
                                            <input type="text"
                                                   name="sub_type"
                                                   class="form-control"
                                                   value="{{ !empty($editBooking) ? $editBooking->sub_type : old('sub_type') }}"
                                                   placeholder="Optional">
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Requirements</label>
                                            <input type="text"
                                                   name="requirements"
                                                   class="form-control"
                                                   value="{{ !empty($editBooking) ? $editBooking->requirements : old('requirements') }}"
                                                   placeholder="What does the customer need?">
                                        </div>

                                        {{-- ── Pricing ── --}}

                                        <div class="form-group">
                                            <label class="input-label">Price</label>
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

                                        <div class="form-group">
                                            <label class="input-label">Discount Price</label>
                                            <input type="number"
                                                   name="discount_price"
                                                   step="0.01"
                                                   min="0"
                                                   class="form-control"
                                                   value="{{ !empty($editBooking) ? $editBooking->discount_price : old('discount_price') }}"
                                                   placeholder="0.00">
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Currency</label>
                                            <select name="currency" class="form-control">
                                                @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                                                    <option value="{{ $cur }}"
                                                        {{ (!empty($editBooking) && $editBooking->currency === $cur) || old('currency', 'USD') === $cur ? 'selected' : '' }}>
                                                        {{ $cur }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Price Per</label>
                                            <select name="price_per" class="form-control">
                                                <option value="">— None —</option>
                                                @foreach(['person','group','hour','day','night','session','item'] as $pp)
                                                    <option value="{{ $pp }}"
                                                        {{ (!empty($editBooking) && $editBooking->price_per === $pp) || old('price_per') === $pp ? 'selected' : '' }}>
                                                        {{ ucfirst($pp) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Price Unit / Label</label>
                                            <input type="text"
                                                   name="price_unit"
                                                   class="form-control"
                                                   value="{{ !empty($editBooking) ? $editBooking->price_unit : old('price_unit') }}"
                                                   placeholder="e.g. per night, per adult">
                                        </div>

                                    </div>
                                </div>

                                {{-- ── Description ── --}}

                                <div class="form-group mt-15">
                                    <label class="input-label">{{ trans('admin/main.description') }}</label>
                                    <textarea name="description"
                                              rows="4"
                                              class="form-control @error('description') is-invalid @enderror"
                                              placeholder="{{ trans('admin/main.description_placeholder') }}">{{ !empty($editBooking) ? $editBooking->description : old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- ── Status & Visibility ── --}}

                                <div class="form-group mt-30 d-flex align-items-center cursor-pointer">
                                    <div class="custom-control custom-switch align-items-start">
                                        <input type="checkbox"
                                               name="featured"
                                               id="featuredSwitch"
                                               value="on"
                                               class="custom-control-input"
                                               {{ (!empty($editBooking) && $editBooking->featured) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="featuredSwitch"></label>
                                    </div>
                                    <label for="featuredSwitch" class="mb-0">{{ trans('admin/main.featured') }}</label>
                                </div>

                                <div class="form-group mt-30 d-flex align-items-center cursor-pointer">
                                    <div class="custom-control custom-switch align-items-start">
                                        <input type="checkbox"
                                               name="status"
                                               id="statusSwitch"
                                               value="published"
                                               class="custom-control-input"
                                               {{ (!empty($editBooking) && $editBooking->status === 'published') || (!isset($editBooking) && old('status', 'published') === 'published') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="statusSwitch"></label>
                                    </div>
                                    <label for="statusSwitch" class="mb-0">{{ trans('admin/main.publish') }}</label>
                                </div>

                                <button type="submit" class="btn btn-primary mt-1">
                                    {{ trans('admin/main.save_change') }}
                                </button>

                            </form>

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