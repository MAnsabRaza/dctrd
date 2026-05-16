@extends('design_1.panel.layouts.panel')

@php
    $selectedBookingId = optional($selectedBooking)->id;
    $baseParams = array_filter([
        'booking_id' => $selectedBookingId,
        'view' => $viewMode,
    ]);

    $periodLabel = $viewMode === 'day'
        ? $currentDate->format('M d, Y')
        : ($viewMode === 'week'
            ? $rangeStart->format('M d') . ' - ' . $rangeEnd->format('M d, Y')
            : $currentDate->format('F Y'));

    $modeLabels = [
        'month' => 'Month',
        'week' => 'Week',
        'day' => 'Day',
    ];

    $weekDays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
@endphp

@push('styles_top')
    <style>
        .booking-calendar-toolbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            flex-wrap: wrap;
        }

        .booking-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(120px, 1fr));
            border-top: 1px solid var(--gray-200, #eceff3);
            border-left: 1px solid var(--gray-200, #eceff3);
        }

        .booking-calendar-grid.booking-calendar-grid--day {
            grid-template-columns: minmax(220px, 1fr);
        }

        .booking-calendar-weekday,
        .booking-calendar-day {
            border-right: 1px solid var(--gray-200, #eceff3);
            border-bottom: 1px solid var(--gray-200, #eceff3);
        }

        .booking-calendar-weekday {
            min-height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
            color: #667085;
            font-size: 12px;
            font-weight: 700;
        }

        .booking-calendar-day {
            min-height: 168px;
            padding: 10px;
            background: #fff;
        }

        .booking-calendar-day.booking-calendar-day--large {
            min-height: 320px;
        }

        .booking-calendar-day.is-outside {
            background: #f9fafb;
            color: #98a2b3;
        }

        .booking-calendar-day.is-today {
            box-shadow: inset 0 0 0 2px #1f3b64;
        }

        .booking-calendar-date {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            min-height: 24px;
        }

        .booking-calendar-slots {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-top: 10px;
        }

        .booking-calendar-slot {
            display: block;
            width: 100%;
            border: 0;
            border-radius: 6px;
            padding: 6px 8px;
            font-size: 12px;
            line-height: 1.35;
            text-align: left;
            white-space: normal;
        }

        .booking-calendar-slot small {
            display: block;
            font-size: 11px;
            opacity: .78;
            margin-top: 2px;
        }

        .booking-calendar-slot--available {
            background: #ecfdf3;
            color: #067647;
        }

        .booking-calendar-slot--booked {
            background: #eff8ff;
            color: #175cd3;
        }

        .booking-calendar-slot--pending {
            background: #fffaeb;
            color: #b54708;
        }

        .booking-calendar-slot--blocked {
            background: #fff1f3;
            color: #c01048;
        }

        .booking-calendar-slot--muted {
            background: #f2f4f7;
            color: #667085;
        }

        .booking-calendar-legend {
            display: flex;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .booking-calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #667085;
            font-size: 12px;
        }

        .booking-calendar-legend i {
            display: inline-block;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        @media (max-width: 991px) {
            .booking-calendar-grid {
                grid-template-columns: repeat(2, minmax(180px, 1fr));
            }

            .booking-calendar-weekday {
                display: none;
            }
        }

        @media (max-width: 575px) {
            .booking-calendar-grid {
                grid-template-columns: minmax(220px, 1fr);
            }
        }
    </style>
@endpush

@section('content')
    <div class="bg-white p-16 rounded-24 mt-20">
        <div class="booking-calendar-toolbar pb-16 border-bottom-gray-100">
            <div>
                <h3 class="font-16 font-weight-bold">Booking Calendar</h3>
                <p class="font-13 text-gray-500 mt-4">{{ $periodLabel }}</p>
            </div>

            <div class="d-flex align-items-center gap-8 flex-wrap">
                <a href="{{ route('panel.bookings.index') }}" class="btn btn-outline-secondary btn-sm">View bookings</a>
                @can('panel_bookings_create')
                    <a href="{{ route('panel.bookings.create') }}" class="btn btn-primary btn-sm">New booking</a>
                @endcan
            </div>
        </div>

        <div class="booking-calendar-toolbar py-16">
            <form action="{{ route('panel.bookings.calendar') }}" method="get" class="d-flex align-items-center gap-8 flex-wrap">
                <input type="hidden" name="view" value="{{ $viewMode }}">
                <input type="hidden" name="date" value="{{ $currentDate->toDateString() }}">

                <select name="booking_id" class="form-control" onchange="this.form.submit()" style="min-width: 260px;">
                    @forelse($bookings as $booking)
                        <option value="{{ $booking->id }}" {{ (int) $selectedBookingId === (int) $booking->id ? 'selected' : '' }}>
                            {{ $booking->title }}
                        </option>
                    @empty
                        <option value="">No bookings found</option>
                    @endforelse
                </select>
            </form>

            <div class="d-flex align-items-center gap-8 flex-wrap">
                <div class="btn-group btn-group-sm" role="group" aria-label="Calendar view">
                    @foreach($modeLabels as $mode => $label)
                        <a href="{{ route('panel.bookings.calendar', array_filter(['booking_id' => $selectedBookingId, 'view' => $mode, 'date' => $currentDate->toDateString()])) }}"
                           class="btn {{ $viewMode === $mode ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $label }}
                        </a>
                    @endforeach
                </div>

                <div class="btn-group btn-group-sm" role="group" aria-label="Calendar navigation">
                    <a href="{{ route('panel.bookings.calendar', array_merge($baseParams, ['date' => $prevDate->toDateString()])) }}" class="btn btn-outline-secondary">Prev</a>
                    <a href="{{ route('panel.bookings.calendar', array_merge($baseParams, ['date' => today()->toDateString()])) }}" class="btn btn-outline-secondary">Today</a>
                    <a href="{{ route('panel.bookings.calendar', array_merge($baseParams, ['date' => $nextDate->toDateString()])) }}" class="btn btn-outline-secondary">Next</a>
                </div>
            </div>
        </div>

        <div class="booking-calendar-legend mb-16">
            <span><i style="background:#12b76a"></i> Available</span>
            <span><i style="background:#2e90fa"></i> Booked</span>
            <span><i style="background:#f79009"></i> Pending</span>
            <span><i style="background:#f04438"></i> Blocked</span>
        </div>

        @if(empty($selectedBooking))
            @include('design_1.panel.includes.no-result',[
                'file_name' => 'calendar.svg',
                'title' => 'No bookings found',
                'hint' => 'Create a booking first, then its calendar will appear here.',
                'btn' => ['url' => route('panel.bookings.create'), 'text' => 'New booking']
            ])
        @else
            @if($viewMode !== 'day')
                <div class="booking-calendar-grid">
                    @foreach($weekDays as $weekDay)
                        <div class="booking-calendar-weekday">{{ $weekDay }}</div>
                    @endforeach
                </div>
            @endif

            <div class="booking-calendar-grid {{ $viewMode === 'day' ? 'booking-calendar-grid--day' : '' }}">
                @foreach($calendarDays as $day)
                    @php
                        $date = $day['date'];
                        $bookedSlots = $day['bookedSlots'];
                        $availableSlots = $day['availableSlots'];
                        $maxVisibleSlots = $viewMode === 'month' ? 4 : 18;
                    @endphp

                    <div class="booking-calendar-day {{ !$day['inRange'] ? 'is-outside' : '' }} {{ $day['isToday'] ? 'is-today' : '' }} {{ $viewMode !== 'month' ? 'booking-calendar-day--large' : '' }}">
                        <div class="booking-calendar-date">
                            <strong class="font-13">{{ $date->format($viewMode === 'month' ? 'j' : 'D, M j') }}</strong>
                            @if($day['isToday'])
                                <span class="badge badge-primary">Today</span>
                            @endif
                        </div>

                        <div class="booking-calendar-slots">
                            @if($day['isBlocked'])
                                <span class="booking-calendar-slot booking-calendar-slot--blocked">
                                    Blocked
                                    @if(!empty($day['blockReason']))
                                        <small>{{ $day['blockReason'] }}</small>
                                    @endif
                                </span>
                            @elseif($day['isPast'])
                                <span class="booking-calendar-slot booking-calendar-slot--muted">Past date</span>
                            @endif

                            @foreach($bookedSlots->take($maxVisibleSlots) as $item)
                                @php
                                    $statusClass = $item->status === 'pending' ? 'booking-calendar-slot--pending' : 'booking-calendar-slot--booked';
                                    $timeText = ($item->start_time and $item->end_time)
                                        ? substr($item->start_time, 0, 5) . ' - ' . substr($item->end_time, 0, 5)
                                        : 'Booked';
                                    $studentName = optional(optional($item->order)->user)->full_name;
                                @endphp

                                <span class="booking-calendar-slot {{ $statusClass }}">
                                    {{ $timeText }}
                                    <small>
                                        {{ ucfirst($item->status) }}
                                        @if(!empty($studentName))
                                            . {{ $studentName }}
                                        @endif
                                    </small>
                                </span>
                            @endforeach

                            @foreach($availableSlots->take(max(0, $maxVisibleSlots - $bookedSlots->take($maxVisibleSlots)->count())) as $slot)
                                <span class="booking-calendar-slot booking-calendar-slot--available">
                                    {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                                    <small>{{ $slot['slots_left'] }} available</small>
                                </span>
                            @endforeach

                            @if(!$day['isBlocked'] and !$day['isPast'] and $bookedSlots->isEmpty() and $availableSlots->isEmpty())
                                <span class="booking-calendar-slot booking-calendar-slot--available">Available date</span>
                            @endif

                            @php
                                $hiddenCount = max(0, $bookedSlots->count() + $availableSlots->count() - $maxVisibleSlots);
                            @endphp

                            @if($hiddenCount > 0)
                                <span class="font-12 text-gray-500">+{{ $hiddenCount }} more</span>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection
