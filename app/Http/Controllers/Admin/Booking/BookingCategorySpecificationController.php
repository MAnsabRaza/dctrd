<?php
// App\Http\Controllers\Admin\Booking\BookingCategorySpecificationController.php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingCategorySpecification;
use App\Models\BookingSpecification;
use Illuminate\Http\Request;

class BookingCategorySpecificationController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_category_specification');

        removeContentLocale();

        $categories     = BookingCategory::where('status', 1)->orderBy('order')->get();
        $specifications = BookingSpecification::active()->ordered()->get(['id', 'title']);

        $categorySpecifications = BookingCategorySpecification::with(['category', 'specification'])
            ->paginate(20);

        return view('admin.booking.categorySpecification', [
            'pageTitle'              => 'Category Specifications',
            'categories'             => $categories,
            'specifications'         => $specifications,
            'categorySpecifications' => $categorySpecifications,
            'editCategorySpec'       => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_category_specification_create');

        $validated = $request->validate([
            'category_id'      => 'required|exists:booking_categories,id',
            'specification_id' => 'required|exists:booking_specifications,id',
        ]);

        // duplicate check
        $exists = BookingCategorySpecification::where($validated)->exists();
        if ($exists) {
            return redirect()->back()
                ->withErrors(['specification_id' => 'This specification is already assigned to this category.'])
                ->withInput();
        }

        BookingCategorySpecification::create($validated);

        return redirect(getAdminPanelUrl('/booking/categorySpecification'))
            ->with('success', 'Category specification assigned successfully.');
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_category_specification_edit');

        $editCategorySpec = BookingCategorySpecification::with(['category', 'specification'])
            ->findOrFail($id);

        $categories     = BookingCategory::where('status', 1)->orderBy('order')->get();
        $specifications = BookingSpecification::active()->ordered()->get(['id', 'title']);

        $categorySpecifications = BookingCategorySpecification::with(['category', 'specification'])
            ->paginate(20);

        return view('admin.booking.categorySpecification', [
            'pageTitle'              => 'Edit Category Specification',
            'categories'             => $categories,
            'specifications'         => $specifications,
            'categorySpecifications' => $categorySpecifications,
            'editCategorySpec'       => $editCategorySpec,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_category_specification_edit');

        $categorySpec = BookingCategorySpecification::findOrFail($id);

        $validated = $request->validate([
            'category_id'      => 'required|exists:booking_categories,id',
            'specification_id' => 'required|exists:booking_specifications,id',
        ]);

        // duplicate check (excluding self)
        $exists = BookingCategorySpecification::where($validated)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return redirect()->back()
                ->withErrors(['specification_id' => 'This specification is already assigned to this category.'])
                ->withInput();
        }

        $categorySpec->update($validated);

        return redirect(getAdminPanelUrl('/booking/categorySpecification'))
            ->with('success', 'Category specification updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_category_specification_delete');

        BookingCategorySpecification::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/categorySpecification'))
            ->with('success', 'Category specification removed successfully.');
    }
}