<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAvailability;
use App\Models\BookingOrderItem;
use App\Models\BookingTimeSlot;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class BookingCalendarController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('panel_bookings_calendar');

        $user = auth()->user();

        if ($user->isUser()) {
            abort(404);
        }

        $bookings = Booking::query()
            ->where('creator_id', $user->id)
            ->orderBy('title')
            ->get(['id', 'title', 'creator_id', 'price', 'capacity']);

        $selectedBookingId = $request->get('booking_id') ?: optional($bookings->first())->id;
        $selectedBooking = $selectedBookingId
            ? Booking::query()->where('creator_id', $user->id)->find($selectedBookingId)
            : null;

        if (!empty($selectedBookingId) and empty($selectedBooking)) {
            abort(404);
        }

        $viewMode = $request->get('view', 'month');
        if (!in_array($viewMode, ['month', 'week', 'day'])) {
            $viewMode = 'month';
        }

        $currentDate = $this->parseDate($request->get('date'));
        [$rangeStart, $rangeEnd, $gridStart, $gridEnd] = $this->getDateRange($viewMode, $currentDate);

        $calendarDays = collect();
        if (!empty($selectedBooking)) {
            $calendarDays = $this->buildCalendarDays($selectedBooking, $gridStart, $gridEnd, $rangeStart, $rangeEnd);
        }

        $data = [
            'pageTitle' => 'Booking Calendar',
            'breadcrumbs' => [
                ['text' => trans('update.platform'), 'url' => '/'],
                ['text' => trans('panel.dashboard'), 'url' => '/panel'],
                ['text' => trans('panel.bookings'), 'url' => route('panel.bookings.index')],
                ['text' => 'Calendar', 'url' => null],
            ],
            'bookings' => $bookings,
            'selectedBooking' => $selectedBooking,
            'viewMode' => $viewMode,
            'currentDate' => $currentDate,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'gridStart' => $gridStart,
            'gridEnd' => $gridEnd,
            'calendarDays' => $calendarDays,
            'prevDate' => $this->shiftDate($viewMode, $currentDate, -1),
            'nextDate' => $this->shiftDate($viewMode, $currentDate, 1),
        ];

        return view('design_1.panel.bookings.calendar.index', $data);
    }

    private function parseDate(?string $date): Carbon
    {
        try {
            return !empty($date) ? Carbon::parse($date)->startOfDay() : today();
        } catch (\Throwable $exception) {
            return today();
        }
    }

    private function getDateRange(string $viewMode, Carbon $currentDate): array
    {
        if ($viewMode === 'day') {
            $rangeStart = $currentDate->copy()->startOfDay();
            $rangeEnd = $currentDate->copy()->endOfDay();
        } elseif ($viewMode === 'week') {
            $rangeStart = $currentDate->copy()->startOfWeek(Carbon::SUNDAY);
            $rangeEnd = $currentDate->copy()->endOfWeek(Carbon::SATURDAY);
        } else {
            $rangeStart = $currentDate->copy()->startOfMonth();
            $rangeEnd = $currentDate->copy()->endOfMonth();
        }

        $gridStart = $viewMode === 'month'
            ? $rangeStart->copy()->startOfWeek(Carbon::SUNDAY)
            : $rangeStart->copy();

        $gridEnd = $viewMode === 'month'
            ? $rangeEnd->copy()->endOfWeek(Carbon::SATURDAY)
            : $rangeEnd->copy();

        return [$rangeStart, $rangeEnd, $gridStart, $gridEnd];
    }

    private function shiftDate(string $viewMode, Carbon $date, int $amount): Carbon
    {
        if ($viewMode === 'day') {
            return $date->copy()->addDays($amount);
        }

        if ($viewMode === 'week') {
            return $date->copy()->addWeeks($amount);
        }

        return $date->copy()->addMonthsNoOverflow($amount);
    }

    private function buildCalendarDays(Booking $booking, Carbon $gridStart, Carbon $gridEnd, Carbon $rangeStart, Carbon $rangeEnd): Collection
    {
        $availabilityByDate = BookingAvailability::query()
            ->where('booking_id', $booking->id)
            ->whereBetween('date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->get()
            ->groupBy(fn($availability) => $availability->date->toDateString());

        $bookedItemsByDate = BookingOrderItem::query()
            ->with(['order.user', 'resource'])
            ->where('booking_id', $booking->id)
            ->whereBetween('booking_date', [$gridStart->toDateString(), $gridEnd->toDateString()])
            ->whereIn('status', ['pending', 'confirmed'])
            ->get()
            ->groupBy(fn($item) => optional($item->booking_date)->toDateString());

        $slotTemplates = BookingTimeSlot::query()
            ->with('resource')
            ->where('booking_id', $booking->id)
            ->where('status', true)
            ->orderBy('start_time')
            ->get();

        $days = collect();

        foreach (CarbonPeriod::create($gridStart, $gridEnd) as $date) {
            $dateKey = $date->toDateString();
            $availabilityItems = $availabilityByDate->get($dateKey, collect());
            $bookedItems = $bookedItemsByDate->get($dateKey, collect());
            $isBlocked = $availabilityItems->contains(fn($availability) => !$availability->is_available);
            $blockReason = optional($availabilityItems->first(fn($availability) => !$availability->is_available))->close_reason;
            $isPast = $date->lt(today());

            $availableSlots = (!$isBlocked and !$isPast)
                ? $this->getAvailableSlotsForDate($slotTemplates, $bookedItems, $date)
                : collect();

            $days->push([
                'date' => $date->copy(),
                'dateKey' => $dateKey,
                'inRange' => $date->gte($rangeStart) && $date->lte($rangeEnd),
                'isToday' => $date->isToday(),
                'isPast' => $isPast,
                'isBlocked' => $isBlocked,
                'blockReason' => $blockReason,
                'bookedSlots' => $bookedItems->sortBy('start_time')->values(),
                'availableSlots' => $availableSlots,
            ]);
        }

        return $days;
    }

    private function getAvailableSlotsForDate(Collection $slotTemplates, Collection $bookedItems, Carbon $date): Collection
    {
        $slots = collect();

        foreach ($slotTemplates as $template) {
            if (!$this->templateAppliesToDate($template, $date)) {
                continue;
            }

            $start = Carbon::parse($date->toDateString() . ' ' . $template->start_time);
            $end = Carbon::parse($date->toDateString() . ' ' . $template->end_time);
            $duration = max(1, (int) $template->duration_minutes);
            $buffer = max(0, (int) $template->buffer_minutes);

            while ($start->copy()->addMinutes($duration)->lte($end)) {
                $slotEnd = $start->copy()->addMinutes($duration);
                $slotStartTime = $start->format('H:i');
                $slotEndTime = $slotEnd->format('H:i');
                $bookedCount = $bookedItems->filter(function ($item) use ($slotStartTime, $slotEndTime, $template) {
                    $itemStart = $item->start_time ? substr($item->start_time, 0, 5) : null;
                    $itemEnd = $item->end_time ? substr($item->end_time, 0, 5) : null;

                    if ($itemStart !== $slotStartTime or $itemEnd !== $slotEndTime) {
                        return false;
                    }

                    if (!empty($template->resource_id)) {
                        return (int) $item->resource_id === (int) $template->resource_id;
                    }

                    return true;
                })->count();

                $slotsLeft = max(0, ((int) $template->max_bookings) - $bookedCount);

                if ($slotsLeft > 0) {
                    $slots->push([
                        'start_time' => $slotStartTime,
                        'end_time' => $slotEndTime,
                        'slots_left' => $slotsLeft,
                        'resource' => optional($template->resource)->name,
                    ]);
                }

                $start->addMinutes($duration + $buffer);
            }
        }

        return $slots->values();
    }

    private function templateAppliesToDate(BookingTimeSlot $template, Carbon $date): bool
    {
        $days = $template->day_of_week;

        if (empty($days)) {
            return true;
        }

        if (!is_array($days)) {
            $decoded = json_decode($days, true);
            $days = is_array($decoded) ? $decoded : explode(',', $days);
        }

        $days = array_map('intval', $days);

        return in_array($date->isoWeekday(), $days) or in_array($date->dayOfWeek, $days);
    }
}
