<?php
// App\Http\Controllers\Admin\Booking\BookingSpecificationValueController.php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingSpecification;
use App\Models\BookingSpecificationValue;
use Illuminate\Http\Request;

class BookingSpecificationValueController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_specification_value');

        removeContentLocale();

        $bookings       = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $specifications = BookingSpecification::active()->ordered()->get(['id', 'title']);

        $specificationValues = BookingSpecificationValue::with(['booking', 'specification'])
            ->paginate(20);

        return view('admin.booking.specificationValue', [
            'pageTitle'          => 'Booking Specification Values',
            'bookings'           => $bookings,
            'specifications'     => $specifications,
            'specificationValues' => $specificationValues,
            'editSpecificationValue' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_specification_value_create');

        $validated = $request->validate([
            'booking_id'       => 'required|exists:bookings,id',
            'specification_id' => 'required|exists:booking_specifications,id',
            'value'            => 'required|string|max:1000',
        ]);

        BookingSpecificationValue::create($validated);

        return redirect(getAdminPanelUrl('/booking/specificationValue'))
            ->with('success', 'Specification value created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_specification_value_edit');

        $editSpecificationValue = BookingSpecificationValue::with(['booking', 'specification'])
            ->findOrFail($id);

        $bookings       = Booking::orderBy('id', 'desc')->get(['id', 'title']);
        $specifications = BookingSpecification::active()->ordered()->get(['id', 'title']);

        $specificationValues = BookingSpecificationValue::with(['booking', 'specification'])
            ->paginate(20);

        return view('admin.booking.specificationValue', [
            'pageTitle'              => 'Edit Specification Value',
            'bookings'               => $bookings,
            'specifications'         => $specifications,
            'specificationValues'    => $specificationValues,
            'editSpecificationValue' => $editSpecificationValue,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_specification_value_edit');

        $specificationValue = BookingSpecificationValue::findOrFail($id);

        $validated = $request->validate([
            'booking_id'       => 'required|exists:bookings,id',
            'specification_id' => 'required|exists:booking_specifications,id',
            'value'            => 'required|string|max:1000',
        ]);

        $specificationValue->update($validated);

        return redirect(getAdminPanelUrl('/booking/specificationValue'))
            ->with('success', 'Specification value updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_specification_value_delete');

        BookingSpecificationValue::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/specificationValue'))
            ->with('success', 'Specification value deleted successfully.');
    }
}