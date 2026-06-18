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
        return view('admin.users.editTabs.booking_settings', $this->makeViewData($userId));
    }

    public function save(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:booking_categories,id'],
            'categories.*.enabled' => ['nullable', 'boolean'],
        ]);

        $categories = BookingCategory::query()
            ->select(['id', 'parent_id'])
            ->get()
            ->keyBy('id');

        $submitted = collect($validated['categories'])
            ->mapWithKeys(fn ($row) => [(int) $row['id'] => (bool) ($row['enabled'] ?? false)])
            ->all();

        $resolved = [];
        foreach ($categories as $categoryId => $category) {
            $resolved[$categoryId] = (bool) ($submitted[$categoryId] ?? false);
        }

        foreach ($resolved as $categoryId => $isEnabled) {
            if (!$isEnabled) {
                continue;
            }

            $parentId = $categories[$categoryId]->parent_id ?? null;
            while (!empty($parentId)) {
                $resolved[(int) $parentId] = true;
                $parentId = $categories[$parentId]->parent_id ?? null;
            }
        }

        DB::transaction(function () use ($resolved) {
            foreach ($resolved as $categoryId => $isEnabled) {
                BookingCategory::where('id', $categoryId)->update([
                    'status' => (bool) $isEnabled,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking categories saved successfully.',
        ]);
    }

    private function makeViewData(int $userId): array
    {
        $categories = BookingCategory::query()
            ->orderBy('order')
            ->get();

        $grouped = $categories->groupBy(fn ($item) => $item->parent_id ?: 0);

        $buildNode = function ($category, bool $parentEnabled = true) use (&$buildNode, $grouped) {
            $rawEnabled = (bool) $category->status;
            $effectiveEnabled = $parentEnabled && $rawEnabled;

            $children = collect($grouped->get($category->id, []))
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
