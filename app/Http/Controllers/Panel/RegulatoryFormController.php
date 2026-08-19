<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\Region;
use App\Models\RegulatoryFormSubmission;
use App\Models\UserRoleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegulatoryFormController extends Controller
{
    public function show()
    {
        $user = auth()->user();

        $userRoles = UserRoleRequest::where('user_id', $user->id)
            ->where('status', UserRoleRequest::STATUS_ACTIVE)
            ->with('roleCatalog')
            ->get();

        $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
            ->where('type', Region::$country)
            ->get();

        $userCountry = $user->country;

        if (!empty($user->country_id)) {
            $country = Region::where('id', $user->country_id)->first();
            $userCountry = $country->title ?? $userCountry;
        }

        $stacks = [];

        foreach ($userRoles as $userRole) {
            $roleCatalogId = $userRole->role_catalog_id;

            // ── Primary form ─────────────────────────────────────────
            $primaryForm = Form::query()
                ->where('connect_regulatory', true)
                ->where('regulatory_role_catalog_id', $roleCatalogId)
                ->where('regulatory_level', 'primary')
                ->where('enable', true)
                ->with(['fields' => function ($q) {
                    $q->orderBy('order', 'asc')->with('options');
                }])
                ->first();

            if (empty($primaryForm) || !$this->formAppliesToCountry($primaryForm, $userCountry)) {
                continue; // is role ka koi primary regulatory form nahi mila
            }

            $primarySubmission = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('role_catalog_id', $roleCatalogId)
                ->where('form_id', $primaryForm->id)
                ->first();

            // ── Secondary/Tertiary/Quaternary/Extra1 (multi-slot: Branch/Warehouse) ──
            $extraForms = Form::query()
                ->where('connect_regulatory', true)
                ->where('regulatory_role_catalog_id', $roleCatalogId)
                ->whereIn('regulatory_level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
                ->where('enable', true)
                ->with(['fields' => function ($q) {
                    $q->orderBy('order', 'asc')->with('options');
                }])
                ->get()
                ->filter(fn($form) => $this->formAppliesToCountry($form, $userCountry))
                ->values();

            $extraSubmissions = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('role_catalog_id', $roleCatalogId)
                ->whereIn('form_id', $extraForms->pluck('id'))
                ->get()
                ->groupBy('form_id');

            // ── Level = NULL (badges/certificates — single apply, no slots) ──
            $badgeForms = Form::query()
                ->where('connect_regulatory', true)
                ->where('regulatory_role_catalog_id', $roleCatalogId)
                ->whereNull('regulatory_level')
                ->where('enable', true)
                ->with(['fields' => function ($q) {
                    $q->orderBy('order', 'asc')->with('options');
                }])
                ->get()
                ->filter(fn($form) => $this->formAppliesToCountry($form, $userCountry))
                ->values();

            $badgeSubmissions = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('role_catalog_id', $roleCatalogId)
                ->whereIn('form_id', $badgeForms->pluck('id'))
                ->get()
                ->keyBy('form_id');

            $stacks[] = [
                'role'              => $userRole->roleCatalog,
                'roleRequestStatus' => $userRole->status,
                'primaryForm'       => $primaryForm,
                'primarySubmission' => $primarySubmission,
                'extraForms'        => $extraForms,
                'extraSubmissions'  => $extraSubmissions,
                'badgeForms'        => $badgeForms,
                'badgeSubmissions'  => $badgeSubmissions,
            ];
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
     * "I want to add Branch" / "I want to add Warehouse" — naya secondary/tertiary slot.
     */
    public function addSlot(Request $request)
    {
        $data = $request->validate([
            'form_id' => 'required|exists:forms,id',
        ]);

        $user = auth()->user();
        $form = Form::where('id', $data['form_id'])
            ->where('connect_regulatory', true)
            ->firstOrFail();

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
            ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1']) // primary/badge delete nahi ho sakta
            ->firstOrFail();

        $this->assertUserHasActiveRole($user->id, $submission->role_catalog_id);

        $submission->delete();

        return response()->json(['code' => 200]);
    }

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
        $form = Form::where('id', $data['form_id'])
            ->where('connect_regulatory', true)
            ->firstOrFail();

        $this->assertUserHasActiveRole($user->id, $form->regulatory_role_catalog_id);

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

        $submission->role_catalog_id = $form->regulatory_role_catalog_id;
        $submission->form_id         = $form->id;
        $submission->level           = $form->regulatory_level;
        $submission->data            = $data['fields'] ?? [];
        $submission->status          = $status;
        $submission->save();

        return response()->json([
            'code'          => 200,
            'submission_id' => $submission->id,
            'msg'           => $status === 'pending'
                ? 'Form submitted for review — admin approval ka intezar hai.'
                : 'Draft saved.',
            'status'        => $submission->status,
        ]);
    }

    /**
     * Form ka regulatory_countries JSON check karta hai — agar khaali/null hai
     * to form har country ke liye applicable hai, warna user ka country list mein hona chahiye.
     */
    private function formAppliesToCountry(Form $form, $userCountry): bool
    {
        if (empty($form->regulatory_countries)) {
            return true;
        }

        $countries = json_decode($form->regulatory_countries, true) ?: [];

        if (empty($countries)) {
            return true;
        }

        return in_array($userCountry, $countries, true);
    }
}