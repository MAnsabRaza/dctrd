<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Models\BookingFilter;
use App\Models\BookingFilterOption;
use Illuminate\Http\Request;

class BookingFilterController extends Controller
{
    public function index()
    {
        $this->authorize('admin_booking_filters');

        $filters = BookingFilter::with(['category', 'options'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        $categories = BookingCategory::where('parent_id', null)
            ->with('booking_categories')
            ->get();

        return view('admin.booking.booking_filter', [
            'pageTitle' => trans('admin/main.booking_filters'),
            'filters' => $filters,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_filters_create');

        $this->validate($request, [
            'title' => 'required|min:1|max:255',
            'category_id' => 'required|exists:booking_categories,id',
        ]);

        $data = $request->only(['title', 'category_id', 'language', 'status']);
        $data['status'] = !empty($data['status']);

        $filter = BookingFilter::create($data);

        $subFilters = $request->input('sub_filters', []);
        $this->setSubFilters($filter, $subFilters);

        return back()->with('success', trans('admin/main.filter_created'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_filters_edit');

        $filter = BookingFilter::findOrFail($id);
        $categories = BookingCategory::where('parent_id', null)
            ->with('subCategories')
            ->get();

        $filterOptions = BookingFilterOption::where('filter_id', $filter->id)
            ->orderBy('id', 'asc')
            ->get();

        return view('admin.booking.booking_filter', [
            'pageTitle' => trans('admin/main.edit_booking_filter'),
            'editItem' => $filter,
            'categories' => $categories,
            'filterOptions' => $filterOptions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_filters_edit');

        $this->validate($request, [
            'title' => 'required|min:1|max:255',
            'category_id' => 'required|exists:booking_categories,id',
        ]);

        $filter = BookingFilter::findOrFail($id);

        $data = $request->only(['title', 'category_id', 'language', 'status']);
        $data['status'] = !empty($data['status']);

        $filter->update($data);

        // Replace options: simplest approach - delete & recreate
        BookingFilterOption::where('filter_id', $filter->id)->delete();

        $subFilters = $request->input('sub_filters', []);
        $this->setSubFilters($filter, $subFilters);

        return redirect(getAdminPanelUrl().'/booking/filters')->with('success', trans('admin/main.filter_updated'));
    }

    public function destroy($id)
    {
        $this->authorize('admin_booking_filters_delete');

        $filter = BookingFilter::findOrFail($id);
        $filter->delete();

        BookingFilterOption::where('filter_id', $id)->delete();

        return back()->with('success', trans('admin/main.filter_deleted'));
    }

    private function setSubFilters(BookingFilter $filter, $subFilters)
    {
        if (empty($subFilters) || !count($subFilters)) {
            return;
        }

        $order = 1;
        foreach ($subFilters as $row) {
            if (empty($row['name'])) continue;

            BookingFilterOption::create([
                'filter_id' => $filter->id,
                'name' => $row['name'],
                'order' => $order,
            ]);

            $order++;
        }
    }

    public function getByCategoryId($categoryId)
    {
        $filters = BookingFilter::where('category_id', $categoryId)
            ->with(['options' => function ($q) { $q->orderBy('order', 'asc'); }])
            ->get();

        return response()->json(['filters' => $filters], 200);
    }
}
