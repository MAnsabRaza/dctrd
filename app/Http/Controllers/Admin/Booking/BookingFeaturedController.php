<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use App\Models\BookingFeatured;
use Illuminate\Http\Request;

class BookingFeaturedController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_featured');

        $query = BookingFeatured::with(['booking', 'user']);

        if ($request->filled('page')) {
            $query->where('page', $request->get('page'));
        }

        if ($request->filled('status')) {
            if ($request->get('status') === 'active') {
                $query->where('status', true);
            } elseif ($request->get('status') === 'inactive') {
                $query->where('status', false);
            }
        }

        if ($request->filled('booking_title')) {
            $search = $request->get('booking_title');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('booking', function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->get('category_id');
            $query->whereHas('booking', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        $items->appends($request->query());

        $categories = BookingCategory::whereNull('parent_id')->get();

        return view('admin.booking.booking_featured', [
            'pageTitle' => trans('admin/main.booking_featured'),
            'items' => $items,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('admin_booking_featured_create');

        $bookings = Booking::orderBy('id', 'desc')->pluck('title', 'id');
        $categories = BookingCategory::whereNull('parent_id')->get();
        $items = BookingFeatured::with(['booking', 'user'])->orderBy('id', 'desc')->paginate(10);

        return view('admin.booking.booking_featured', [
            'pageTitle' => trans('admin/main.new_booking_featured'),
            'bookings' => $bookings,
            'categories' => $categories,
            'items' => $items,
            'activeTab' => 'new',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_featured_create');

        $this->validate($request, [
            'booking_id' => 'required|exists:bookings,id',
            'title' => 'required|string|max:255',
            'page' => 'required|string',
        ]);

        $data = $request->only(['language', 'booking_id', 'page', 'title', 'description', 'status']);
        $data['user_id'] = auth()->id();
        $data['status'] = !empty($data['status']);

        BookingFeatured::create($data);

        return back()->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_featured_edit');

        $item = BookingFeatured::findOrFail($id);
        $bookings = Booking::orderBy('id', 'desc')->pluck('title', 'id');
        $categories = BookingCategory::whereNull('parent_id')->get();
        $items = BookingFeatured::with(['booking', 'user'])->orderBy('id', 'desc')->paginate(10);

        return view('admin.booking.booking_featured', [
            'pageTitle' => trans('admin/main.edit_booking_featured'),
            'editItem' => $item,
            'bookings' => $bookings,
            'categories' => $categories,
            'items' => $items,
            'activeTab' => 'new',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_featured_edit');

        $this->validate($request, [
            'booking_id' => 'required|exists:bookings,id',
            'title' => 'required|string|max:255',
            'page' => 'required|string',
        ]);

        $item = BookingFeatured::findOrFail($id);

        $data = $request->only(['language', 'booking_id', 'page', 'title', 'description', 'status']);
        $data['user_id'] = auth()->id();
        $data['status'] = !empty($data['status']);

        $item->update($data);

        return redirect(getAdminPanelUrl().'/booking/featured')->with('success', trans('admin/main.updated_successfully'));
    }

    public function destroy($id)
    {
        $this->authorize('admin_booking_featured_delete');

        BookingFeatured::findOrFail($id)->delete();

        return back()->with('success', trans('admin/main.deleted_successfully'));
    }
}
