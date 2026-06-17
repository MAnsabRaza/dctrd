<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgAvailabilityRule;
use App\Models\OrgAvailabilityRange;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AvailabilitySettingsController extends Controller
{
    // Aapke view folder structure ke mutabiq path define kiya hai
    protected string $viewPath = 'admin.user.edittab.aviabality';

    public function index()
    {
        $orgId = Auth::id();

        $rule = OrgAvailabilityRule::firstOrNew(
            ['org_id' => $orgId],
            [
                'availability_mode'                  => 'available_by_default',
                'product_specific_takes_precedence'  => false,
                'make_all_unavailable_by_default'    => false,
            ]
        );

        $ranges = OrgAvailabilityRange::where('org_id', $orgId)
            ->orderBy('id')
            ->get();

        return view($this->viewPath, compact('rule', 'ranges'));
    }

    /**
     * Save availability settings (full save — settings + ranges).
     */
    public function save(Request $request): JsonResponse
    {
        return $this->processSave($request, Auth::id());
    }

    /**
     * AJAX: Add a blank range row (returns HTML partial).
     */
    public function addRow(): JsonResponse
    {
        // Agar aapki partial file 'admin/user/edittab/partials/availability_row.blade.php' par hai:
        $html = view('admin.user.edittab.partials.availability_row', [
            'range'  => null,
            'index'  => 'new_' . time(),
        ])->render();

        return response()->json(['html' => $html]);
    }

    /**
     * AJAX: Delete a specific range row by ID.
     */
    public function deleteRow(int $id): JsonResponse
    {
        $orgId = Auth::id();

        $deleted = OrgAvailabilityRange::where('id', $id)
            ->where('org_id', $orgId)
            ->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN: /admin/users/{id}/availability
    // ─────────────────────────────────────────────────────────────────

    /**
     * Admin view of org/instructor availability.
     */
    public function adminIndex(int $userId)
    {
        $rule = OrgAvailabilityRule::firstOrNew(
            ['org_id' => $userId],
            [
                'availability_mode'                  => 'available_by_default',
                'product_specific_takes_precedence'  => false,
                'make_all_unavailable_by_default'    => false,
            ]
        );

        $ranges = OrgAvailabilityRange::where('org_id', $userId)
            ->orderBy('id')
            ->get();

        return view($this->viewPath, array_merge(
            compact('rule', 'ranges'),
            ['isAdmin' => true, 'orgId' => $userId]
        ));
    }

    /**
     * Admin save of org/instructor availability.
     */
    public function adminSave(Request $request, int $userId): JsonResponse
    {
        return $this->processSave($request, $userId);
    }

    // ─────────────────────────────────────────────────────────────────
    // PRIVATE HELPER METHOD (Validation aur Database Transaction ke liye)
    // ─────────────────────────────────────────────────────────────────
    
    /**
     * Centralized function to validate and save data for both User and Admin.
     */
    private function processSave(Request $request, int $orgId): JsonResponse
    {
        $validated = $request->validate([
            'availability_mode'                 => ['required', Rule::in(['available_by_default', 'unavailable_by_default'])],
            'make_all_unavailable_by_default'   => ['nullable', 'boolean'],
            'product_specific_takes_precedence' => ['nullable', 'boolean'],
            'ranges'                            => ['nullable', 'array'],
            'ranges.*.range_type'               => ['required_with:ranges', Rule::in(['custom', 'daily', 'weekly', 'monthly', 'date_range'])],
            'ranges.*.from_date'                => ['required_with:ranges', 'date'],
            'ranges.*.to_date'                  => ['required_with:ranges', 'date', 'after_or_equal:ranges.*.from_date'],
            'ranges.*.bookable'                 => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($orgId, $validated) {

            // 1. Upsert the rule row
            OrgAvailabilityRule::updateOrCreate(
                ['org_id' => $orgId],
                [
                    'availability_mode'                  => $validated['availability_mode'],
                    'make_all_unavailable_by_default'    => (bool) ($validated['make_all_unavailable_by_default'] ?? false),
                    'product_specific_takes_precedence'  => (bool) ($validated['product_specific_takes_precedence'] ?? false),
                ]
            );

            // 2. Replace all ranges for this org
            OrgAvailabilityRange::where('org_id', $orgId)->delete();

            if (!empty($validated['ranges'])) {
                $rows = collect($validated['ranges'])->map(fn ($r) => [
                    'org_id'     => $orgId,
                    'range_type' => $r['range_type'],
                    'from_date'  => $r['from_date'],
                    'to_date'    => $r['to_date'],
                    'bookable'   => (bool) ($r['bookable'] ?? true),
                    'created_at' => now(),
                    'updated_at' => now(),
                ])->toArray();

                OrgAvailabilityRange::insert($rows);
            }
        });

        return response()->json([
            'success' => true,
            'message' => trans('booking.availability_saved'),
        ]);
    }
}