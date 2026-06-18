<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingCategorySettingsController extends Controller
{
    // ─────────────────────────────────────────────────────────────
    // GET  /admin/users/{id}/booking-settings
    // ─────────────────────────────────────────────────────────────
    public function index(int $userId)
    {
        return view('admin.users.editTabs.booking_settings', $this->makeViewData($userId));
    }

    // ─────────────────────────────────────────────────────────────
    // POST /admin/users/{id}/booking-settings/save
    // ─────────────────────────────────────────────────────────────
    public function save(Request $request, int $userId): JsonResponse
    {
        $validated = $request->validate([
            'categories'           => ['required', 'array'],
            'categories.*.id'      => ['required', 'integer', 'exists:booking_categories,id'],
            'categories.*.enabled' => ['nullable'],
        ]);

        // All categories from DB (id + parent_id)
        $allCategories = BookingCategory::query()
            ->select(['id', 'parent_id'])
            ->get()
            ->keyBy('id');

        // submitted map: category_id => bool
        $submitted = collect($validated['categories'])
            ->mapWithKeys(fn ($row) => [(int) $row['id'] => (bool) ($row['enabled'] ?? false)])
            ->all();

        // Start with submitted values
        $resolved = [];
        foreach ($allCategories as $catId => $cat) {
            $resolved[$catId] = (bool) ($submitted[$catId] ?? false);
        }

        // If a child is enabled → its parent must also be enabled (cascade up)
        foreach ($resolved as $catId => $isEnabled) {
            if (!$isEnabled) continue;
            $parentId = $allCategories[$catId]->parent_id ?? null;
            while (!empty($parentId)) {
                $resolved[(int) $parentId] = true;
                $parentId = $allCategories[$parentId]->parent_id ?? null;
            }
        }

        // Save directly to booking_categories.status
        DB::transaction(function () use ($resolved) {
            foreach ($resolved as $catId => $isEnabled) {
                BookingCategory::where('id', $catId)
                    ->update(['status' => (int) $isEnabled]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking categories saved successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // Build tree from booking_categories.status
    // ─────────────────────────────────────────────────────────────
    private function makeViewData(int $userId): array
    {
        $categories = BookingCategory::query()
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        // Group by parent_id  (root nodes → key 0)
        $grouped = $categories->groupBy(fn ($item) => $item->parent_id ?: 0);

        $buildNode = function ($category, bool $parentEnabled = true) use (&$buildNode, $grouped) {

            $rawEnabled      = (bool) $category->status;
            $effectiveEnabled = $parentEnabled && $rawEnabled;

            $children = collect($grouped->get((int) $category->id, []))
                ->map(fn ($child) => $buildNode($child, $effectiveEnabled))
                ->values()
                ->all();

            return [
                'id'       => (int) $category->id,
                'title'    => $category->title,
                'enabled'  => $effectiveEnabled,
                'children' => $children,
            ];
        };

        $roots = collect($grouped->get(0, []))
            ->map(fn ($cat) => $buildNode($cat, true))
            ->values()
            ->all();

        return [
            'categoryTree' => $roots,
            'saveUrl'      => route('admin.users.booking_settings.save', ['id' => $userId]),
        ];
    }
}