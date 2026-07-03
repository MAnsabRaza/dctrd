<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use App\Services\BookingTemplateConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

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

        $nextOrder = (BookingCategory::max('order') ?? 0) + 1;

        return view('admin.booking.categories', [
            'pageTitle'         => trans('admin/main.booking_categories'),
            'bookingCategories' => $bookingCategories,
            'parentCategories'  => $bookingCategories,
            'bookingTypes'      => BookingTemplateConfig::allTypes(),      // 7, for root categories
            'allTemplates'      => BookingTemplateConfig::TEMPLATES,       // 23, for child categories (JS filters by parent)
            'nextOrder'         => $nextOrder,
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('admin_booking_categories_create');
        $this->validateCategory($request);

        $data      = $request->all();
        $nextOrder = (BookingCategory::max('order') ?? 0) + 1;

        BookingCategory::create([
            'parent_id'    => $data['parent_id'] ?? null,
            'booking_type' => empty($data['parent_id']) ? $data['booking_type'] : null,
            'template_key' => !empty($data['parent_id']) ? $data['template_key'] : null,
            'user_id'      => auth()->id(),
            'title'        => $data['title'],
            'subtitle'     => $data['subtitle'] ?? null,
            'slug'         => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'description'  => $data['description'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'order'        => !empty($data['order']) ? $data['order'] : $nextOrder,
            'status'       => isset($data['status']) && $data['status'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_created_successfully'));
    }

    public function edit($id)
    {
        $this->authorize('admin_booking_categories_edit');

        $editCategory      = BookingCategory::findOrFail($id);
        $bookingCategories = BookingCategory::whereNull('parent_id')->with('children')->orderBy('order')->get();
        $parentCategories  = BookingCategory::roots()->get();
        $nextOrder         = (BookingCategory::max('order') ?? 0) + 1;

        return view('admin.booking.categories', [
            'pageTitle'         => trans('admin/main.booking_categories'),
            'bookingCategories' => $bookingCategories,
            'parentCategories'  => $parentCategories,
            'bookingTypes'      => BookingTemplateConfig::allTypes(),
            'allTemplates'      => BookingTemplateConfig::TEMPLATES,
            'nextOrder'         => $nextOrder,
            'editCategory'      => $editCategory,
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->authorize('admin_booking_categories_edit');

        $category = BookingCategory::findOrFail($id);
        $this->validateCategory($request, $id);

        $data = $request->all();

        $category->update([
            'parent_id'    => $data['parent_id'] ?? null,
            'booking_type' => empty($data['parent_id']) ? $data['booking_type'] : null,
            'template_key' => !empty($data['parent_id']) ? $data['template_key'] : null,
            'user_id'      => auth()->id(),
            'title'        => $data['title'],
            'subtitle'     => $data['subtitle'] ?? null,
            'slug'         => !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']),
            'description'  => $data['description'] ?? null,
            'icon'         => $data['icon'] ?? null,
            'order'        => !empty($data['order']) ? $data['order'] : $category->order,
            'status'       => isset($data['status']) && $data['status'] === 'on',
        ]);

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_updated_successfully'));
    }

    public function delete($id)
    {
        $this->authorize('admin_booking_categories_delete');
        BookingCategory::findOrFail($id)->delete();

        return redirect(getAdminPanelUrl('/booking/categories'))
            ->with('success', trans('admin/main.category_deleted_successfully'));
    }

    // ── Private ─────────────────────────────────────────────

    private function validateCategory(Request $request, ?int $ignoreId = null): void
    {
        $slugRule = ['nullable', 'string', 'max:255'];
        $slugRule[] = $ignoreId
            ? Rule::unique('booking_categories', 'slug')->ignore($ignoreId)
            : Rule::unique('booking_categories', 'slug');

        $isRoot = empty($request->parent_id);

        $this->validate($request, [
            'title'        => 'required|string|max:255',
            'subtitle'     => 'nullable|string|max:255',
            'slug'         => $slugRule,
            'description'  => 'nullable|string',
            'icon'         => 'nullable|string',
            'order'        => 'nullable|integer|min:0',

            'parent_id' => [
                'nullable',
                'exists:booking_categories,id',
                function ($attr, $value, $fail) {
                    if ($value) {
                        $parent = BookingCategory::find($value);
                        if ($parent && $parent->parent_id) {
                            $fail(trans('admin/main.subcategory_two_levels_only'));
                        }
                    }
                },
            ],

            // Required ONLY for a root category (this IS the booking type)
            'booking_type' => [
                Rule::requiredIf($isRoot),
                'nullable',
                Rule::in(array_keys(BookingTemplateConfig::allTypes())),
            ],

            // Required ONLY for a child category (this IS the template),
            // and it must belong to the parent's booking_type — this is the
            // "invalid combination" guard from the requirement doc.
            'template_key' => [
                Rule::requiredIf(!$isRoot),
                'nullable',
                Rule::in(array_keys(BookingTemplateConfig::TEMPLATES)),
                function ($attr, $value, $fail) use ($request, $isRoot) {
                    if ($isRoot || empty($value)) {
                        return;
                    }
                    $parent = BookingCategory::find($request->parent_id);
                    if (!$parent) {
                        return;
                    }
                    $templateParent = BookingTemplateConfig::parentOf($value);
                    if ($templateParent !== $parent->booking_type) {
                        $fail(trans('admin/main.template_does_not_match_booking_type'));
                    }
                },
            ],
        ]);
    }
}