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
            ->select(['id', 'parent_id', 'status'])
            ->get()
            ->keyBy('id');

        $submitted = collect($validated['categories'])
            ->mapWithKeys(fn ($row) => [(int) $row['id'] => (bool) ($row['enabled'] ?? false)])
            ->all();

        $resolved = [];

        $resolveEnabled = function (int $categoryId) use (&$resolveEnabled, &$resolved, $submitted, $categories): bool {
            if (array_key_exists($categoryId, $resolved)) {
                return $resolved[$categoryId];
            }

            $category = $categories->get($categoryId);
            if (!$category) {
                return false;
            }

            $parentEnabled = true;
            if (!empty($category->parent_id)) {
                $parentEnabled = $resolveEnabled((int) $category->parent_id);
            }

            $resolved[$categoryId] = $parentEnabled && ($submitted[$categoryId] ?? true);

            return $resolved[$categoryId];
        };

        foreach ($categories as $categoryId => $category) {
            $resolveEnabled((int) $categoryId);
        }

        DB::transaction(function () use ($userId, $categories, $resolved) {
            foreach ($categories as $categoryId => $category) {
                BookingCategory::where('id', $categoryId)->update([
                    'status' => (bool) ($resolved[$categoryId] ?? true),
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

        $enabledMap = $categories
            ->pluck('status', 'id')
            ->map(fn ($value) => (bool) $value)
            ->all();

        $grouped = $categories->groupBy(fn ($item) => $item->parent_id ?: 0);

        $buildNode = function ($category, bool $parentEnabled = true) use (&$buildNode, $grouped, $enabledMap) {
            $rawEnabled = array_key_exists($category->id, $enabledMap)
                ? (bool) $enabledMap[$category->id]
                : true;

            $effectiveEnabled = $parentEnabled && $rawEnabled;

            $children = collect($grouped->get($category->id, []))
                ->map(fn ($child) => $buildNode($child, $effectiveEnabled))
                ->values()
                ->all();

            return [
                'id' => (int) $category->id,
                'title' => $category->title,
                'enabled' => $rawEnabled,
                'effective_enabled' => $effectiveEnabled,
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
