<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSeason;
use Illuminate\Http\Request;

class BookingSeasonController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_season');

        removeContentLocale();

        $seasons = BookingSeason::orderBy('id', 'desc')->paginate(20);
        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle' => trans('admin/main.admin_booking_season'),
            'seasons'   => $seasons,
            'bookings'  => $bookings,
        ];

        return view('admin.booking.season', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_season_create');

        $this->validate($request, [
            'booking_id'     => 'required|exists:bookings,id',
            'name'           => 'required|string|max:255',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'price_modifier' => 'required|numeric|min:0',
            'modifier_type'  => 'required|in:multiplier,fixed',
        ]);

        BookingSeason::create([
            'booking_id'     => $request->booking_id,
            'name'           => $request->name,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'price_modifier' => $request->price_modifier,
            'modifier_type'  => $request->modifier_type,
            'status'         => !empty($request->status) ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/season'))
            ->with('success', trans('admin/main.booking_season_created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_season_edit');

        $editSeason = BookingSeason::findOrFail($id);
        $seasons    = BookingSeason::orderBy('id', 'desc')->paginate(20);
        $bookings   = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle'  => trans('admin/main.admin_booking_season'),
            'seasons'    => $seasons,
            'editSeason' => $editSeason,
            'bookings'   => $bookings,
        ];

        return view('admin.booking.season', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_season_edit');

        $season = BookingSeason::findOrFail($id);

        $this->validate($request, [
            'booking_id'     => 'required|exists:bookings,id',
            'name'           => 'required|string|max:255',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'price_modifier' => 'required|numeric|min:0',
            'modifier_type'  => 'required|in:multiplier,fixed',
        ]);

        $season->update([
            'booking_id'     => $request->booking_id,
            'name'           => $request->name,
            'start_date'     => $request->start_date,
            'end_date'       => $request->end_date,
            'price_modifier' => $request->price_modifier,
            'modifier_type'  => $request->modifier_type,
            'status'         => !empty($request->status) ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/season'))
            ->with('success', trans('admin/main.booking_season_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_season_delete');

        $season = BookingSeason::findOrFail($id);
        $season->delete();

        return redirect(getAdminPanelUrl('/booking/season'))
            ->with('success', trans('admin/main.booking_season_deleted_successfully'));
    }
}