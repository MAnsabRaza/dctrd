<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\RegulatoryFormSubmission;
use App\Services\RegulatoryAccessService;
use Illuminate\Http\Request;

class RegulatoryFormController extends Controller
{
    public function list(Request $request)
    {
        $user = auth()->user();
        $status = $request->get('status', 'all');

        $query = RegulatoryFormSubmission::where('user_id', $user->id)
            ->with([
                'roleCatalog',
                'form.fields' => function ($query) {
                    $query->orderBy('order', 'asc')->with(['options' => function ($q) {
                        $q->orderBy('order', 'asc');
                    }]);
                },
                'reviewedBy',
            ]);

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        $submissions = $query->orderByDesc('created_at')->paginate(15);

        return view('design_1.panel.regulatory.index', [
            'pageTitle' => 'Regulatory List',
            'submissions' => $submissions,
            'status' => $status,
        ]);
    }

    public function show(RegulatoryAccessService $regulatoryAccess)
    {
        return redirect('/panel/setting/step/regulatory');
    }

    public function saveDraft(Request $request)
    {
        return $this->storeSubmission($request, 'draft');
    }

    public function submitForReview(Request $request)
    {
        return $this->storeSubmission($request, 'pending');
    }

    public function addSlot(Request $request, RegulatoryAccessService $regulatoryAccess)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
        ]);

        $user = auth()->user();
        $form = $regulatoryAccess->accessibleFormForUser($user, (int) $data['form_id']);

        $lockedSubmission = RegulatoryFormSubmission::where('user_id', $user->id)
            ->where('role_catalog_id', $form->regulatory_role_catalog_id)
            ->where('form_id', $form->id)
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        abort_if($lockedSubmission, 422, 'This form is already pending or approved. You can resubmit only after rejection.');

        $submission = RegulatoryFormSubmission::create([
            'user_id' => $user->id,
            'role_catalog_id' => $form->regulatory_role_catalog_id,
            'form_id' => $form->id,
            'level' => $form->regulatory_level,
            'data' => [],
            'status' => 'draft',
        ]);

        return response()->json([
            'code' => 200,
            'submission_id' => $submission->id,
        ]);
    }

    public function destroy($submissionId, RegulatoryAccessService $regulatoryAccess)
    {
        $user = auth()->user();

        $submission = RegulatoryFormSubmission::where('id', $submissionId)
            ->where('user_id', $user->id)
            ->where('status', 'draft')
            ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
            ->firstOrFail();

        $regulatoryAccess->assertUserHasActiveRole($user, (int) $submission->role_catalog_id);

        $submission->delete();

        return response()->json(['code' => 200]);
    }

    private function storeSubmission(Request $request, string $status)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
            'submission_id' => 'nullable|integer',
            'fields' => 'nullable|array',
        ]);

        $user = auth()->user();
        $submission = app(RegulatoryAccessService::class)->storeSubmission(
            $user,
            (int) $data['form_id'],
            $data['fields'] ?? [],
            $status,
            !empty($data['submission_id']) ? (int) $data['submission_id'] : null
        );

        if ($status === 'pending') {
            sendNotification('regulatory_submission_created', [
                '[u.name]' => $user->full_name,
                '[form.title]' => optional($submission->form)->title,
                '[request.id]' => $submission->id,
                '[link]' => getAdminPanelUrl('/regulatory-submissions/' . $submission->id . '/show'),
            ], 1);
        }

        return response()->json([
            'code' => 200,
            'submission_id' => $submission->id,
            'msg' => $status === 'pending' ? 'Form submitted for review. Admin approval is required.' : 'Draft saved.',
            'status' => $submission->status,
        ]);
    }
}
