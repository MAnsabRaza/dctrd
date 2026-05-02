<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_categories');

        removeContentLocale();

        $bookingCategories = BookingCategory::withCount('bookings')->orderBy('order')->get();
        $allCategories     = BookingCategory::orderBy('order')->get();

        $data = [
            'pageTitle'         => trans('admin/main.booking_categories'),
            'bookingCategories' => $bookingCategories,
            'allCategories'     => $allCategories,
        ];

        return view('admin.booking.categories', $data);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_categories_create');

        $this->validate($request, [
            'title'       => 'required|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:booking_categories,slug',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string',
            'order'       => 'nullable|integer|min:0',
            'parent_id'   => 'nullable|exists:booking_categories,id',
        ]);

        $data = $request->all();

        BookingCategory::create([
            'parent_id'   => $data['parent_id'] ?? null,
            'title'       => $data['title'],
            'subtitle'    => $data['subtitle'] ?? null,
            'slug'        => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? null,
            'order'       => $data['order'] ?? 0,
            'status'      => isset($data['status']) && $data['status'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_categories_edit');

        $editCategory = BookingCategory::findOrFail($id);
        $bookingCategories = BookingCategory::withCount('bookings')->orderBy('order')->get();
        $allCategories     = BookingCategory::orderBy('order')->get();

        $data = [
            'pageTitle'         => trans('admin/main.booking_categories'),
            'bookingCategories' => $bookingCategories,
            'allCategories'     => $allCategories,
            'editCategory'      => $editCategory,
        ];

        return view('admin.booking.categories', $data);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_categories_edit');

        $category = BookingCategory::findOrFail($id);

        $this->validate($request, [
            'title'       => 'required|string|max:255',
            'subtitle'    => 'nullable|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:booking_categories,slug,' . $id,
            'description' => 'nullable|string',
            'icon'        => 'nullable|string',
            'order'       => 'nullable|integer|min:0',
            'parent_id'   => 'nullable|exists:booking_categories,id',
        ]);

        $data = $request->all();

        $category->update([
            'parent_id'   => $data['parent_id'] ?? null,
            'title'       => $data['title'],
            'subtitle'    => $data['subtitle'] ?? null,
            'slug'        => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? null,
            'order'       => $data['order'] ?? 0,
            'status'      => isset($data['status']) && $data['status'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_categories_delete');

        $category = BookingCategory::findOrFail($id);
        $category->delete();

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_deleted_successfully'));
    }
}