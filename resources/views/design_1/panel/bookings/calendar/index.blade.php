@extends('design_1.panel.layouts.panel')

@section('content')

<div class="bg-white rounded-24 p-16">

    <div class="d-flex align-items-center justify-content-between flex-wrap mb-16">
        <h3 class="font-16 font-weight-bold mb-0">
            {{ trans('panel.booking_calendar') }}: {{ trans('panel.available') }} / {{ trans('panel.purchased') }}
        </h3>
    </div>

    <form method="get" action="{{ route('panel.bookings.calendar') }}" id="bookingCalendarFilterForm">

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-12">

            {{-- Month / Year + navigation --}}
            <div class="d-flex align-items-center gap-8">

                @php
                    $prevMonth = $month == 1 ? 12 : $month - 1;
                    $prevYear = $month == 1 ? $year - 1 : $year;
                    $nextMonth = $month == 12 ? 1 : $month + 1;
                    $nextYear = $month == 12 ? $year + 1 : $year;
                @endphp

                <a href="{{ route('panel.bookings.calendar', array_merge($request->query(), ['month' => $prevMonth, 'year' => $prevYear])) }}"
                   class="d-flex-center size-32 bg-gray-100 rounded-8">
                    <x-iconsax-lin-arrow-left class="icons" width="18px" height="18px"/>
                </a>

                <select name="month" class="form-control select-sm" onchange="document.getElementById('bookingCalendarFilterForm').submit()">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>

                <select name="year" class="form-control select-sm" onchange="document.getElementById('bookingCalendarFilterForm').submit()">
                    @for($y = now()->year - 2; $y <= now()->year + 3; $y++)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>

                <a href="{{ route('panel.bookings.calendar', array_merge($request->query(), ['month' => $nextMonth, 'year' => $nextYear])) }}"
                   class="d-flex-center size-32 bg-gray-100 rounded-8">
                    <x-iconsax-lin-arrow-right class="icons" width="18px" height="18px"/>
                </a>
            </div>

            {{-- Toggles --}}
            <div class="d-flex align-items-center gap-20 flex-wrap">

                <div class="d-flex align-items-center gap-8">
                    <span class="font-14">{{ trans('panel.available') }}</span>
                    <label class="booking-cal-switch">
                        <input type="checkbox" name="available" value="1" {{ $showAvailable ? 'checked' : '' }}
                               onchange="document.getElementById('bookingCalendarFilterForm').submit()">
                        <span class="booking-cal-switch-slider"></span>
                    </label>
                </div>

                <div class="d-flex align-items-center gap-8">
                    <span class="font-14">{{ trans('panel.purchased') }}</span>
                    <label class="booking-cal-switch">
                        <input type="checkbox" name="purchased" value="1" {{ $showPurchased ? 'checked' : '' }}
                               onchange="document.getElementById('bookingCalendarFilterForm').submit()">
                        <span class="booking-cal-switch-slider"></span>
                    </label>
                </div>

                <div class="d-flex align-items-center gap-8">
                    <span class="font-14">{{ trans('panel.values') }}</span>
                    <label class="booking-cal-switch">
                        <input type="checkbox" name="values" value="1" {{ $showValues ? 'checked' : '' }}
                               onchange="document.getElementById('bookingCalendarFilterForm').submit()">
                        <span class="booking-cal-switch-slider"></span>
                    </label>
                </div>

                <div class="d-flex align-items-center gap-8">
                    <span class="font-14">{{ trans('panel.number_of') }}</span>
                    <label class="booking-cal-switch">
                        <input type="checkbox" name="number_of" value="1" {{ $showNumberOf ? 'checked' : '' }}
                               onchange="document.getElementById('bookingCalendarFilterForm').submit()">
                        <span class="booking-cal-switch-slider"></span>
                    </label>
                </div>
            </div>

            {{-- Bookings multi-select + status + filter btn --}}
            <div class="d-flex align-items-center gap-8 flex-wrap">

                <select name="booking_ids[]" multiple class="form-control select2-bookings" style="min-width:220px;">
                    @foreach($allBookings as $item)
                        <option value="{{ $item->id }}" {{ in_array($item->id, $selectedBookingIds) ? 'selected' : '' }}>
                            {{ $item->title }}
                        </option>
                    @endforeach
                </select>

                <select name="status" class="form-control select-sm">
                    <option value="all" {{ $status == 'all' ? 'selected' : '' }}>{{ trans('panel.all_status') }}</option>
                    @foreach($statusOptions as $statusOption)
                        <option value="{{ $statusOption }}" {{ $status == $statusOption ? 'selected' : '' }}>
                            {{ trans('panel.' . $statusOption) }}
                        </option>
                    @endforeach
                </select>

                <button type="submit" class="btn btn-primary">
                    {{ trans('panel.filter') }}
                </button>
            </div>

        </div>

    </form>

</div>

<div class="bg-white rounded-24 p-16 mt-20">

    <div class="calendar-grid">

        {{-- WEEK DAYS --}}
        @foreach([trans('panel.monday'), trans('panel.tuesday'), trans('panel.wednesday'), trans('panel.thursday'), trans('panel.friday'), trans('panel.saturday'), trans('panel.sunday')] as $dayName)
            <div class="calendar-weekday">{{ $dayName }}</div>
        @endforeach

        {{-- DAYS --}}
        @foreach($calendarDays as $day)
            @php
                $isCurrentMonth = $day['isCurrentMonth'];
                $isToday = $day['isToday'];
                $bars = $day['bars'];
            @endphp

            <div class="calendar-day {{ !$isCurrentMonth ? 'calendar-day-disabled' : '' }} {{ $isToday ? 'calendar-day-today' : '' }}">

                <div class="d-flex align-items-center justify-content-between">
                    <span class="font-weight-bold">{{ $day['date']->day }}</span>

                    @if($showNumberOf)
                        <span class="badge badge-light">{{ count($bars) }}</span>
                    @endif
                </div>

                @if(count($bars))
                    <div class="mt-8 d-flex flex-column gap-4">
                        @foreach($bars as $bar)
                            <div class="calendar-bar calendar-bar--{{ $bar['type'] }}"
                                 title="{{ $bar['title'] }}">
                                <span class="calendar-bar__title">{{ truncate($bar['title'], 16) }}</span>

                                @if($showValues)
                                    <span class="calendar-bar__price">{{ handlePrice($bar['price']) }}</span>
                                @endif

                                @if(!empty($bar['time']))
                                    <span class="calendar-bar__time">{{ $bar['time'] }}</span>
                                @endif
                            </div>
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
    gap:8px;
}

.calendar-weekday{
    padding:10px;
    text-align:center;
    font-weight:700;
    font-size:13px;
    background:#f8f9fa;
    border-radius:10px;
}

.calendar-day{
    min-height:140px;
    border:1px solid #e5e7eb;
    border-radius:12px;
    padding:10px;
    background:#fff;
}

.calendar-day-disabled{
    opacity:.35;
}

.calendar-day-today{
    border:2px solid #4361ee;
}

.calendar-bar{
    display:flex;
    flex-direction:column;
    padding:4px 8px;
    border-radius:6px;
    font-size:11px;
    line-height:1.3;
    color:#fff;
    overflow:hidden;
}

.calendar-bar--available{
    background:#6b7280;
}

.calendar-bar--purchased{
    background:#374151;
}

.calendar-bar__title{
    font-weight:600;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}

.calendar-bar__price,
.calendar-bar__time{
    opacity:.85;
}

.booking-cal-switch{
    position:relative;
    display:inline-block;
    width:40px;
    height:22px;
    flex-shrink:0;
}

.booking-cal-switch input{
    opacity:0;
    width:0;
    height:0;
    position:absolute;
}

.booking-cal-switch-slider{
    position:absolute;
    inset:0;
    background:#ccc;
    border-radius:22px;
    cursor:pointer;
    transition:background .2s;
}

.booking-cal-switch-slider:before{
    content:'';
    position:absolute;
    height:16px;
    width:16px;
    left:3px;
    bottom:3px;
    background:#fff;
    border-radius:50%;
    transition:transform .2s;
}

.booking-cal-switch input:checked + .booking-cal-switch-slider{
    background:#2196F3;
}

.booking-cal-switch input:checked + .booking-cal-switch-slider:before{
    transform:translateX(18px);
}

.select-sm{
    width:auto;
    display:inline-block;
}
</style>

@endpush

@push('scripts_bottom')
<script>
    if (window.jQuery && jQuery.fn.select2) {
        jQuery('.select2-bookings').select2({
            placeholder: "{{ trans('panel.select_bookings') }}",
            closeOnSelect: false
        });
    }
</script>
@endpush
