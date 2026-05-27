<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingResource;
use Illuminate\Http\Request;

class BookingResourceController extends Controller
{
    /**
     * List all booking resources
     */
    public function index()
    {
        $this->authorize('admin_booking_resources');

        removeContentLocale();

        $bookingResources = BookingResource::orderBy('order')->paginate(20);
        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle' => trans('admin/main.booking_resources'),
            'bookingResources' => $bookingResources,
            'bookings' => $bookings,
        ];

        return view('admin.booking.resources', $data);
    }

    /**
     * Store a new booking resource
     */
    public function store(Request $request)
    {
        $this->authorize('admin_booking_resources_create');

        $this->validate($request, [
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'extra_price' => 'nullable|numeric|min:0',
            'attribute_keys' => 'nullable|array',
            'attribute_keys.*' => 'nullable|string|max:255',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'nullable|string|max:1000',
            'image' => 'nullable|string',
            'booking_id' => 'required|exists:bookings,id',
        ]);
        $data = $request->all();
        $nextOrder = max((int) BookingResource::max('order'), BookingResource::count()) + 1;

        BookingResource::create([
            'booking_id' => $data['booking_id'],
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'extra_price' => $data['extra_price'] ?? 0,
            'attributes' => $this->prepareAttributes($data),
            'image' => $data['image'] ?? null,
            'status' => !empty($data['status']) ? 1 : 0,
            'order' => $nextOrder,
        ]);

        return redirect(getAdminPanelUrl('/booking/resources'))
            ->with('success', trans('admin/main.booking_resources_created_successfully'));
    }

    /**
     * Show edit form for a resource
     */
    public function edit($id)
    {
        $this->authorize('admin_booking_resources_edit');

        $editResource = BookingResource::findOrFail($id);
        $bookingResources = BookingResource::orderBy('order')->paginate(20);
        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        $data = [
            'pageTitle' => trans('admin/main.booking_resources'),
            'bookingResources' => $bookingResources,
            'editResource' => $editResource,
            'bookings' => $bookings,
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
            'type' => 'required|string|max:255',
            'description' => 'nullable|string',
            'capacity' => 'nullable|integer|min:0',
            'extra_price' => 'nullable|numeric|min:0',
            'attribute_keys' => 'nullable|array',
            'attribute_keys.*' => 'nullable|string|max:255',
            'attribute_values' => 'nullable|array',
            'attribute_values.*' => 'nullable|string|max:1000',
            'image' => 'nullable|string',
            'booking_id' => 'required|exists:bookings,id',
        ]);
        $data = $request->all();

        $resource->update([
            'booking_id' => $data['booking_id'],
            'name' => $data['name'],
            'type' => $data['type'] ?? null,
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'extra_price' => $data['extra_price'] ?? 0,
            'attributes' => $this->prepareAttributes($data),
            'image' => $data['image'] ?? null,
            'status' => !empty($data['status']) ? 1 : 0,
            'order' => $resource->order,
        ]);

        return redirect(getAdminPanelUrl('/booking/resources'))
            ->with('success', trans('admin/main.booking_resources_updated_successfully'));
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
            ->with('success', trans('admin/main.booking_resources_deleted_successfully'));
    }

    private function prepareAttributes(array $data): ?array
    {
        $attributes = [];
        $keys = $data['attribute_keys'] ?? [];
        $values = $data['attribute_values'] ?? [];

        foreach ($keys as $index => $key) {
            $key = trim((string) $key);

            if ($key === '') {
                continue;
            }

            $attributes[$key] = $values[$index] ?? null;
        }

        return !empty($attributes) ? $attributes : null;
    }
}
