<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRatePlan;
use Illuminate\Http\Request;

class BookingRatePlanController extends Controller
{
    /**
     * Display a listing of rate plans
     */
    public function index()
    {
        $this->authorize('admin_booking_rate_plan');

        removeContentLocale();

        $bookingRatePlans = BookingRatePlan::with('booking')->paginate(20);
        $bookings = Booking::select('id', 'title')->orderBy('title')->get();

        $data = [
            'pageTitle'        => trans('admin/main.booking_rate_plan'),
            'bookingRatePlans' => $bookingRatePlans,
            'bookings'         => $bookings,
        ];

        return view('admin.booking.rateplan', $data);
    }

    /**
     * Store a new rate plan
     */
    public function store(Request $request)
    {
        $this->authorize('admin_booking_rate_plan_create');

        $this->validate($request, [
            'name'             => 'required|string|max:255',
            'type'             => 'nullable|string|max:255',
            'price'            => 'nullable|numeric|min:0',
            'price_unit'       => 'nullable|integer|min:0',
            'calculation_type' => 'nullable|numeric|min:0',
            'priority'         => 'nullable|integer|min:0',
            'conditions'       => 'nullable|array',
            'status'           => 'nullable',
            'booking_id'       => 'nullable|exists:bookings,id',
        ]);

        $data = $request->all();

        BookingRatePlan::create([
            'booking_id'       => $data['booking_id'] ?? null,
            'name'             => $data['name'],
            'type'             => $data['type'] ?? null,
            'price_unit'       => $data['price_unit'] ?? null,
            'price'            => $data['price'] ?? null,
            'calculation_type' => $data['calculation_type'] ?? 0,
            'priority'         => $data['priority'] ?? null,
            'conditions'       => !empty($data['conditions']) ? $data['conditions'] : null,
            'status'           => !empty($data['status']) ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/rate'))
            ->with('success', trans('admin/main.rate_plan_created_successfully'));
    }

    /**
     * Show edit form for a rate plan
     */
    public function edit($id)
    {
        $this->authorize('admin_booking_rate_plan_edit');

        $editRatePlan    = BookingRatePlan::findOrFail($id);
        $bookingRatePlans = BookingRatePlan::with('booking')->paginate(20);
        $bookings        = Booking::select('id', 'title')->orderBy('title')->get();

        $data = [
            'pageTitle'        => trans('admin/main.booking_rate_plan'),
            'bookingRatePlans' => $bookingRatePlans,
            'editRatePlan'     => $editRatePlan,
            'bookings'         => $bookings,
        ];

        return view('admin.booking.rateplan', $data);
    }

    /**
     * Update an existing rate plan
     */
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_rate_plan_edit');

        $ratePlan = BookingRatePlan::findOrFail($id);

        $this->validate($request, [
            'name'             => 'required|string|max:255',
            'type'             => 'nullable|string|max:255',
            'price'            => 'nullable|numeric|min:0',
            'price_unit'       => 'nullable|integer|min:0',
            'calculation_type' => 'nullable|numeric|min:0',
            'priority'         => 'nullable|integer|min:0',
            'conditions'       => 'nullable|array',
            'status'           => 'nullable',
            'booking_id'       => 'nullable|exists:bookings,id',
        ]);

        $data = $request->all();

        $ratePlan->update([
            'booking_id'       => $data['booking_id'] ?? null,
            'name'             => $data['name'],
            'type'             => $data['type'] ?? null,
            'price_unit'       => $data['price_unit'] ?? null,
            'price'            => $data['price'] ?? null,
            'calculation_type' => $data['calculation_type'] ?? 0,
            'priority'         => $data['priority'] ?? null,
            'conditions'       => !empty($data['conditions']) ? $data['conditions'] : null,
            'status'           => !empty($data['status']) ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/rate'))
            ->with('success', trans('admin/main.rate_plan_updated_successfully'));
    }

    /**
     * Delete a rate plan
     */
    public function delete($id)
    {
        $this->authorize('admin_booking_rate_plan_delete');

        $ratePlan = BookingRatePlan::findOrFail($id);
        $ratePlan->delete();

        return redirect(getAdminPanelUrl('/booking/rate'))
            ->with('success', trans('admin/main.rate_plan_deleted_successfully'));
    }
}