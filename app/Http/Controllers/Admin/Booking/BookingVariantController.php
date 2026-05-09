<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingVariant;

class BookingVariantController extends Controller
{
    // ─────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────
    public function index()
    {
        $this->authorize('admin_booking_variants');

        removeContentLocale();

        $variants = BookingVariant::with(['booking'])
            ->orderBy('sort_order')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        return view('admin.booking.variant', [
            'pageTitle'   => 'Booking Variants',
            'variants'    => $variants,
            'bookings'    => $bookings,
            'editVariant' => null,
        ]);
    }

    // ─────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('admin_booking_variants_create');

        // checkbox normalize
        $request->merge([
            'affects_availability' => $request->has('affects_availability') ? 1 : 0,
            'status'               => $request->has('status') ? 1 : 0,
        ]);

        // validation
        $validated = $request->validate([
            'booking_id'           => 'required|exists:bookings,id',
            'name'                 => 'required|string|max:255',
            'options'              => 'required|array',
            'options.*'            => 'required|string|max:255',
            'price_modifier'       => 'nullable|numeric',
            'affects_availability' => 'required|boolean',
            'status'               => 'required|boolean',
            'sort_order'           => 'nullable|integer',
        ]);

        // create
        BookingVariant::create($validated);

        return redirect(getAdminPanelUrl('/booking/variant'))
            ->with('success', 'Variant created successfully');
    }

    // ─────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────
    public function edit($id)
    {
        $this->authorize('admin_booking_variants_edit');

        $editVariant = BookingVariant::findOrFail($id);

        $variants = BookingVariant::with(['booking'])
            ->orderBy('sort_order')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        return view('admin.booking.variant', [
            'pageTitle'   => 'Edit Variant',
            'variants'    => $variants,
            'bookings'    => $bookings,
            'editVariant' => $editVariant,
        ]);
    }

    // ─────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_variants_edit');

        $variant = BookingVariant::findOrFail($id);

        $request->merge([
            'affects_availability' => $request->has('affects_availability') ? 1 : 0,
            'status'               => $request->has('status') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'booking_id'           => 'required|exists:bookings,id',
            'name'                 => 'required|string|max:255',
            'options'              => 'required|array',
            'options.*'            => 'required|string|max:255',
            'price_modifier'       => 'nullable|numeric',
            'affects_availability' => 'required|boolean',
            'status'               => 'required|boolean',
            'sort_order'           => 'nullable|integer',
        ]);

        $variant->update($validated);

        return redirect(getAdminPanelUrl('/booking/variant'))
            ->with('success', 'Variant updated successfully');
    }

    // ─────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────
    public function delete($id)
    {
        $this->authorize('admin_booking_variants_delete');

        $variant = BookingVariant::findOrFail($id);
        $variant->delete();

        return redirect(getAdminPanelUrl('/booking/variant'))
            ->with('success', 'Variant deleted successfully');
    }
}