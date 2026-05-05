<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking');

        removeContentLocale();

        $booking = Booking::orderBy('order')->get();
        $data = [
            'pageTitle' => trans('admin/main.booking'),
            'bookingCategories' => $booking,
        ];
        return view('admin.booking.booking', $data);
    }
    public function store(Request $request)
    {
        $this->authorize('admin_booking_create');
        $this->validate($request, [
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:booking_categories,id',
            'booking_type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $data = $request->all();
        Booking::create([
            'created_id' => auth()->id(),
            'category_id' => $data['category_id'] ?? null,
            'title' => $data['title'],
            'slug' => !empty($data['slug'])
                ? Str::slug($data['slug'])
                : Str::slug($data['title']) . '-' . uniqid(),
            'booking_type' => $data['booking_type'],
            'sub_type' => $data['sub_type'] ?? null,
            'description' => $data['description'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            // Pricing
            'price' => $data['price'],
            'price_per' => $data['price_per'] ?? null,
            'price_unit' => $data['price_unit'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            // Status
            'status' => $data['status'] ?? 'draft',
            'featured' => isset($data['featured']) && $data['featured'] === 'on',
        ]);
        return redirect(getAdminPanelUrl('/booking'))
            ->with('success', trans('admin/main.created_successfully'));
    }
    public function edit($id)
    {
        $this->authorize('admin_booking_edit');

        $booking = Booking::findOrFail($id);
        $bookings = Booking::latest()->get();
        $categories = BookingCategory::orderBy('order')->get();

        $data = [
            'pageTitle' => trans('admin/main.booking'),
            'bookings' => $bookings,
            'categories' => $categories,
            'editBooking' => $booking,
        ];

        return view('admin.booking.booking', $data);
    }
    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_edit');

        $booking = Booking::findOrFail($id);

        $this->validate($request, [
            'title' => 'required|string|max:255',
            'category_id' => 'nullable|exists:booking_categories,id',
            'booking_type' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
        ]);

        $data = $request->all();

        $booking->update([
            'category_id' => $data['category_id'] ?? null,

            'title' => $data['title'],
            'slug' => !empty($data['slug'])
                ? Str::slug($data['slug'])
                : Str::slug($data['title']) . '-' . uniqid(),

            'booking_type' => $data['booking_type'],
            'sub_type' => $data['sub_type'] ?? null,

            'description' => $data['description'] ?? null,
            'requirements' => $data['requirements'] ?? null,

            // Pricing
            'price' => $data['price'],
            'price_per' => $data['price_per'] ?? null,
            'price_unit' => $data['price_unit'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'currency' => $data['currency'] ?? 'USD',

            // Status
            'status' => $data['status'] ?? 'draft',
            'featured' => isset($data['featured']) && $data['featured'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking'))
            ->with('success', trans('admin/main.updated_successfully'));
    }
    public function delete($id)
    {
        $this->authorize('admin_booking_delete');

        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect(getAdminPanelUrl('/booking'))
            ->with('success', trans('admin/main.deleted_successfully'));
    }
}
