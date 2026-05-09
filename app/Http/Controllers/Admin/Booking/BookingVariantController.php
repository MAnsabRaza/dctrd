<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\BookingVariant;

class BookingVariantController extends Controller
{
    // ─────────────────────────────────────────────────────────────────────
    // LIST
    // ─────────────────────────────────────────────────────────────────────
    public function index()
    {
        $this->authorize('admin_booking_variants');

        removeContentLocale();

        $variants = BookingVariant::with(['booking'])
            ->orderBy('sort_order')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        // Next auto sort order — max existing + 1
        $nextSortOrder = (BookingVariant::max('sort_order') ?? 0) + 1;

        return view('admin.booking.variant', [
            'pageTitle'     => 'Booking Variants',
            'variants'      => $variants,
            'bookings'      => $bookings,
            'editVariant'   => null,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // STORE
    // ─────────────────────────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->authorize('admin_booking_variants_create');

        // ── Normalize checkboxes ──────────────────────────────────────────
        $request->merge([
            'affects_availability' => $request->has('affects_availability') ? 1 : 0,
            'status'               => $request->has('status') ? 1 : 0,
        ]);

        // ── Validation ───────────────────────────────────────────────────
        $validated = $request->validate([
            'booking_id'           => 'required|exists:bookings,id',
            'name'                 => 'required|string|max:255',
            'options'              => 'required|array|min:1',
            'options.*'            => 'required|string|max:255',
            'price_modifier'       => 'nullable|numeric',
            'affects_availability' => 'required|boolean',
            'status'               => 'required|boolean',
            'sort_order'           => 'nullable|integer|min:0',
        ]);

        // ── Auto sort order ───────────────────────────────────────────────
        // If user left it empty or sent 0, auto-assign next available number
        $manualOrder = $request->input('sort_order');

        if ($manualOrder === null || $manualOrder === '' || (int)$manualOrder === 0) {
            $validated['sort_order'] = (BookingVariant::max('sort_order') ?? 0) + 1;
        } else {
            $validated['sort_order'] = (int) $manualOrder;
        }

        // ── Filter empty options ──────────────────────────────────────────
        $validated['options'] = array_values(
            array_filter($validated['options'], fn($o) => trim($o) !== '')
        );

        BookingVariant::create($validated);

        return redirect(getAdminPanelUrl('/booking/variant'))
            ->with('success', 'Variant created successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // EDIT
    // ─────────────────────────────────────────────────────────────────────
    public function edit($id)
    {
        $this->authorize('admin_booking_variants_edit');

        $editVariant = BookingVariant::findOrFail($id);

        $variants = BookingVariant::with(['booking'])
            ->orderBy('sort_order')
            ->paginate(20);

        $bookings = Booking::orderBy('id', 'desc')->get(['id', 'title']);

        // For edit we show the current sort order (no auto-increment needed)
        $nextSortOrder = $editVariant->sort_order;

        return view('admin.booking.variant', [
            'pageTitle'     => 'Edit Variant',
            'variants'      => $variants,
            'bookings'      => $bookings,
            'editVariant'   => $editVariant,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────
    // UPDATE
    // ─────────────────────────────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_variants_edit');

        $variant = BookingVariant::findOrFail($id);

        // ── Normalize checkboxes ──────────────────────────────────────────
        $request->merge([
            'affects_availability' => $request->has('affects_availability') ? 1 : 0,
            'status'               => $request->has('status') ? 1 : 0,
        ]);

        // ── Validation ───────────────────────────────────────────────────
        $validated = $request->validate([
            'booking_id'           => 'required|exists:bookings,id',
            'name'                 => 'required|string|max:255',
            'options'              => 'required|array|min:1',
            'options.*'            => 'required|string|max:255',
            'price_modifier'       => 'nullable|numeric',
            'affects_availability' => 'required|boolean',
            'status'               => 'required|boolean',
            'sort_order'           => 'nullable|integer|min:0',
        ]);

        // ── Auto sort order on update ─────────────────────────────────────
        // If user cleared sort_order, keep the existing one
        $manualOrder = $request->input('sort_order');

        if ($manualOrder === null || $manualOrder === '') {
            $validated['sort_order'] = $variant->sort_order;
        } else {
            $validated['sort_order'] = (int) $manualOrder;
        }

        // ── Filter empty options ──────────────────────────────────────────
        $validated['options'] = array_values(
            array_filter($validated['options'], fn($o) => trim($o) !== '')
        );

        $variant->update($validated);

        return redirect(getAdminPanelUrl('/booking/variant'))
            ->with('success', 'Variant updated successfully.');
    }

    // ─────────────────────────────────────────────────────────────────────
    // DELETE
    // ─────────────────────────────────────────────────────────────────────
    public function delete($id)
    {
        $this->authorize('admin_booking_variants_delete');

        $variant = BookingVariant::findOrFail($id);
        $variant->delete();

        // ── Re-sequence sort orders after delete ──────────────────────────
        // So numbers stay clean: 1, 2, 3 ... (no gaps)
        BookingVariant::orderBy('sort_order')
            ->get()
            ->each(function ($v, $index) {
                $v->updateQuietly(['sort_order' => $index + 1]);
            });

        return redirect(getAdminPanelUrl('/booking/variant'))
            ->with('success', 'Variant deleted successfully.');
    }
}