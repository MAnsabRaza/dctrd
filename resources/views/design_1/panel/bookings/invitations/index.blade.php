@extends('design_1.panel.layout')

@section('content')

    <div class="panel-page-header d-flex justify-content-between align-items-center">
        <h4 class="panel-page-title">{{ trans('panel.invited_bookings') }}</h4>
        <div class="panel-breadcrumb">
            <span>{{ trans('panel.platform') }}</span> /
            <span>{{ trans('panel.dashboard') }}</span> /
            <span>{{ trans('panel.invited_bookings') }}</span>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="stat-card d-flex justify-content-between align-items-center">
                <div>
                    <p class="stat-card-label">{{ trans('panel.total_bookings') }}</p>
                    <h3 class="stat-card-value">{{ $stats['bookings_count'] }}</h3>
                </div>
                <div class="stat-card-icon bg-primary-light">
                    <i class="fa-light fa-calendar-check"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card d-flex justify-content-between align-items-center">
                <div>
                    <p class="stat-card-label">{{ trans('panel.total_booking_sales') }}</p>
                    <h3 class="stat-card-value">{{ currencyFormat($stats['bookings_sales']) }}</h3>
                </div>
                <div class="stat-card-icon bg-dark-light">
                    <i class="fa-light fa-bag-shopping"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- List / Empty State --}}
    <div class="panel-box">

        @if($bookings->count())

            <div class="row g-3" id="bookings-list">
                @foreach($bookings as $booking)
                    @include('design_1.panel.bookings.invitations.card_item', ['booking' => $booking])
                @endforeach
            </div>

            <div class="mt-4">
                {!! $pagination !!}
            </div>

        @else

            <div class="empty-state text-center py-5">
                <img src="{{ asset('design_1/img/empty-states/no-bookings.svg') }}"
                     alt="{{ trans('panel.no_bookings') }}"
                     class="empty-state-img mb-3" />

                <h5 class="empty-state-title">{{ trans('panel.no_bookings') }}</h5>
                <p class="empty-state-text text-muted">
                    {{ trans('panel.no_invited_bookings_desc') }}
                </p>
            </div>

        @endif

    </div>

@endsection