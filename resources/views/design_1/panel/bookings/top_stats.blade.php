@php
    $totalBookings    = \App\Models\Booking::count();
    $activeBookings   = \App\Models\Booking::where('status', 'published')->count();
    $featuredBookings = \App\Models\Booking::where('featured', true)->count();
    $draftBookings    = \App\Models\Booking::where('status', 'draft')->count();
@endphp

<div class="row">

    <div class="col-6 col-lg-3">
        <div class="bg-white rounded-16 p-16 mb-20">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="font-12 text-muted mb-4">{{ trans('panel.total_bookings') }}</p>
                    <h4 class="font-24 font-weight-700">{{ $totalBookings }}</h4>
                </div>
                <div class="avatar avatar-48 bg-primary-light rounded-circle d-flex align-items-center justify-content-center">
                    <i data-feather="calendar" width="20" height="20" class="text-primary"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="bg-white rounded-16 p-16 mb-20">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="font-12 text-muted mb-4">{{ trans('public.active') }}</p>
                    <h4 class="font-24 font-weight-700 text-success">{{ $activeBookings }}</h4>
                </div>
                <div class="avatar avatar-48 bg-success-light rounded-circle d-flex align-items-center justify-content-center">
                    <i data-feather="check-circle" width="20" height="20" class="text-success"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="bg-white rounded-16 p-16 mb-20">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="font-12 text-muted mb-4">{{ trans('panel.featured') }}</p>
                    <h4 class="font-24 font-weight-700 text-warning">{{ $featuredBookings }}</h4>
                </div>
                <div class="avatar avatar-48 bg-warning-light rounded-circle d-flex align-items-center justify-content-center">
                    <i data-feather="star" width="20" height="20" class="text-warning"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="bg-white rounded-16 p-16 mb-20">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <p class="font-12 text-muted mb-4">{{ trans('public.inactive') }}</p>
                    <h4 class="font-24 font-weight-700 text-secondary">{{ $draftBookings }}</h4>
                </div>
                <div class="avatar avatar-48 bg-secondary-light rounded-circle d-flex align-items-center justify-content-center">
                    <i data-feather="pause-circle" width="20" height="20" class="text-secondary"></i>
                </div>
            </div>
        </div>
    </div>

</div>