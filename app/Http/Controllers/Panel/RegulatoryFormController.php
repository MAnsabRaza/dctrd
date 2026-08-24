<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Region;
use App\Models\Form;
use App\Models\RegulatoryFormSubmission;
use App\Models\UserRoleRequest;
use Illuminate\Http\Request;
use App\Models\FormSubmission;
use App\Models\FormSubmissionItem;
use Illuminate\Support\Facades\DB;

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

        if (!empty($status) and $status !== 'all') {
            $query->where('status', $status);
        }

        $submissions = $query->orderByDesc('created_at')->paginate(15);

        return view('design_1.panel.regulatory.index', [
            'pageTitle'   => 'Regulatory List',
            'submissions' => $submissions,
            'status'      => $status,
        ]);
    }

    /**
     * User ke sare (active + pending) roles ke stacked regulatory forms dikhao.
     */
   public function show()
{
    $user = auth()->user();

    $userRoles = UserRoleRequest::where('user_id', $user->id)
        ->where('status', UserRoleRequest::STATUS_ACTIVE)
        ->with('roleCatalog')
        ->get();

    $stacks = [];

    foreach ($userRoles as $userRole) {
        $roleCatalogId = $userRole->role_catalog_id;

        $primaryForm = Form::where('connect_regulatory', true)
            ->where('regulatory_role_catalog_id', $roleCatalogId)
            ->where('regulatory_level', 'primary')
            ->where('enable', true)
            ->with('fields.options')
            ->first();

        if (empty($primaryForm)) {
            continue;
        }

      $primarySubmission = RegulatoryFormSubmission::where('user_id', $user->id)
    ->where('role_catalog_id', $roleCatalogId)
    ->where('level', 'primary')
    ->first();

        $extraForms = Form::where('connect_regulatory', true)
            ->where('regulatory_role_catalog_id', $roleCatalogId)
            ->whereIn('regulatory_level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
            ->where('enable', true)
            ->with('fields.options')
            ->get();

        $extraSubmissions = RegulatoryFormSubmission::where('user_id', $user->id)
            ->where('role_catalog_id', $roleCatalogId)
            ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
            ->get()
            ->groupBy('form_id');

        $stacks[] = [
            'role'              => $userRole->roleCatalog,
            'roleRequestStatus' => $userRole->status,
            'primaryForm'       => $primaryForm,
            'primarySubmission' => $primarySubmission,
            'extraTemplates'    => $extraForms,      // ⚠️ ye key blade expect karta hai
            'extraSubmissions'  => $extraSubmissions, // ⚠️ ye bhi
        ];
    }

    $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
        ->where('type', Region::$country)
        ->get();

    $userCountry = $user->country;

    if (!empty($user->country_id)) {
        $country = Region::where('id', $user->country_id)->first();
        $userCountry = $country->title ?? $userCountry;
    }

    return view('design_1.panel.settings.regulatory', [
        'pageTitle'   => 'Regulatory & Badges',
        'stacks'      => $stacks,
        'countries'   => $countries,
        'userCountry' => $userCountry,
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
        'form_id' => 'required|exists:forms,id',
    ]);

    $user = auth()->user();
    $form = Form::where('connect_regulatory', true)->findOrFail($data['form_id']);

    $this->assertUserHasActiveRole($user->id, $form->regulatory_role_catalog_id);

    $submission = RegulatoryFormSubmission::create([
        'user_id'         => $user->id,
        'role_catalog_id' => $form->regulatory_role_catalog_id,
        'form_id'         => $form->id,
        'level'           => $form->regulatory_level,
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

        // Server-side check: submission jis role_catalog_id ki hai, us par user ka ACTIVE role hona chahiye
        $this->assertUserHasActiveRole($user->id, $submission->role_catalog_id);

        $submission->delete();

        return response()->json(['code' => 200]);
    }

    /**
     * Verify karo ke authenticated user ke paas is role_catalog_id
     * ke liye ACTIVE role hai. Warna 403 abort.
     */
    private function assertUserHasActiveRole($userId, $roleCatalogId): void
    {
        $hasActiveRole = UserRoleRequest::where('user_id', $userId)
            ->where('role_catalog_id', $roleCatalogId)
            ->where('status', UserRoleRequest::STATUS_ACTIVE)
            ->exists();

        abort_unless($hasActiveRole, 403, 'Is regulatory form ke liye aapka role active nahi hai.');
    }

  private function storeSubmission(Request $request, string $status)
{
    $data = $request->validate([
        'form_id'       => 'required|exists:forms,id',
        'submission_id' => 'nullable|integer',
        'fields'        => 'nullable|array',
    ]);

    $user = auth()->user();
    $form = Form::where('connect_regulatory', true)->findOrFail($data['form_id']);

    $this->assertUserHasActiveRole($user->id, $form->regulatory_role_catalog_id);

    $submissionId = !empty($data['submission_id']) ? (int) $data['submission_id'] : null;
    $submission = null;

    if ($submissionId) {
        $submission = RegulatoryFormSubmission::where('id', $submissionId)
            ->where('user_id', $user->id)
            ->first();
    }

    if (empty($submission)) {
        $submission = RegulatoryFormSubmission::where('user_id', $user->id)
            ->where('form_id', $form->id)
            ->first();
    }

    if (empty($submission)) {
        $submission = new RegulatoryFormSubmission();
        $submission->user_id = $user->id;
    }

    $fields = $data['fields'] ?? [];

    // ── Required fields check (sirf Submit for Review par) ──
    if ($status === 'pending') {
        foreach ($form->fields as $field) {
            if (!$field->required) {
                continue;
            }

            $key = 'field_' . $field->id;
            $val = $fields[$key] ?? null;
            $isEmpty = is_array($val) ? empty($val) : ($val === null or $val === '');

            if ($isEmpty) {
                abort(response()->json([
                    'message' => 'Please fill in all required fields.',
                    'errors' => [$key => ['This field is required.']],
                ], 422));
            }
        }
    }

    // ✅ Pending/Draft stage par sirf regulatory_form_submissions.data (JSON) mein save hota hai.
    // Form Builder ke form_submissions / form_submission_items tables ko YAHAN se bilkul touch nahi
    // karte — wo sirf Admin approve kare tab banenge (RegulatoryFormSubmissionsController@approve).
    $submission->role_catalog_id = $form->regulatory_role_catalog_id;
    $submission->form_id         = $form->id;
    $submission->level           = $form->regulatory_level;
    $submission->data            = array_filter($fields, fn($value) => $value !== null and $value !== '');
    $submission->status          = $status;

    // agar user dubara draft save kare to purana rejection reason clear ho jaye
    if ($status !== 'rejected') {
        $submission->rejection_reason = null;
    }

     $submission->save();

    // ── Admin ko notify karo jab user "Submit for Review" kare (draft save par nahi) ──
    if ($status === 'pending') {
        sendNotification('regulatory_submission_created', [
            '[u.name]'     => $user->full_name,
            '[form.title]' => $form->title,
            '[request.id]' => $submission->id,
            '[link]'       => getAdminPanelUrl('/regulatory-submissions/' . $submission->id . '/show'),
        ], 1); // 1 = admin user id, jaisa baaki jagah pattern hai
    }

    return response()->json([
        'code'          => 200,
        'submission_id' => $submission->id,
        'msg'           => $status === 'pending' ? 'Form submitted for review — admin approval ka intezar hai.' : 'Draft saved.',
        'status'        => $submission->status,
    ]);
}
}
