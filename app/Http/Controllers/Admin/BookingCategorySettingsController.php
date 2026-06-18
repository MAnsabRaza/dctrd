<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingCategorySettingsController extends Controller
{
    public function index(int $userId)
    {
        $data = $this->makeViewData($userId);

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($data);
        }

        return view('admin.users.editTabs.booking_settings', $data);
    }

    public function save(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:booking_categories,id'],
            'categories.*.enabled' => ['nullable'],
        ]);

        $allCategories = BookingCategory::query()
            ->select(['id', 'parent_id'])
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $submitted = collect($validated['categories'])
            ->mapWithKeys(fn ($row) => [(int) $row['id'] => (bool) ($row['enabled'] ?? false)])
            ->all();

        $resolved = [];
        foreach ($allCategories as $catId => $category) {
            $resolved[$catId] = (bool) ($submitted[$catId] ?? false);
        }

        foreach ($resolved as $catId => $enabled) {
            if (!$enabled) {
                continue;
            }

            $parentId = $allCategories[$catId]->parent_id ?? null;

            while (!empty($parentId)) {
                $resolved[(int) $parentId] = true;
                $parentId = $allCategories[$parentId]->parent_id ?? null;
            }
        }

        DB::transaction(function () use ($resolved) {
            foreach ($resolved as $catId => $enabled) {
                BookingCategory::where('id', $catId)->update([
                    'status' => (int) $enabled,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking categories saved successfully.',
        ]);
    }

    public function makeViewData(int $userId): array
    {
        $categories = BookingCategory::query()
            ->select(['id', 'parent_id', 'title', 'status', 'order'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $grouped = $categories->groupBy(fn ($item) => $item->parent_id ?: 0);

        $buildNode = function ($category, bool $parentEnabled = true) use (&$buildNode, $grouped) {
            $rawEnabled = (bool) $category->status;
            $effectiveEnabled = $parentEnabled && $rawEnabled;

            $children = collect($grouped->get((int) $category->id, []))
                ->map(fn ($child) => $buildNode($child, $effectiveEnabled))
                ->values()
                ->all();

            return [
                'id' => (int) $category->id,
                'title' => $category->title,
                'enabled' => $effectiveEnabled,
                'children' => $children,
            ];
        };

        $roots = collect($grouped->get(0, []))
            ->map(fn ($category) => $buildNode($category, true))
            ->values()
            ->all();

        return [
            'categoryTree' => $roots,
            'saveUrl' => route('admin.users.booking_settings.save', ['id' => $userId]),
        ];
    }
}