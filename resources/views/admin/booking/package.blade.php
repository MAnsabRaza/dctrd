@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ !empty($editPackage) ? trans('admin/main.edit') : trans('admin/main.new') }} {{ trans('update.package') ?? 'Package' }}</h5>
                        </div>

                        <div class="card-body">
                            <form method="post" action="{{ !empty($editPackage) ? getAdminPanelUrl('/booking/package/'.$editPackage->id.'/update') : getAdminPanelUrl('/booking/package/store') }}">
                                {{ csrf_field() }}

                                <div class="form-group">
                                    <label>{{ trans('admin/main.title') }}</label>
                                    <input type="text" name="title" class="form-control" value="{{ old('title', $editPackage->title ?? '') }}" required>
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.category') }}</label>
                                    <select name="category_id" class="form-control">
                                        <option value="">{{ trans('admin/main.select') }}</option>
                                        @foreach($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('category_id', $editPackage->category_id ?? null) == $category->id ? 'selected' : '' }}>{{ $category->title }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.description') }}</label>
                                    <textarea name="description" class="form-control" rows="4">{{ old('description', $editPackage->description ?? '') }}</textarea>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>{{ trans('admin/main.price') }}</label>
                                            <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $editPackage->price ?? 0) }}" required>
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>{{ trans('admin/main.currency') ?? 'Currency' }}</label>
                                            <input type="text" name="currency" class="form-control" value="{{ old('currency', $editPackage->currency ?? 'USD') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>{{ trans('admin/main.discount') ?? 'Discount' }}</label>
                                            <input type="number" step="0.01" name="discount_price" class="form-control" value="{{ old('discount_price', $editPackage->discount_price ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>{{ trans('admin/main.status') }}</label>
                                            <select name="status" class="form-control">
                                                @foreach(['draft', 'published', 'inactive'] as $status)
                                                    <option value="{{ $status }}" {{ old('status', $editPackage->status ?? 'draft') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>{{ trans('update.validity_days') ?? 'Validity days' }}</label>
                                            <input type="number" name="validity_days" class="form-control" value="{{ old('validity_days', $editPackage->validity_days ?? '') }}">
                                        </div>
                                    </div>

                                    <div class="col-6">
                                        <div class="form-group">
                                            <label>{{ trans('update.usage_limit') ?? 'Usage limit' }}</label>
                                            <input type="number" name="usage_limit" class="form-control" value="{{ old('usage_limit', $editPackage->usage_limit ?? '') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>{{ trans('admin/main.bookings') ?? 'Bookings' }}</label>
                                    @php($packageItems = !empty($editPackage) ? $editPackage->items : collect())

                                    @for($i = 0; $i < 5; $i++)
                                        @php($item = $packageItems->get($i))
                                        <div class="d-flex mb-2">
                                            <select name="booking_ids[]" class="form-control mr-2">
                                                <option value="">{{ trans('admin/main.select') }}</option>
                                                @foreach($bookings as $booking)
                                                    <option value="{{ $booking->id }}" {{ !empty($item) && $item->booking_id == $booking->id ? 'selected' : '' }}>{{ $booking->title }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="quantities[]" class="form-control mr-2" style="max-width: 90px" value="{{ $item->quantity ?? 1 }}" min="1">
                                            <input type="number" name="included_minutes[]" class="form-control" style="max-width: 130px" value="{{ $item->included_minutes ?? '' }}" placeholder="Minutes">
                                        </div>
                                    @endfor
                                </div>

                                <div class="custom-control custom-checkbox mb-3">
                                    <input type="checkbox" name="featured" class="custom-control-input" id="featuredPackage" {{ old('featured', !empty($editPackage) && $editPackage->featured) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="featuredPackage">{{ trans('admin/main.featured') ?? 'Featured' }}</label>
                                </div>

                                <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                @if(!empty($editPackage))
                                    <a href="{{ getAdminPanelUrl('/booking/package') }}" class="btn btn-secondary">{{ trans('admin/main.cancel') }}</a>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $pageTitle }}</h5>
                        </div>

                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped font-14">
                                    <thead>
                                        <tr>
                                            <th>{{ trans('admin/main.title') }}</th>
                                            <th>{{ trans('admin/main.category') }}</th>
                                            <th>{{ trans('admin/main.price') }}</th>
                                            <th>{{ trans('admin/main.items') ?? 'Items' }}</th>
                                            <th>{{ trans('admin/main.status') }}</th>
                                            <th class="text-right">{{ trans('admin/main.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($packages as $package)
                                            <tr>
                                                <td>{{ $package->title }}</td>
                                                <td>{{ optional($package->category)->title }}</td>
                                                <td>{{ $package->currency }} {{ number_format((float) ($package->discount_price ?: $package->price), 2) }}</td>
                                                <td>{{ $package->items->count() }}</td>
                                                <td>{{ ucfirst($package->status) }}</td>
                                                <td class="text-right">
                                                    <a href="{{ getAdminPanelUrl('/booking/package/'.$package->id.'/edit') }}" class="btn-transparent text-primary">{{ trans('admin/main.edit') }}</a>
                                                    <a href="{{ getAdminPanelUrl('/booking/package/'.$package->id.'/delete') }}" class="btn-transparent text-danger ml-2 delete-action">{{ trans('admin/main.delete') }}</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $packages->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
