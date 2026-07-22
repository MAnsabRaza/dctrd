<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RegulatoryFormSubmission;
use App\Models\RegulatoryFormTemplate;
use App\Models\UserRoleRequest;
use Illuminate\Http\Request;

class RegulatoryFormController extends Controller
{
    /**
     * User ke sare (active + pending) roles ke stacked regulatory forms dikhao.
     */
    public function show()
    {
        $user = auth()->user();

        $userRoles = UserRoleRequest::where('user_id', $user->id)
            ->whereIn('status', [UserRoleRequest::STATUS_ACTIVE, UserRoleRequest::STATUS_PENDING])
            ->with('roleCatalog')
            ->get();

        $stacks = [];

        foreach ($userRoles as $userRole) {
            $roleCatalogId = $userRole->role_catalog_id;

            $primaryTemplate = RegulatoryFormTemplate::where('role_catalog_id', $roleCatalogId)
                ->where('level', 'primary')
                ->where('active', true)
                ->first();

            if (empty($primaryTemplate)) {
                continue; // is role ka koi regulatory form define nahi hua
            }

            $primarySubmission = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('role_catalog_id', $roleCatalogId)
                ->where('level', 'primary')
                ->first();

            // Secondary/Tertiary/Quaternary templates (agar exist karte hon)
            $extraTemplates = RegulatoryFormTemplate::where('role_catalog_id', $roleCatalogId)
                ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
                ->where('active', true)
                ->get();

            // User ke already-added secondary/tertiary slots (multiple ho sakte hain — Branch1, Branch2, ...)
            $extraSubmissions = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('role_catalog_id', $roleCatalogId)
                ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
                ->get()
                ->groupBy('template_id');

            $stacks[] = [
                'role'              => $userRole->roleCatalog,
                'roleRequestStatus' => $userRole->status,
                'primaryTemplate'   => $primaryTemplate,
                'primarySubmission' => $primarySubmission,
                'extraTemplates'    => $extraTemplates,
                'extraSubmissions'  => $extraSubmissions,
            ];
        }

        return view('design_1.panel.settings.regulatory', [
            'pageTitle' => 'Regulatory & Badges',
            'stacks'    => $stacks,
        ]);
    }

    public function saveDraft(Request $request)
    {
        return $this->storeSubmission($request, 'draft');
    }

    public function submitForReview(Request $request)
    {
        return $this->storeSubmission($request, 'pending');
    }

    /**
     * "I want to add Brand" / "I want to add Warehouse" button — naya secondary/tertiary slot banata hai.
     */
    public function addSlot(Request $request)
    {
        $data = $request->validate([
            'template_id' => 'required|exists:regulatory_form_templates,id',
        ]);

        $user     = auth()->user();
        $template = RegulatoryFormTemplate::findOrFail($data['template_id']);

        $submission = RegulatoryFormSubmission::create([
            'user_id'         => $user->id,
            'role_catalog_id' => $template->role_catalog_id,
            'template_id'     => $template->id,
            'level'           => $template->level,
            'data'            => [],
            'status'          => 'draft',
        ]);

        return response()->json([
            'code'          => 200,
            'submission_id' => $submission->id,
        ]);
    }

    public function destroy($submissionId)
    {
        $user = auth()->user();

        $submission = RegulatoryFormSubmission::where('id', $submissionId)
            ->where('user_id', $user->id)
            ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1']) // primary delete nahi ho sakta
            ->firstOrFail();

        $submission->delete();

        return response()->json(['code' => 200]);
    }

  private function storeSubmission(Request $request, string $status)
{
    $data = $request->validate([
        'template_id'   => 'required|exists:regulatory_form_templates,id',
        'submission_id' => 'nullable|integer', // exists rule hata di — empty string safe rahegi
        'fields'        => 'nullable|array',
    ]);

    $user     = auth()->user();
    $template = RegulatoryFormTemplate::findOrFail($data['template_id']);

    // Empty string / 0 ko null treat karo
    $submissionId = !empty($data['submission_id']) ? (int) $data['submission_id'] : null;

    $submission = null;

    if ($submissionId) {
        $submission = RegulatoryFormSubmission::where('id', $submissionId)
            ->where('user_id', $user->id)
            ->first();
    }

    if (empty($submission)) {
        $submission = new RegulatoryFormSubmission();
        $submission->user_id = $user->id;
    }

    $submission->role_catalog_id = $template->role_catalog_id;
    $submission->template_id     = $template->id;
    $submission->level           = $template->level;
    $submission->data            = $data['fields'] ?? [];
    $submission->status          = $status;
    $submission->save();

    return response()->json([
        'code'          => 200,
        'submission_id' => $submission->id, // JS isay dobara use karega taake next save "update" ho, duplicate na bane
        'msg'           => $status === 'pending'
            ? 'Form submitted for review — admin approval ka intezar hai.'
            : 'Draft saved.',
        'status'        => $submission->status,
    ]);
}
}