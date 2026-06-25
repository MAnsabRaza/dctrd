<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingResource;
use App\Models\BookingTimeSlot;
use Illuminate\Http\Request;

class BookingTimeSlotController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_time_slots');

        removeContentLocale();

        $timeSlots = BookingTimeSlot::with(['booking', 'resource'])
            ->orderBy('booking_id')
            ->orderBy('resource_id')
            ->orderBy('start_time')
            ->paginate(20);

        $bookings  = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $resources = BookingResource::orderBy('name')->get(['id', 'booking_id', 'name']);

        return view('admin.booking.time-slot', [
            'pageTitle' => 'Booking Time Slots',
            'timeSlots' => $timeSlots,
            'bookings'  => $bookings,
            'resources' => $resources,
            'editSlot'  => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_time_slots_create');

        $request->merge([
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'booking_id'       => 'required|exists:bookings,id',
            'resource_id'      => 'nullable|exists:booking_resources,id',
            'day_of_week'      => 'required|array|min:1',
            'day_of_week.*'    => 'required|in:1,2,3,4,5,6,7',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'buffer_minutes'   => 'nullable|integer|min:0',
            'max_bookings'     => 'required|integer|min:1',
            'status'           => 'required|boolean',
        ]);

        $validated['resource_id']      = !empty($validated['resource_id']) ? $validated['resource_id'] : null;
        $validated['buffer_minutes']   = isset($validated['buffer_minutes']) ? (int) $validated['buffer_minutes'] : 0;
        $validated['duration_minutes'] = (int) $validated['duration_minutes'];
        $validated['max_bookings']     = (int) $validated['max_bookings'];
        $validated['status']           = (int) $validated['status'];

        if (!empty($validated['resource_id']) &&
            !BookingResource::where('id', $validated['resource_id'])
                ->where('booking_id', $validated['booking_id'])
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['resource_id' => 'Selected resource does not belong to the selected booking.']);
        }

        $validated['day_of_week'] = array_values(array_unique(array_map('intval', $validated['day_of_week'])));

        BookingTimeSlot::create($validated);

        return redirect(getAdminPanelUrl('/booking/time-slot'))
            ->with('success', 'Time slot created successfully.');
    }

    public function edit(Request $request, $id)
    {
        $this->authorize('admin_booking_time_slots_edit');

        $editSlot = BookingTimeSlot::findOrFail($id);

        $timeSlots = BookingTimeSlot::with(['booking', 'resource'])
            ->orderBy('booking_id')
            ->orderBy('resource_id')
            ->orderBy('start_time')
            ->paginate(20);

        $bookings  = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $resources = BookingResource::orderBy('name')->get(['id', 'booking_id', 'name']);

        return view('admin.booking.time-slot', [
            'pageTitle' => 'Edit Booking Time Slot',
            'timeSlots' => $timeSlots,
            'bookings'  => $bookings,
            'resources' => $resources,
            'editSlot'  => $editSlot,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_time_slots_edit');

        $slot = BookingTimeSlot::findOrFail($id);

        $request->merge([
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'booking_id'       => 'required|exists:bookings,id',
            'resource_id'      => 'nullable|exists:booking_resources,id',
            'day_of_week'      => 'required|array|min:1',
            'day_of_week.*'    => 'required|in:1,2,3,4,5,6,7',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'duration_minutes' => 'required|integer|min:1',
            'buffer_minutes'   => 'nullable|integer|min:0',
            'max_bookings'     => 'required|integer|min:1',
            'status'           => 'required|boolean',
        ]);

        $validated['resource_id']      = !empty($validated['resource_id']) ? $validated['resource_id'] : null;
        $validated['buffer_minutes']   = isset($validated['buffer_minutes']) ? (int) $validated['buffer_minutes'] : 0;
        $validated['duration_minutes'] = (int) $validated['duration_minutes'];
        $validated['max_bookings']     = (int) $validated['max_bookings'];
        $validated['status']           = (int) $validated['status'];

        if (!empty($validated['resource_id']) &&
            !BookingResource::where('id', $validated['resource_id'])
                ->where('booking_id', $validated['booking_id'])
                ->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors(['resource_id' => 'Selected resource does not belong to the selected booking.']);
        }

        $validated['day_of_week'] = array_values(array_unique(array_map('intval', $validated['day_of_week'])));

        $slot->update($validated);

        return redirect(getAdminPanelUrl('/booking/time-slot'))
            ->with('success', 'Time slot updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_time_slots_delete');

        BookingTimeSlot::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/time-slot'))
            ->with('success', 'Time slot deleted successfully.');
    }
}