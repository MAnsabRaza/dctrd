@extends('design_1.panel.layouts.panel')

@section('content')

<div class="row">

    <div class="col-12">

        <div class="bg-white rounded-24 p-16">

            <div class="d-flex align-items-center justify-content-between flex-wrap">

                <div>

                    <h3 class="font-16 font-weight-bold">
                        Booking Calendar
                    </h3>

                    <p class="text-muted font-12 mt-4">
                        Manage booking availability and slots
                    </p>

                </div>

                <form method="get"
                      action="{{ route('panel.bookings.calendar') }}"
                      class="d-flex align-items-center flex-wrap">

                    <div class="mr-10">

                        <select name="booking_id"
                                class="form-control select2">

                            @foreach($bookings as $item)

                                <option value="{{ $item->id }}"
                                    {{ $booking->id == $item->id ? 'selected' : '' }}>

                                    {{ $item->title }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="mr-10">

                        <select name="month"
                                class="form-control">

                            @for($m = 1; $m <= 12; $m++)

                                <option value="{{ $m }}"
                                    {{ $month == $m ? 'selected' : '' }}>

                                    {{ \Carbon\Carbon::create()->month($m)->format('F') }}

                                </option>

                            @endfor

                        </select>

                    </div>

                    <div class="mr-10">

                        <select name="year"
                                class="form-control">

                            @for($y = now()->year - 2; $y <= now()->year + 3; $y++)

                                <option value="{{ $y }}"
                                    {{ $year == $y ? 'selected' : '' }}>

                                    {{ $y }}

                                </option>

                            @endfor

                        </select>

                    </div>

                    <button type="submit"
                            class="btn btn-primary">

                        View Calendar

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>

<div class="bg-white rounded-24 p-16 mt-20">

    <div class="calendar-grid">

        {{-- WEEK DAYS --}}

        @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dayName)

            <div class="calendar-weekday">

                {{ $dayName }}

            </div>

        @endforeach

        {{-- DAYS --}}

        @foreach($calendarDays as $day)

            @php
                $isAvailable = $day['isAvailable'];
                $isCurrentMonth = $day['isCurrentMonth'];
                $isToday = $day['isToday'];
            @endphp

            <div class="calendar-day
                {{ !$isCurrentMonth ? 'calendar-day-disabled' : '' }}
                {{ $isToday ? 'calendar-day-today' : '' }}
            ">

                <div class="d-flex align-items-center justify-content-between">

                    <span class="font-weight-bold">

                        {{ $day['date']->day }}

                    </span>

                    @if($isAvailable)

                        <span class="badge badge-success">
                            Open
                        </span>

                    @else

                        <span class="badge badge-danger">
                            Full
                        </span>

                    @endif

                </div>

                <div class="mt-10">

                    <div class="font-12 text-muted">

                        Price

                    </div>

                    <div class="font-weight-bold">

                        {{ handlePrice($day['price']) }}

                    </div>

                </div>

                <div class="mt-10">

                    <div class="font-12 text-muted">

                        Slots

                    </div>

                    <div>

                        {{ $day['slotsLeft'] }}

                    </div>

                </div>

                <div class="mt-10">

                    <div class="font-12 text-muted">

                        Orders

                    </div>

                    <div>

                        {{ $day['ordersCount'] }}

                    </div>

                </div>

                @if(count($day['slots']))

                    <div class="mt-12">

                        @foreach($day['slots'] as $slot)

                            <span class="badge badge-light mr-4 mb-4">

                                {{ $slot['start'] ?? '' }}

                            </span>

                        @endforeach

                    </div>

                @endif

            </div>

        @endforeach

    </div>

</div>

@endsection

@push('styles_top')

<style>

.calendar-grid{
    display:grid;
    grid-template-columns:repeat(7,1fr);
    gap:12px;
}

.calendar-weekday{
    padding:12px;
    text-align:center;
    font-weight:700;
    background:#f8f9fa;
    border-radius:12px;
}

.calendar-day{
    min-height:180px;
    border:1px solid #e5e7eb;
    border-radius:16px;
    padding:14px;
    background:#fff;
}

.calendar-day-disabled{
    opacity:.4;
}

.calendar-day-today{
    border:2px solid #4361ee;
}

</style>

@endpush