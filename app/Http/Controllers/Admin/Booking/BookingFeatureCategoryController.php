<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingFeatureCategory;
use Illuminate\Http\Request;

class BookingFeatureCategoryController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('admin_booking_feature_categories');

        $query = BookingFeatureCategory::with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $items = $query->orderBy('id', 'desc')->paginate(10);
        $items->appends($request->query());

        $categories = BookingCategory::orderBy('title')->pluck('title', 'id');

        return view('admin.booking.feature_category', [
            'pageTitle' => trans('admin/main.booking_feature_categories'),
            'items'     => $items,
            'categories' => $categories,
        ]);
    }

    public function create()
    {
        $this->authorize('admin_booking_feature_categories_create');

        $categories = BookingCategory::orderBy('title')->pluck('title', 'id');
        $items = BookingFeatureCategory::with('category')->orderBy('id', 'desc')->paginate(10);

        return view('admin.booking.feature_category', [
            'pageTitle' => trans('admin/main.new_booking_feature_category'),
            'categories' => $categories,
            'items'      => $items,
            'activeTab'  => 'new',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_feature_categories_create');

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
            'image' => 'required|image',
        ]);

        $data = $request->only(['category_id']);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $filename = 'feature_category_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/booking/feature_categories', $filename);
            $data['image'] = 'booking/feature_categories/' . $filename;
        }

        BookingFeatureCategory::create($data);

        return redirect(getAdminPanelUrl() . '/booking/feature-categories')
            ->with('success', trans('admin/main.created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_feature_categories_edit');

        $item = BookingFeatureCategory::findOrFail($id);
        $categories = BookingCategory::orderBy('title')->pluck('title', 'id');
        $items = BookingFeatureCategory::with('category')->orderBy('id', 'desc')->paginate(10);

        return view('admin.booking.feature_category', [
            'pageTitle' => trans('admin/main.edit_booking_feature_category'),
            'editItem'  => $item,
            'categories' => $categories,
            'items'      => $items,
            'activeTab'  => 'new',
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_feature_categories_edit');

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
            'image' => 'nullable|image',
        ]);

        $item = BookingFeatureCategory::findOrFail($id);
        $data = $request->only(['category_id']);

        if ($request->hasFile('image')) {
            if ($item->image && file_exists(storage_path('app/public/' . $item->image))) {
                unlink(storage_path('app/public/' . $item->image));
            }
            
            $image = $request->file('image');
            $filename = 'feature_category_' . time() . '.' . $image->getClientOriginalExtension();
            $image->storeAs('public/booking/feature_categories', $filename);
            $data['image'] = 'booking/feature_categories/' . $filename;
        }

        $item->update($data);

        return redirect(getAdminPanelUrl() . '/booking/feature-categories')
            ->with('success', trans('admin/main.updated_successfully'));
    }

    public function destroy($id)
    {
        $this->authorize('admin_booking_feature_categories_delete');

        $item = BookingFeatureCategory::findOrFail($id);

        if ($item->image && file_exists(storage_path('app/public/' . $item->image))) {
            unlink(storage_path('app/public/' . $item->image));
        }

        $item->delete();

        return redirect(getAdminPanelUrl() . '/booking/feature-categories')
            ->with('success', trans('admin/main.deleted_successfully'));
    }
}
