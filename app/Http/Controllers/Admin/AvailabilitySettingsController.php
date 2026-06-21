<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrgAvailabilityRule;
use App\Models\OrgAvailabilityRange;
// Agar aapke paas Asset aur AssetRange ke models hain toh unhe yahan import karein
// use App\Models\Asset;
// use App\Models\OrgAssetAvailabilityRange;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AvailabilitySettingsController extends Controller
{
    // View path exact file ke mutabiq
    protected string $viewPath = 'admin.users.editTabs.availability';

    /**
     * Standard User/Instructor Index
     */
    public function index()
    {
        $orgId = Auth::id();

        $rule = OrgAvailabilityRule::firstOrNew(
            ['org_id' => $orgId],
            [
                'availability_mode'                 => 'available_by_default',
                'product_specific_takes_precedence'  => false,
                'make_all_unavailable_by_default'    => false,
            ]
        );

        $ranges = OrgAvailabilityRange::where('org_id', $orgId)
            ->orderBy('id')
            ->get();

        // Optional: Empty collections agar models exist nahi karte taake view crash na ho
        $assets = collect(); 
        $assetRanges = collect();

        // CRITICAL FIX: 'orgId', 'assets', aur 'assetRanges' ko yahan se lazmi pass karna hai
        return view($this->viewPath, compact('rule', 'ranges', 'orgId', 'assets', 'assetRanges'));
    }

    /**
     * Save availability settings (User Side)
     */
    public function save(Request $request): JsonResponse
    {
        return $this->processSave($request, Auth::id());
    }

    /**
     * AJAX: Delete a specific range row by ID.
     */
    public function deleteRow(int $userId, int $id): JsonResponse
    {
        $deleted = OrgAvailabilityRange::where('id', $id)
            ->where('org_id', $userId)
            ->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Row not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

    // ─────────────────────────────────────────────────────────────────
    // ADMIN DEDICATED METHODS
    // ─────────────────────────────────────────────────────────────────

    /**
     * Admin view of org/instructor availability.
     */
    public function adminIndex(int $userId)
    {
        $rule = OrgAvailabilityRule::firstOrNew(
            ['org_id' => $userId],
            [
                'availability_mode'                 => 'available_by_default',
                'product_specific_takes_precedence'  => false,
                'make_all_unavailable_by_default'    => false,
            ]
        );

        $ranges = OrgAvailabilityRange::where('org_id', $userId)
            ->orderBy('id')
            ->get();

        $assets = collect(); 
        $assetRanges = collect();

        return view($this->viewPath, compact('rule', 'ranges', 'assets', 'assetRanges') + ['orgId' => $userId]);
    }

    /**
     * Admin save of org/instructor availability.
     */
    public function adminSave(Request $request, int $userId): JsonResponse
    {
        return $this->processSave($request, $userId);
    }

    /**
     * Centralized function to validate and save data.
     */
    private function processSave(Request $request, int $orgId): JsonResponse
    {
        $validated = $request->validate([
            'make_all_unavailable_by_default'   => ['nullable', 'integer'],
            'product_specific_takes_precedence' => ['nullable', 'integer'],
            'ranges'                            => ['nullable', 'array'],
            'ranges.*.range_type'               => ['required_with:ranges', Rule::in(['custom', 'daily', 'weekly', 'monthly', 'date_range'])],
            'ranges.*.from_date'                => ['required_with:ranges', 'date'],
            'ranges.*.to_date'                  => ['required_with:ranges', 'date', 'after_or_equal:ranges.*.from_date'],
            'ranges.*.bookable'                 => ['nullable', 'integer'],
        ]);

        try {
            DB::beginTransaction();

            // 1. Save or Update Rules
            OrgAvailabilityRule::updateOrCreate(
                ['org_id' => $orgId],
                [
                    'availability_mode'                 => $request->input('availability_mode', 'available_by_default'),
                    'make_all_unavailable_by_default'   => $request->input('make_all_unavailable_by_default', 0),
                    'product_specific_takes_precedence' => $request->input('product_specific_takes_precedence', 0),
                ]
            );

            // 2. Sync Ranges
            if ($request->has('ranges')) {
                // Purane records delete karke naye insert karne ka asan tarika
                OrgAvailabilityRange::where('org_id', $orgId)->delete();
                
                foreach ($request->input('ranges') as $rangeData) {
                    OrgAvailabilityRange::create([
                        'org_id'     => $orgId,
                        'range_type' => $rangeData['range_type'],
                        'from_date'  => $rangeData['from_date'],
                        'to_date'    => $rangeData['to_date'],
                        'bookable'   => $rangeData['bookable'] ?? 1,
                    ]);
                }
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Changes saved successfully!']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()], 500);
        }
    }
}
