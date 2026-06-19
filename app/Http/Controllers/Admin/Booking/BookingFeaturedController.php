<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingFeatured;
use App\Models\User;
use Illuminate\Http\Request;

class BookingFeaturedController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_featured');

        $items = BookingFeatured::with(['booking', 'user'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('admin.booking.booking_featured', [
            'pageTitle' => trans('admin/main.booking_featured'),
            'items' => $items,
        ]);
    }

    public function create()
    {
        $this->authorize('admin_booking_featured_create');

        $bookings = Booking::orderBy('id', 'desc')->pluck('title', 'id');
        $users = User::orderBy('id', 'desc')->pluck('full_name', 'id');

        return view('admin.booking.booking_featured', [
            'pageTitle' => trans('admin/main.new_booking_featured'),
            'bookings' => $bookings,
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_featured_create');

        $this->validate($request, [
            'booking_id' => 'required|exists:bookings,id',
            'title' => 'required|string|max:255',
            'page' => 'required|string',
        ]);

        $data = $request->only(['language', 'user_id', 'booking_id', 'page', 'title', 'description', 'status']);
        $data['status'] = !empty($data['status']);

        BookingFeatured::create($data);

        return back()->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_featured_edit');

        $item = BookingFeatured::findOrFail($id);
        $bookings = Booking::orderBy('id', 'desc')->pluck('title', 'id');
        $users = User::orderBy('id', 'desc')->pluck('full_name', 'id');

        return view('admin.booking.booking_featured', [
            'pageTitle' => trans('admin/main.edit_booking_featured'),
            'editItem' => $item,
            'bookings' => $bookings,
            'users' => $users,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_featured_edit');

        $this->validate($request, [
            'booking_id' => 'required|exists:bookings,id',
            'title' => 'required|string|max:255',
            'page' => 'required|string',
        ]);

        $item = BookingFeatured::findOrFail($id);

        $data = $request->only(['language', 'user_id', 'booking_id', 'page', 'title', 'description', 'status']);
        $data['status'] = !empty($data['status']);

        $item->update($data);

        return redirect(getAdminPanelUrl().'/booking/featured')->with('success', trans('admin/main.updated_successfully'));
    }

    public function destroy($id)
    {
        $this->authorize('admin_booking_featured_delete');

        BookingFeatured::findOrFail($id)->delete();

        return back()->with('success', trans('admin/main.deleted_successfully'));
    }
}
