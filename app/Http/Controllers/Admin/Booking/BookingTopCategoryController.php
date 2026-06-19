<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingTopCategory;
use Illuminate\Http\Request;

class BookingTopCategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_top_categories');

        $query = BookingTopCategory::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        $items->appends($request->query());

        $categories = BookingCategory::orderBy('title')->pluck('title', 'id');

        return view('admin.booking.top_category', [
            'pageTitle' => trans('admin/main.booking_top_categories'),
            'items'     => $items,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('admin_booking_top_categories_create');

        $categories = BookingCategory::orderBy('title')->pluck('title', 'id');
        $items = BookingTopCategory::with('category')->orderBy('id', 'desc')->paginate(10);

        return view('admin.booking.top_category', [
            'pageTitle' => trans('admin/main.new_booking_top_category'),
            'categories' => $categories,
            'items'      => $items,
            'activeTab'  => 'new',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_top_categories_create');

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
             'image' => 'required',
        ]);

        $data = $request->only(['category_id']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'top_category_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/booking/top_categories', $filename);
            $data['image'] = 'booking/top_categories/' . $filename;
        }

        BookingTopCategory::create($data);

        return redirect(getAdminPanelUrl() . '/booking/top-categories')
            ->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_top_categories_edit');

        $item = BookingTopCategory::findOrFail($id);
        $categories = BookingCategory::orderBy('title')->pluck('title', 'id');
        $items = BookingTopCategory::with('category')->orderBy('id', 'desc')->paginate(10);

        return view('admin.booking.top_category', [
            'pageTitle' => trans('admin/main.edit_booking_top_category'),
            'item'      => $item,
            'categories' => $categories,
            'items'      => $items,
            'activeTab'  => 'new',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_top_categories_edit');

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
             'image' => 'required',
        ]);

        $item = BookingTopCategory::findOrFail($id);
        $data = $request->only(['category_id']);

        if ($request->hasFile('image')) {
            if ($item->image && file_exists(storage_path('app/public/' . $item->image))) {
                unlink(storage_path('app/public/' . $item->image));
            }
            
            $image = $request->file('image');
            $filename = 'top_category_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/booking/top_categories', $filename);
            $data['image'] = 'booking/top_categories/' . $filename;
        }

        $item->update($data);

        return redirect(getAdminPanelUrl() . '/booking/top-categories')
            ->with('success', trans('admin/main.updated_successfully'));
    }

    public function destroy($id)
    {
        $this->authorize('admin_booking_top_categories_delete');

        $item = BookingTopCategory::findOrFail($id);

        if ($item->image && file_exists(storage_path('app/public/' . $item->image))) {
            unlink(storage_path('app/public/' . $item->image));
        }

        $item->delete();

        return back()->with('success', trans('admin/main.deleted_successfully'));
    }
}
