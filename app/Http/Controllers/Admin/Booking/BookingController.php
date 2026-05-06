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

        $query = Booking::query();

        if ($request->get('title'))
            $query->where('title', 'like', '%' . $request->get('title') . '%');
        if ($request->get('category_id'))
            $query->where('category_id', $request->get('category_id'));
        if ($request->get('booking_type'))
            $query->where('booking_type', $request->get('booking_type'));
        if ($request->get('status'))
            $query->where('status', $request->get('status'));
        if ($request->get('from'))
            $query->whereDate('created_at', '>=', $request->get('from'));
        if ($request->get('to'))
            $query->whereDate('created_at', '<=', $request->get('to'));

        $bookings      = $query->orderBy('created_at', 'desc')->paginate(15);
        $categories    = BookingCategory::where('status', 1)->orderBy('order')->get();
        $allCategories = BookingCategory::orderBy('order')->get();

        return view('admin.booking.booking', [
            'pageTitle'     => trans('admin/main.booking'),
            'bookings'      => $bookings,
            'categories'    => $categories,
            'allCategories' => $allCategories,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_create');

        $this->validate($request, [
            'title'        => 'required|string|max:255',
            'category_id'  => 'nullable|exists:booking_categories,id',
            'booking_type' => 'required|string|max:255',
            'price'        => 'required|numeric|min:0',
            // price_per — migration mein decimal hai, isliye numeric validate karo
            'price_per'    => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        Booking::create([
            'creator_id'       => auth()->id(),
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'slug'             => $request->slug
                                    ? Str::slug($request->slug)
                                    : Str::slug($request->title) . '-' . uniqid(),
            'booking_type'     => $request->booking_type,
            'sub_type'         => $request->sub_type,
            'description'      => $request->description,
            'requirements'     => $request->requirements,

            // Pricing
            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,  // decimal — null agar empty
            'price_unit'       => $request->price_unit,         // string — "per night" etc.
            'discount_price'   => $request->discount_price ?: null,
            'currency'         => $request->currency ?? 'USD',

            // Capacity
            'min_persons'      => $request->min_persons ?? 1,
            'max_persons'      => $request->max_persons ?: null,
            'capacity'         => $request->capacity ?: null,

            // Duration
            'duration_minutes' => $request->duration_minutes ?: null,

            // Location
            'location_enabled' => $request->location_enabled === 'on',
            'address_line'     => $request->address_line,
            'city'             => $request->city,
            'state'            => $request->state,
            'country'          => $request->country,
            'postal_code'      => $request->postal_code,
            'lat'              => $request->lat ?: null,
            'lng'              => $request->lng ?: null,

            // Status
            'status'           => $request->status === 'published' ? 'published' : 'draft',
            'featured'         => $request->featured === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking'))
            ->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_edit');
        removeContentLocale();

        $editBooking   = Booking::findOrFail($id);
        $bookings      = Booking::orderBy('created_at', 'desc')->paginate(15);
        $categories    = BookingCategory::where('status', 1)->orderBy('order')->get();
        $allCategories = BookingCategory::orderBy('order')->get();

        return view('admin.booking.booking', [
            'pageTitle'     => trans('admin/main.edit_booking'),
            'bookings'      => $bookings,
            'editBooking'   => $editBooking,
            'categories'    => $categories,
            'allCategories' => $allCategories,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_edit');

        $booking = Booking::findOrFail($id);

        $this->validate($request, [
            'title'          => 'required|string|max:255',
            'category_id'    => 'nullable|exists:booking_categories,id',
            'booking_type'   => 'required|string|max:255',
            'price'          => 'required|numeric|min:0',
            'price_per'      => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0',
        ]);

        $booking->update([
            'category_id'      => $request->category_id,
            'title'            => $request->title,
            'slug'             => $request->slug
                                    ? Str::slug($request->slug)
                                    : Str::slug($request->title) . '-' . uniqid(),
            'booking_type'     => $request->booking_type,
            'sub_type'         => $request->sub_type,
            'description'      => $request->description,
            'requirements'     => $request->requirements,

            // Pricing
            'price'            => $request->price,
            'price_per'        => $request->price_per ?: null,
            'price_unit'       => $request->price_unit,
            'discount_price'   => $request->discount_price ?: null,
            'currency'         => $request->currency ?? 'USD',

            // Capacity
            'min_persons'      => $request->min_persons ?? 1,
            'max_persons'      => $request->max_persons ?: null,
            'capacity'         => $request->capacity ?: null,

            // Duration
            'duration_minutes' => $request->duration_minutes ?: null,

            // Location
            'location_enabled' => $request->location_enabled === 'on',
            'address_line'     => $request->address_line,
            'city'             => $request->city,
            'state'            => $request->state,
            'country'          => $request->country,
            'postal_code'      => $request->postal_code,
            'lat'              => $request->lat ?: null,
            'lng'              => $request->lng ?: null,

            // Status
            'status'           => $request->status === 'published' ? 'published' : 'draft',
            'featured'         => $request->featured === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking'))
            ->with('success', trans('admin/main.updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_delete');

        Booking::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking'))
            ->with('success', trans('admin/main.deleted_successfully'));
    }
}