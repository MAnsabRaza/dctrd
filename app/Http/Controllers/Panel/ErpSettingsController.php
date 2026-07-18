<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\ErpCredential;
use App\Services\Erp\ErpCredentialService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Panel → Settings → APIs tab
 * Do-column layout (Image 4): "API Credentials Import/Export" | "API Credentials for DropShippers"
 */
class ErpSettingsController extends Controller
{
    public function __construct(protected ErpCredentialService $credentialService)
    {
    }

    /**
     * UserController@getUserEditPageData se call hota hai jab step == "apis"
     */
    public function viewData(int $vendorId): array
    {
        return [
            'erpImportExport'  => $this->credentialService->getOrNew($vendorId, 'import_export'),
            'erpDropshipping'  => $this->credentialService->getOrNew($vendorId, 'dropshipping'),
            'erpChecklistKeys' => ErpCredential::CHECKLIST_KEYS,
        ];
    }

    public function save(Request $request, string $type): JsonResponse
    {
        $this->authorize('panel_others_profile_setting');

        $user = auth()->user();

        if (!($user->isOrganization() || $user->isTeacher())) {
            abort(404);
        }

        $data = $request->validate([
            'base_url'                    => 'nullable|url|max:255',
            'export_ability_enabled'      => 'nullable|boolean',
            'import_dropshipping_enabled' => 'nullable|boolean',
            'rate_limit_per_minute'       => 'nullable|integer|min:1|max:6000',
            'checklist'                   => 'nullable|array',
        ]);

        // Vendor khud "is_active" (subscribe) on/off nahi karta seedha yahan se — wo alag endpoint
        // (toggleSubscription) se hota hai taake accidental disable na ho.
        $credential = $this->credentialService->getOrNew($user->id, $type);
        $data['is_active'] = $credential->is_active;

        $this->credentialService->save($user->id, $type, $data);

        return response()->json([
            'success' => true,
            'message' => trans('panel.saved_successfully') ?? 'Saved.',
        ]);
    }

    public function toggleSubscription(Request $request, string $type): JsonResponse
    {
        $this->authorize('panel_others_profile_setting');

        $user = auth()->user();
        $credential = $this->credentialService->getOrNew($user->id, $type);
        $credential->exists ? null : $credential->save();

        $this->credentialService->toggleStatus($credential, $request->boolean('active'));

        return response()->json([
            'success' => true,
            'message' => trans('panel.saved_successfully') ?? 'Saved.',
            'api_key' => $credential->fresh()->api_key,
        ]);
    }

    public function regenerateKey(string $type): JsonResponse
    {
        $this->authorize('panel_others_profile_setting');

        $user = auth()->user();
        $credential = $this->credentialService->getOrNew($user->id, $type);
        $credential->exists ? null : $credential->save();

        $this->credentialService->regenerateKey($credential);

        return response()->json([
            'success' => true,
            'api_key' => $credential->fresh()->api_key,
        ]);
    }
}
