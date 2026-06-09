<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CheckoutModule;
use App\Models\EntityCheckoutModule;
use App\Services\CheckoutModuleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutSettingsController extends Controller
{
    protected CheckoutModuleService $checkoutModuleService;

    public function __construct(CheckoutModuleService $checkoutModuleService)
    {
        $this->checkoutModuleService = $checkoutModuleService;
    }

    // =========================================================

    /**
     * METHOD 1: index()
     *
     * Org/Instructor ka checkout settings page dikhata hai.
     * Yahan woh decide karta hai ke kon sa module enable/disable karna hai.
     * URL: GET /panel/checkout-settings
     */
    public function index()
    {
        $orgId = Auth::id();

        // Service se saare modules + org ka enabled/disabled status lao
        $moduleSettings = $this->checkoutModuleService->getOrgModuleSettings($orgId);

        return view('panel.setting.checkout_options', [
            'pageTitle'      => trans('panel/checkout_settings.page_title'),
            'moduleSettings' => $moduleSettings,
        ]);
    }

    // =========================================================

    /**
     * METHOD 2: save()
     *
     * Org/Instructor ki module preferences save karta hai.
     * URL: POST /panel/checkout-settings/save
     * Returns: JSON (AJAX se call hota hai)
     */
    public function save(Request $request)
    {
        $request->validate([
            'modules'   => 'required|array',
            'modules.*' => 'boolean',
        ], [
            'modules.required' => trans('panel/checkout_settings.modules_required'),
            'modules.array'    => trans('panel/checkout_settings.modules_invalid'),
        ]);

        $orgId = Auth::id();

        try {
            // Service ko call karo — ['days' => true, 'hours' => false, ...]
            $this->checkoutModuleService->saveOrgModuleSettings(
                $orgId,
                $request->modules
            );

            // AJAX request hai?
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => trans('panel/checkout_settings.saved_successfully'),
                ]);
            }

            return redirect()
                ->route('panel.checkout-settings')
                ->with('success', trans('panel/checkout_settings.saved_successfully'));

        } catch (\Exception $e) {

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => trans('panel/checkout_settings.save_failed'),
                ], 500);
            }

            return back()->with('error', trans('panel/checkout_settings.save_failed'));
        }
    }

    // =========================================================

    /**
     * METHOD 3: saveEntityModules()
     *
     * Per-product / per-course / per-booking level pe
     * module override save karta hai.
     *
     * Matlab: ek specific product ke liye alag module settings ho sakti hain
     * chahe org level pe kuch aur set ho.
     *
     * URL: POST /panel/checkout-settings/entity
     * Returns: JSON
     */
    public function saveEntityModules(Request $request)
    {
        $request->validate([
            'entity_type'    => 'required|string|in:product,course,booking,bundle',
            'entity_id'      => 'required|integer|min:1',
            'modules'        => 'required|array',
            'modules.*.name'            => 'required|string',
            'modules.*.enabled'         => 'required|boolean',
            'modules.*.config_override' => 'nullable|array',
        ], [
            'entity_type.in'      => trans('panel/checkout_settings.invalid_entity_type'),
            'entity_id.required'  => trans('panel/checkout_settings.entity_id_required'),
            'modules.required'    => trans('panel/checkout_settings.modules_required'),
        ]);

        $orgId      = Auth::id();
        $entityType = $request->entity_type;
        $entityId   = $request->entity_id;

        // Check karo — yeh entity is org ki hai?
        if (!$this->entityBelongsToOrg($entityType, $entityId, $orgId)) {
            return response()->json([
                'success' => false,
                'message' => trans('panel/checkout_settings.entity_not_found'),
            ], 403);
        }

        try {
            foreach ($request->modules as $moduleData) {

                // Module ka ID dhundo name se
                $module = CheckoutModule::where('name', $moduleData['name'])
                    ->where('is_active', true)
                    ->first();

                if (!$module) {
                    continue; // Unknown module — skip
                }

                // Entity level override updateOrCreate karo
                EntityCheckoutModule::updateOrCreate(
                    [
                        'entity_type' => $entityType,
                        'entity_id'   => $entityId,
                        'module_id'   => $module->id,
                    ],
                    [
                        'enabled'         => (bool) $moduleData['enabled'],
                        'config_override' => $moduleData['config_override'] ?? null,
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => trans('panel/checkout_settings.entity_saved_successfully'),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => trans('panel/checkout_settings.save_failed'),
            ], 500);
        }
    }

    // =========================================================
    // PRIVATE HELPERS
    // =========================================================

    /**
     * Check karo ke yeh product/course/booking
     * is logged-in org ki hai ya nahi.
     * Dusre org ka data change na ho sake.
     */
    private function entityBelongsToOrg(
        string $entityType,
        int $entityId,
        int $orgId
    ): bool {
        switch ($entityType) {

            case 'product':
                return \App\Models\Product::where('id', $entityId)
                    ->where('creator_id', $orgId)
                    ->exists();

            case 'course':
                // Rocket LMS mein courses "webinars" table mein hain
                return \App\Models\Webinar::where('id', $entityId)
                    ->where('creator_id', $orgId)
                    ->exists();

            case 'booking':
                return \App\Models\Booking::where('id', $entityId)
                    ->where('creator_id', $orgId)
                    ->exists();

            case 'bundle':
                return \App\Models\Bundle::where('id', $entityId)
                    ->where('creator_id', $orgId)
                    ->exists();

            default:
                return false;
        }
    }
}