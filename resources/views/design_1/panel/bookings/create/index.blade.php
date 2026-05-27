@extends('design_1.panel.layouts.panel')

@section('content')
    <div class="bg-white p-16 rounded-24 mt-20">
        <div class="d-flex align-items-center justify-content-between mb-20">
            <div>
                <h3 class="font-16 font-weight-bold">
                    {{ isset($booking) && !is_null($booking) ? trans('panel.edit_booking') : trans('panel.new_booking') }}
                </h3>
            </div>
            <a href="{{ route('panel.bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                {{ trans('public.back') }}
            </a>
        </div>

        <form action="{{ (isset($booking) && !is_null($booking)) ? route('panel.bookings.update.post', ['id' => $booking->id]) : route('panel.bookings.store') }}" method="post">
            @csrf
            @include('design_1.panel.bookings.create.form', ['booking' => $booking ?? null])
        </form>
    </div>
@endsection