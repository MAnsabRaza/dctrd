<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingRatePlan;
use Illuminate\Http\Request;

class BookingRatePlanController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_rate_plan');

        removeContentLocale();

        $bookingRatePlans = BookingRatePlan::with('booking')->paginate(20);
        $bookings         = Booking::select('id', 'title')->orderBy('title')->get();

        return view('admin.booking.rateplan', [
            'pageTitle'        => trans('admin/main.booking_rate_plan'),
            'bookingRatePlans' => $bookingRatePlans,
            'bookings'         => $bookings,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_rate_plan_create');

        $this->validate($request, [
            'name'             => 'required|string|max:255',
            'type'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'price_unit'       => 'required|string|max:50',
            'calculation_type' => 'required|string|in:fixed,percent_off,percent_of_base',
            'priority'         => 'nullable|integer|min:0',
            'booking_id'       => 'required|exists:bookings,id',
            'status'           => 'nullable',
        ]);

        BookingRatePlan::create([
            'booking_id'       => $request->booking_id,
            'name'             => $request->name,
            'type'             => $request->type,
            'price'            => $request->price,
            'price_unit'       => $request->price_unit,
            'calculation_type' => $request->calculation_type,
            'priority'         => $request->priority ?? 0,
            'conditions'       => $this->buildConditions($request),
            'status'           => $request->has('status') ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/rate'))
            ->with('success', trans('admin/main.rate_plan_created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_rate_plan_edit');

        $editRatePlan     = BookingRatePlan::findOrFail($id);
        $bookingRatePlans = BookingRatePlan::with('booking')->paginate(20);
        $bookings         = Booking::select('id', 'title')->orderBy('title')->get();

        return view('admin.booking.rateplan', [
            'pageTitle'        => trans('admin/main.booking_rate_plan'),
            'bookingRatePlans' => $bookingRatePlans,
            'editRatePlan'     => $editRatePlan,
            'bookings'         => $bookings,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_rate_plan_edit');

        $ratePlan = BookingRatePlan::findOrFail($id);

        $this->validate($request, [
            'name'             => 'required|string|max:255',
            'type'             => 'required|string|max:255',
            'price'            => 'required|numeric|min:0',
            'price_unit'       => 'required|string|max:50',
            'calculation_type' => 'required|string|in:fixed,percent_off,percent_of_base',
            'priority'         => 'nullable|integer|min:0',
            'booking_id'       => 'required|exists:bookings,id',
            'status'           => 'nullable',
        ]);

        $ratePlan->update([
            'booking_id'       => $request->booking_id,
            'name'             => $request->name,
            'type'             => $request->type,
            'price'            => $request->price,
            'price_unit'       => $request->price_unit,
            'calculation_type' => $request->calculation_type,
            'priority'         => $request->priority ?? 0,
            'conditions'       => $this->buildConditions($request),
            'status'           => $request->has('status') ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/rate'))
            ->with('success', trans('admin/main.rate_plan_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_rate_plan_delete');

        BookingRatePlan::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/rate'))
            ->with('success', trans('admin/main.rate_plan_deleted_successfully'));
    }

    /**
     * Convert condition_key[] + condition_value[] POST arrays to a JSON-ready array.
     * Value auto-detection:
     *   "[6,7]" -> JSON array
     *   "3"     -> integer/float
     *   "text"  -> plain string
     */
    private function buildConditions(Request $request): ?array
    {
        $keys   = $request->input('condition_key', []);
        $values = $request->input('condition_value', []);

        if (empty($keys)) {
            return null;
        }

        $conditions = [];
        foreach ($keys as $i => $key) {
            $key = trim($key);
            if ($key === '') continue;

            $raw     = trim($values[$i] ?? '');
            $decoded = json_decode($raw, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $conditions[$key] = $decoded;
            } elseif (is_numeric($raw)) {
                $conditions[$key] = $raw + 0;
            } else {
                $conditions[$key] = $raw;
            }
        }

        return empty($conditions) ? null : $conditions;
    }
}