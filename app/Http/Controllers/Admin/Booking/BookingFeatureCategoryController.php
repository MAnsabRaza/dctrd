<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingFeatureCategory;
use Illuminate\Http\Request;

class BookingFeatureCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_feature_categories');
        removeContentLocale();

        $featuredCategories = BookingFeatureCategory::query()
            ->with('category')
            ->latest()
            ->get();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $data = [
            'pageTitle' => 'Booking Feature Categories',
            'featuredCategories' => $featuredCategories,
            'productCategories' => $productCategories,
        ];

        return view('admin.booking.feature_category', $data);
    }

    public function create()
    {
        $this->authorize('admin_booking_feature_categories_create');

        $featuredCategories = BookingFeatureCategory::query()
            ->with('category')
            ->latest()
            ->get();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        return view('admin.booking.feature_category', [
            'pageTitle' => 'Add Booking Feature Category',
            'featuredCategories' => $featuredCategories,
            'productCategories' => $productCategories,
            'activeTab' => 'new',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_feature_categories_create');

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
            'image' => 'required|string|max:255',
        ]);

        $data = $request->all();

        $check = BookingFeatureCategory::query()->where('category_id', $data['category_id'])->first();

        if (!empty($check)) {
            return redirect()->back()->withErrors([
                'category_id' => trans('update.featured_category_exists'),
            ]);
        }

        BookingFeatureCategory::query()->create([
            'category_id' => $data['category_id'],
            'image' => $data['image'],
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.featured_category_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/booking/feature-categories"))->with(['toast' => $toastData]);
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_feature_categories_edit');

        $editFeaturedCategory = BookingFeatureCategory::query()->findOrFail($id);

        $featuredCategories = BookingFeatureCategory::query()
            ->with('category')
            ->latest()
            ->get();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $data = [
            'pageTitle' => 'Edit Booking Feature Category',
            'editFeaturedCategory' => $editFeaturedCategory,
            'featuredCategories' => $featuredCategories,
            'productCategories' => $productCategories,
            'activeTab' => 'new',
        ];

        return view('admin.booking.feature_category', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_feature_categories_edit');

        $featuredCategory = BookingFeatureCategory::query()->findOrFail($id);

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
            'image' => 'required|string|max:255',
        ]);

        $data = $request->all();

        $check = BookingFeatureCategory::query()->where('id', '!=', $featuredCategory->id)
            ->where('category_id', $data['category_id'])
            ->first();

        if (!empty($check)) {
            return redirect()->back()->withErrors([
                'category_id' => trans('update.featured_category_exists'),
            ]);
        }

        $featuredCategory->update([
            'category_id' => $data['category_id'],
            'image' => $data['image'],
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.featured_category_updated_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/booking/feature-categories"))->with(['toast' => $toastData]);
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_feature_categories_delete');

        $featuredCategory = BookingFeatureCategory::query()->findOrFail($id);

        $featuredCategory->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.featured_category_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/booking/feature-categories"))->with(['toast' => $toastData]);
    }

    public function destroy($id)
    {
        return $this->delete($id);
    }
}
