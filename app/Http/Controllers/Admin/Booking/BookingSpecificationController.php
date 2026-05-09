<?php
// App\Http\Controllers\Admin\Booking\BookingSpecificationController.php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingSpecification;
use Illuminate\Http\Request;

class BookingSpecificationController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_specification');

        removeContentLocale();

        $specifications = BookingSpecification::with(['categories'])
            ->orderBy('sort_order')
            ->paginate(20);

        $categories    = BookingCategory::where('status', 1)->orderBy('order')->get();
        $nextSortOrder = (BookingSpecification::max('sort_order') ?? 0) + 1;

        return view('admin.booking.specification', [
            'pageTitle'      => 'Booking Specifications',
            'specifications' => $specifications,
            'categories'     => $categories,
            'nextSortOrder'  => $nextSortOrder,
            'editSpecification' => null,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_specification_create');

        $request->merge([
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'type'         => 'required|in:textbox,multi_value',
            'values'       => 'nullable|array',
            'values.*'     => 'nullable|string|max:255',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:booking_categories,id',
            'status'       => 'required|boolean',
            'sort_order'   => 'nullable|integer|min:0',
        ]);

        if (!empty($validated['values'])) {
            $validated['values'] = array_values(
                array_filter($validated['values'], fn($v) => trim($v) !== '')
            );
        }

        $validated['sort_order'] = (empty($validated['sort_order']))
            ? (BookingSpecification::max('sort_order') ?? 0) + 1
            : (int) $validated['sort_order'];

        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $spec = BookingSpecification::create($validated);

        if (!empty($categoryIds)) {
            $spec->categories()->sync($categoryIds);
        }

        return redirect(getAdminPanelUrl('/booking/specification'))
            ->with('success', 'Specification created successfully.');
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_specification_edit');

        $editSpecification = BookingSpecification::with(['categories'])->findOrFail($id);

        $specifications = BookingSpecification::with(['categories'])
            ->orderBy('sort_order')
            ->paginate(20);

        $categories = BookingCategory::where('status', 1)->orderBy('order')->get();

        return view('admin.booking.specification', [
            'pageTitle'         => 'Edit Specification',
            'specifications'    => $specifications,
            'categories'        => $categories,
            'editSpecification' => $editSpecification,
            'nextSortOrder'     => $editSpecification->sort_order,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_specification_edit');

        $specification = BookingSpecification::findOrFail($id);

        $request->merge([
            'status' => $request->has('status') ? 1 : 0,
        ]);

        $validated = $request->validate([
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:textbox,multi_value',
            'values'         => 'nullable|array',
            'values.*'       => 'nullable|string|max:255',
            'category_ids'   => 'nullable|array',
            'category_ids.*' => 'exists:booking_categories,id',
            'status'         => 'required|boolean',
            'sort_order'     => 'nullable|integer|min:0',
        ]);

        if (!empty($validated['values'])) {
            $validated['values'] = array_values(
                array_filter($validated['values'], fn($v) => trim($v) !== '')
            );
        }

        if (empty($validated['sort_order'])) {
            $validated['sort_order'] = $specification->sort_order;
        }

        $categoryIds = $validated['category_ids'] ?? [];
        unset($validated['category_ids']);

        $specification->update($validated);
        $specification->categories()->sync($categoryIds);

        return redirect(getAdminPanelUrl('/booking/specification'))
            ->with('success', 'Specification updated successfully.');
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_specification_delete');

        $specification = BookingSpecification::findOrFail($id);
        $specification->categories()->detach(); // pivot clean
        $specification->delete();

        // sort_order reorder
        BookingSpecification::orderBy('sort_order')
            ->get()
            ->each(fn($v, $i) => $v->updateQuietly(['sort_order' => $i + 1]));

        return redirect(getAdminPanelUrl('/booking/specification'))
            ->with('success', 'Specification deleted successfully.');
    }
}