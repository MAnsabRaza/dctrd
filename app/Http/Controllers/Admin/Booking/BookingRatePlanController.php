<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingRatePlan;
use Illuminate\Http\Request;

class BookingRatePlanController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_resources');

        removeContentLocale();

        $bookingRatePlan = BookingRatePlan::all()->paginate(20);

        $data = [
            'pageTitle' => trans('admin/main.booking_rate_plan'),
            'bookingRatePlan' => $bookingRatePlan,
        ];

        return view('admin.booking.rate_plan', $data);
    }

    /**
     * Store a new booking resource
     */
    public function store(Request $request)
    {
        $this->authorize('admin_booking_rate_plan_create');

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'price' => 'nullable|string',
            'price_unit' => 'nullable|integer|min:0',
            'calculation_type' => 'nullable|numeric|min:0',
            'priority' => 'nullable|json',
            'conditions' => 'nullable|integer|min:0',
            'status'=>'nullable|integer|min:0',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);
        $data = $request->all();

        BookingRatePlan::create([
            'booking_id' => $data['booking_id'] ?? null,
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'price_unit' => $data['price_unit'] ?? null,
            'price' => $data['price'] ?? null,
            'calculation_type' => $data['calculation_type'] ?? 0,
            'priority' => $data['priority'] ?? null,
            'conditions' => $data['conditions'] ?? null,
            'status' => !empty($data['status']) ? 1 : 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/rateplan'))
            ->with('success', trans('admin/main.rate_plan_created_successfully'));
    }

    /**
     * Show edit form for a resource
     */
    public function edit($id)
    {
        $this->authorize('admin_booking_resources_edit');

        $editResource = BookingResource::findOrFail($id);
        $bookingResources = BookingResource::orderBy('sort_order')->paginate(20);

        $data = [
            'pageTitle' => trans('admin/main.booking_resources'),
            'bookingResources' => $bookingResources,
            'editResource' => $editResource,
        ];

        return view('admin.booking.resources', $data);
    }

    /**
     * Update an existing resource
     */
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_resources_edit');

        $resource = BookingResource::findOrFail($id);

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'extra_price' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|json',
            'image' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'booking_id' => 'nullable|exists:bookings,id',
        ]);
        $data = $request->all();

        $resource->update([
            'booking_id' => $data['booking_id'] ?? null,
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'extra_price' => $data['extra_price'] ?? 0,
            'attributes' => $data['attributes'] ?? null,
            'image' => $data['image'] ?? null,
            'status' => !empty($data['status']) ? 1 : 0,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return redirect(getAdminPanelUrl('/booking/resources'))
            ->with('success', trans('admin/main.resource_updated_successfully'));
    }

    /**
     * Delete a resource
     */
    public function delete($id)
    {
        $this->authorize('admin_booking_resources_delete');

        $resource = BookingResource::findOrFail($id);
        $resource->delete();

        return redirect(getAdminPanelUrl('/booking/resources'))
            ->with('success', trans('admin/main.resource_deleted_successfully'));
    }
}
