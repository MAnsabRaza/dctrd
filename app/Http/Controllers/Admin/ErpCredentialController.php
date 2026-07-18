<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErpCredential;
use App\Services\Erp\ErpCredentialService;
use App\User;
use Illuminate\Http\Request;

/**
 * Admin → Edit User → "APIs" tab
 * Do blocks: type=import_export ("API Credentials Import/Export")
 *            type=dropshipping  ("API Credentials for DropShippers")
 */
class ErpCredentialController extends Controller
{
    public function __construct(protected ErpCredentialService $credentialService)
    {
    }

    /**
     * View data helper — UserController@edit isko call karke $data['erpCredentials'] set karega
     */
    public function viewData(int $userId): array
    {
        return [
            'erpImportExport' => $this->credentialService->getOrNew($userId, 'import_export'),
            'erpDropshipping' => $this->credentialService->getOrNew($userId, 'dropshipping'),
            'erpChecklistKeys' => ErpCredential::CHECKLIST_KEYS,
        ];
    }

    public function save(Request $request, $userId, string $type)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($userId);

        $data = $request->validate([
            'base_url'                    => 'nullable|url|max:255',
            'is_active'                   => 'nullable|boolean',
            'export_ability_enabled'      => 'nullable|boolean',
            'import_dropshipping_enabled' => 'nullable|boolean',
            'rate_limit_per_minute'       => 'nullable|integer|min:1|max:6000',
            'checklist'                   => 'nullable|array',
        ]);

        $this->credentialService->save($user->id, $type, $data);

        return redirect(getAdminPanelUrl("/users/{$user->id}/edit?tab=apis"))
            ->with('msg', trans('admin/main.save_change'));
    }

    public function regenerateKey($userId, string $type)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($userId);
        $credential = $this->credentialService->getOrNew($user->id, $type);
        $credential->exists ? null : $credential->save();

        $this->credentialService->regenerateKey($credential);

        return redirect(getAdminPanelUrl("/users/{$user->id}/edit?tab=apis"))
            ->with('msg', trans('admin/main.save_change'));
    }

    public function toggleStatus(Request $request, $userId, string $type)
    {
        $this->authorize('admin_users_edit');

        $user = User::findOrFail($userId);
        $credential = $this->credentialService->getOrNew($user->id, $type);
        $credential->exists ? null : $credential->save();

        $this->credentialService->toggleStatus($credential, $request->boolean('active'));

        return redirect(getAdminPanelUrl("/users/{$user->id}/edit?tab=apis"))
            ->with('msg', trans('admin/main.save_change'));
    }
}
