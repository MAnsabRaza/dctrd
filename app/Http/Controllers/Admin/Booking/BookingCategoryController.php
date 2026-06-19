<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\BookingCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BookingCategoryController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_categories');

        removeContentLocale();

        $bookingCategories = BookingCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();

        $bookingTypes = Booking::select('booking_type')
            ->distinct()
            ->orderBy('booking_type')
            ->pluck('booking_type')
            ->toArray();

        $nextOrder = (BookingCategory::max('order') ?? 0) + 1;

        $data = [
            'pageTitle'         => trans('admin/main.booking_categories'),
            'bookingCategories' => $bookingCategories,
            'parentCategories'  => $bookingCategories,
            'bookingTypes'      => $bookingTypes,
            'nextOrder'         => $nextOrder,
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
        $nextOrder = (BookingCategory::max('order') ?? 0) + 1;

        BookingCategory::create([
            'parent_id'   => $data['parent_id'] ?? null,
            'user_id'     => auth()->id(),
            'title'       => $data['title'],
            'subtitle'    => $data['subtitle'] ?? null,
            'slug'        => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? null,
            'order'       => !empty($data['order']) ? $data['order'] : $nextOrder,
            'status'      => isset($data['status']) && $data['status'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_categories_edit');

        $editCategory = BookingCategory::findOrFail($id);
        $bookingCategories = BookingCategory::whereNull('parent_id')
            ->with('children')
            ->orderBy('order')
            ->get();
        $bookingTypes = Booking::select('booking_type')
            ->distinct()
            ->orderBy('booking_type')
            ->pluck('booking_type')
            ->toArray();
        $parentCategories  = BookingCategory::roots()->get();
        $nextOrder         = (BookingCategory::max('order') ?? 0) + 1;

        $data = [
            'pageTitle'         => trans('admin/main.booking_categories'),
            'bookingCategories' => $bookingCategories,
            'parentCategories'  => $parentCategories,
            'bookingTypes'      => $bookingTypes,
            'nextOrder'         => $nextOrder,
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
            'user_id'     => auth()->id(),
            'title'       => $data['title'],
            'subtitle'    => $data['subtitle'] ?? null,
            'slug'        => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'description' => $data['description'] ?? null,
            'icon'        => $data['icon'] ?? null,
            'order'       => !empty($data['order']) ? $data['order'] : $category->order,
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