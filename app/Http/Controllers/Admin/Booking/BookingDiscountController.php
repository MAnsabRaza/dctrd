<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingBundle;
use App\Models\BookingDiscount;
use Illuminate\Http\Request;

class BookingDiscountController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_discounts');
        removeContentLocale();

        $items = BookingDiscount::with(['booking', 'bundle'])
            ->latest('id')
            ->paginate(20);

        $bookings = Booking::query()
            ->orderByDesc('id')
            ->get(['id', 'title']);

        $bundles = BookingBundle::query()
            ->orderByDesc('id')
            ->get(['id', 'title']);

        return view('admin.booking.booking_discounts', [
            'pageTitle' => trans('admin/main.booking_discounts') ?: 'Booking Discounts',
            'items' => $items,
            'bookings' => $bookings,
            'bundles' => $bundles,
            'editItem' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_discounts_create');

        $data = $this->validateData($request);
        $data['discount_type'] = 'percent';

        BookingDiscount::create($data);

        return back()->with('success', trans('admin/main.created_successfully') ?: 'Booking discount created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_discounts_edit');
        removeContentLocale();

        $item = BookingDiscount::findOrFail($id);
        $items = BookingDiscount::with(['booking', 'bundle'])
            ->latest('id')
            ->paginate(20);

        $bookings = Booking::query()
            ->orderByDesc('id')
            ->get(['id', 'title']);

        $bundles = BookingBundle::query()
            ->orderByDesc('id')
            ->get(['id', 'title']);

        return view('admin.booking.booking_discounts', [
            'pageTitle' => trans('admin/main.booking_discounts') ?: 'Booking Discounts',
            'items' => $items,
            'bookings' => $bookings,
            'bundles' => $bundles,
            'editItem' => $item,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_discounts_edit');

        $item = BookingDiscount::findOrFail($id);
        $data = $this->validateData($request);
        $data['discount_type'] = 'percent';

        $item->update($data);

        return redirect(getAdminPanelUrl('/booking/discounts'))
            ->with('success', trans('admin/main.updated_successfully') ?: 'Booking discount updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_discounts_delete');

        BookingDiscount::findOrFail($id)->delete();

        return back()->with('success', trans('admin/main.deleted_successfully') ?: 'Booking discount deleted successfully.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'booking_id' => 'nullable|exists:bookings,id|required_without:bundle_id',
            'bundle_id' => 'nullable|exists:booking_bundles,id|required_without:booking_id',
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0|max:100',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'status' => 'nullable|boolean',
            'meta' => 'nullable|json',
        ]);
    }
}
