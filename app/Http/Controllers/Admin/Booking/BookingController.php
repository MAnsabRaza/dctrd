<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking');

        removeContentLocale();

        // Build query with filters
        $query = Booking::query();

        // Apply filters if present
        if ($request->get('title')) {
            $query->where('title', 'like', '%' . $request->get('title') . '%');
        }

        if ($request->get('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        if ($request->get('booking_type')) {
            $query->where('booking_type', $request->get('booking_type'));
        }

        if ($request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->get('from')) {
            $query->whereDate('created_at', '>=', $request->get('from'));
        }

        if ($request->get('to')) {
            $query->whereDate('created_at', '<=', $request->get('to'));
        }

        // Get paginated bookings (15 per page)
        $bookings = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get categories for filters and forms
        $categories = BookingCategory::where('status', 1)->orderBy('order')->get();
        $allCategories = BookingCategory::orderBy('order')->get();

        $data = [
            'pageTitle' => trans('admin/main.booking'),
            'bookings' => $bookings,           // ← Plural for list
            'categories' => $categories,       // ← For filter dropdown
            'allCategories' => $allCategories, // ← For create form
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
            'content' => $data['content'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'image' => $data['image'] ?? null,
            'gallery' => $data['gallery'] ?? null,
            // Pricing
            'price' => $data['price'],
            'price_per' => $data['price_per'] ?? null,
            'price_unit' => $data['price_unit'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            // Status
            'status' => isset($data['status']) && $data['status'] === 'published' ? 'published' : 'draft',
            'featured' => isset($data['featured']) && $data['featured'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking'))->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_edit');

        removeContentLocale();

        $editBooking = Booking::findOrFail($id);
        
        // Get paginated bookings for list tab
        $bookings = Booking::orderBy('created_at', 'desc')->paginate(15);
        
        // Get categories
        $categories = BookingCategory::where('status', 1)->orderBy('order')->get();
        $allCategories = BookingCategory::orderBy('order')->get();

        $data = [
            'pageTitle' => trans('admin/main.edit_booking'),
            'bookings' => $bookings,
            'editBooking' => $editBooking,
            'categories' => $categories,
            'allCategories' => $allCategories,
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
            'content' => $data['content'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'requirements' => $data['requirements'] ?? null,
            'image' => $data['image'] ?? null,
            'gallery' => $data['gallery'] ?? null,
            // Pricing
            'price' => $data['price'],
            'price_per' => $data['price_per'] ?? null,
            'price_unit' => $data['price_unit'] ?? null,
            'discount_price' => $data['discount_price'] ?? null,
            'currency' => $data['currency'] ?? 'USD',
            // Status
            'status' => isset($data['status']) && $data['status'] === 'published' ? 'published' : 'draft',
            'featured' => isset($data['featured']) && $data['featured'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking'))->with('success', trans('admin/main.updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_delete');

        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect(getAdminPanelUrl('/booking'))->with('success', trans('admin/main.deleted_successfully'));
    }
}