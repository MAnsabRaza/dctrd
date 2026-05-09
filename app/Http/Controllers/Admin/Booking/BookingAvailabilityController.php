<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingAvailability;
use App\Models\BookingResource;
use Illuminate\Http\Request;

class BookingAvailabilityController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_availability');

        removeContentLocale();

        $availabilities  = BookingAvailability::with(['booking', 'resource'])
                            ->orderBy('id', 'desc')
                            ->paginate(20);

        $bookings        = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $bookingResources = BookingResource::orderBy('id', 'desc')->get(['id', 'name']);

        $data = [
            'pageTitle'        => trans('admin/main.admin_booking_availability'),
            'availabilities'   => $availabilities,
            'bookings'         => $bookings,
            'bookingResources' => $bookingResources,
        ];

        return view('admin.booking.availability', $data);
    }

  public function store(Request $request)
{
    $this->authorize('admin_booking_availability_create');

    // Normalize checkbox value BEFORE validation
    $request->merge([
        'is_available' => $request->has('is_available') ? 1 : 0,
    ]);

    $this->validate($request, [
        'booking_id'      => 'required|exists:bookings,id',
        'resource_id'     => 'nullable|exists:booking_resources,id',
        'date'            => 'required|date',
        'is_available'    => 'required|boolean',  // now safely 1 or 0
        'slots_available' => 'nullable|integer|min:0',
        'price_override'  => 'nullable|numeric|min:0',
        'close_reason'    => 'nullable|string|max:255',
    ]);

    BookingAvailability::create([
        'booking_id'      => $request->booking_id,
        'resource_id'     => $request->resource_id ?: null,
        'date'            => $request->date,
        'is_available'    => $request->is_available,  // already 1 or 0
        'slots_available' => $request->slots_available ?: null,
        'price_override'  => $request->price_override ?: null,
        'close_reason'    => $request->close_reason,
    ]);

    return redirect(getAdminPanelUrl('/booking/availability'))
        ->with('success', trans('admin/main.booking_availability_created_successfully'));
}

    public function edit($id)
    {
        $this->authorize('admin_booking_availability_edit');

        $editAvailability = BookingAvailability::findOrFail($id);
        $availabilities   = BookingAvailability::with(['booking', 'resource'])
                             ->orderBy('id', 'desc')
                             ->paginate(20);
        $bookings         = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $bookingResources = BookingResource::orderBy('id', 'desc')->get(['id', 'name']);

        $data = [
            'pageTitle'         => trans('admin/main.admin_booking_availability'),
            'availabilities'    => $availabilities,
            'editAvailability'  => $editAvailability,
            'bookings'          => $bookings,
            'bookingResources'  => $bookingResources,
        ];

        return view('admin.booking.availability', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_availability_edit');

        $availability = BookingAvailability::findOrFail($id);

        $this->validate($request, [
            'booking_id'      => 'required|exists:bookings,id',
            'resource_id'     => 'nullable|exists:booking_resources,id',
            'date'            => 'required|date',
            'is_available'    => 'nullable|boolean',
            'slots_available' => 'nullable|integer|min:0',
            'price_override'  => 'nullable|numeric|min:0',
            'close_reason'    => 'nullable|string|max:255',
        ]);

        $availability->update([
            'booking_id'      => $request->booking_id,
            'resource_id'     => $request->resource_id ?: null,
            'date'            => $request->date,
            'is_available'    => $request->has('is_available') ? 1 : 0,
            'slots_available' => $request->slots_available,
            'price_override'  => $request->price_override,
            'close_reason'    => $request->close_reason,
        ]);

        return redirect(getAdminPanelUrl('/booking/availability'))
            ->with('success', trans('admin/main.booking_availability_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_availability_delete');

        $availability = BookingAvailability::findOrFail($id);
        $availability->delete();

        return redirect(getAdminPanelUrl('/booking/availability'))
            ->with('success', trans('admin/main.booking_availability_deleted_successfully'));
    }
}