<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingTopCategory;
use Illuminate\Http\Request;

class BookingTopCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_top_categories');
        removeContentLocale();

        $featuredCategories = BookingTopCategory::query()
            ->with('category')
            ->latest()
            ->get();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $data = [
            'pageTitle' => trans('update.top_categories'),
            'featuredCategories' => $featuredCategories,
            'productCategories' => $productCategories,
        ];

        return view('admin.booking.top_category', $data);
    }

    public function create()
    {
        $this->authorize('admin_booking_top_categories_create');

        $featuredCategories = BookingTopCategory::query()
            ->with('category')
            ->latest()
            ->get();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        return view('admin.booking.top_category', [
            'pageTitle' => trans('update.add_top_category'),
            'featuredCategories' => $featuredCategories,
            'productCategories' => $productCategories,
            'activeTab' => 'new',
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_top_categories_create');

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
            'image' => 'required|string|max:255',
        ]);

        $data = $request->all();

        $check = BookingTopCategory::query()->where('category_id', $data['category_id'])->first();

        if (!empty($check)) {
            return redirect()->back()->withErrors([
                'category_id' => trans('update.featured_category_exists'),
            ]);
        }

        BookingTopCategory::query()->create([
            'category_id' => $data['category_id'],
            'image' => $data['image'],
        ]);

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.featured_category_created_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/booking/top-categories"))->with(['toast' => $toastData]);
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_top_categories_edit');

        $editFeaturedCategory = BookingTopCategory::query()->findOrFail($id);

        $featuredCategories = BookingTopCategory::query()
            ->with('category')
            ->latest()
            ->get();

        $productCategories = BookingCategory::query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $data = [
            'pageTitle' => trans('update.top_categories'),
            'editFeaturedCategory' => $editFeaturedCategory,
            'featuredCategories' => $featuredCategories,
            'productCategories' => $productCategories,
            'activeTab' => 'new',
        ];

        return view('admin.booking.top_category', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_top_categories_edit');

        $featuredCategory = BookingTopCategory::query()->findOrFail($id);

        $this->validate($request, [
            'category_id' => 'required|exists:booking_categories,id',
            'image' => 'required|string|max:255',
        ]);

        $data = $request->all();

        $check = BookingTopCategory::query()->where('id', '!=', $featuredCategory->id)
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
        return redirect(getAdminPanelUrl("/booking/top-categories"))->with(['toast' => $toastData]);
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_top_categories_delete');

        $featuredCategory = BookingTopCategory::query()->findOrFail($id);

        $featuredCategory->delete();

        $toastData = [
            'title' => trans('public.request_success'),
            'msg' => trans('update.featured_category_deleted_successful'),
            'status' => 'success'
        ];
        return redirect(getAdminPanelUrl("/booking/top-categories"))->with(['toast' => $toastData]);
    }

    public function destroy($id)
    {
        return $this->delete($id);
    }
}
