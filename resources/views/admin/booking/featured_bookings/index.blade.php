@extends('admin.layouts.app')

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
                            <form action="{{ getAdminPanelUrl() }}/booking/featured-bookings" method="post">
                                {{ csrf_field() }}

                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="form-group mt-3">
                                            <label class="input-label">Featured Bookings</label>
                                            <select name="value[featured_bookings][]" multiple="multiple" class="form-control select2" data-placeholder="Select bookings">
                                                @foreach($bookings as $booking)
                                                    <option value="{{ $booking->id }}" {{ (!empty($settingValues) and !empty($settingValues['featured_bookings']) and in_array($booking->id, $settingValues['featured_bookings'])) ? 'selected' : '' }}>
                                                        {{ $booking->title }}
                                                    </option>
                                                @endforeach
                                            </select>

                                            <p class="font-12 text-gray-500 mt-2">Add featured bookings to show on the booking listing page.</p>
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Background Image</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text admin-file-manager" data-input="background_image" data-preview="holder">
                                                        <i class="fa fa-upload"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="value[background_image]" id="background_image" class="form-control" value="{{ (!empty($settingValues) and !empty($settingValues['background_image'])) ? $settingValues['background_image'] : '' }}"/>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label class="input-label">Overlay Image</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <button type="button" class="input-group-text admin-file-manager" data-input="overlay_image" data-preview="holder">
                                                        <i class="fa fa-upload"></i>
                                                    </button>
                                                </div>
                                                <input type="text" name="value[overlay_image]" id="overlay_image" class="form-control" value="{{ (!empty($settingValues) and !empty($settingValues['overlay_image'])) ? $settingValues['overlay_image'] : '' }}"/>
                                                <div class="invalid-feedback"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-6 text-right">
                                    <button type="submit" class="btn btn-primary mt-1">{{ trans('admin/main.submit') }}</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
