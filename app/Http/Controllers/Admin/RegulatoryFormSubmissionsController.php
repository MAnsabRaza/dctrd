<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Form;
use App\Models\FormSubmission;
use App\Models\FormSubmissionItem;
use App\Models\RegulatoryFormSubmission;
use Illuminate\Http\Request;

class RegulatoryFormSubmissionsController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize("admin_regulatory_submissions");

        $status = $request->get('status', 'pending');

        $query = RegulatoryFormSubmission::query()
            ->with(['user', 'form', 'roleCatalog']);

        if (!empty($status) and $status !== 'all') {
            $query->where('status', $status);
        }

        $submissions = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.regulatory_submissions.index', [
            'pageTitle'   => trans('update.regulatory_submissions'),
            'submissions' => $submissions,
            'status'      => $status,
        ]);
    }

    public function show($id)
    {
        $this->authorize("admin_regulatory_submissions");

        $submission = RegulatoryFormSubmission::where('id', $id)
            ->with([
                'user',
                'roleCatalog',
                'form.fields' => function ($query) {
                    $query->orderBy('order', 'asc')->with(['options' => function ($q) {
                        $q->orderBy('order', 'asc');
                    }]);
                },
            ])
            ->firstOrFail();

        return view('admin.regulatory_submissions.show', [
            'pageTitle'  => trans('update.submission_details'),
            'submission' => $submission,
            'form'       => $submission->form,
        ]);
    }

        public function approve($id)
    {
        $this->authorize("admin_regulatory_submissions_review");

        $submission = RegulatoryFormSubmission::with(['form', 'user'])->findOrFail($id);

        if (empty($submission->form_submission_id)) {
            $formSubmission = FormSubmission::create([
                'user_id'    => $submission->user_id,
                'form_id'    => $submission->form_id,
                'created_at' => time(),
            ]);

            foreach ((array) $submission->data as $fieldKey => $value) {
                $fieldId = str_replace('field_', '', $fieldKey);

                FormSubmissionItem::query()->updateOrCreate([
                    'submission_id' => $formSubmission->id,
                    'form_field_id' => $fieldId,
                ], [
                    'value' => is_array($value) ? json_encode($value) : $value,
                ]);
            }

            $submission->form_submission_id = $formSubmission->id;
        }

        $submission->status = 'approved';
        $submission->rejection_reason = null;
        $submission->reviewed_by = auth()->id();
        $submission->reviewed_at = now();
        $submission->save();

        // ── Jis user ne data submit kiya tha usko notify karo ──
        sendNotification('regulatory_submission_approved', [
            '[u.name]'     => optional($submission->user)->full_name,
            '[form.title]' => optional($submission->form)->title,
            '[request.id]' => $submission->id,
            '[link]'       => url('/panel/setting/step/regulatory'),
        ], $submission->user_id);

        return back()->with(['toast' => [
            'title'  => trans('public.request_success'),
            'msg'    => trans('update.regulatory_submission_approved'),
            'status' => 'success',
        ]]);
    }

       public function reject(Request $request, $id)
    {
        $this->authorize("admin_regulatory_submissions_review");

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $submission = RegulatoryFormSubmission::with(['form', 'user'])->findOrFail($id);
        $submission->status = 'rejected';
        $submission->rejection_reason = $request->get('rejection_reason');
        $submission->reviewed_by = auth()->id();
        $submission->reviewed_at = now();
        $submission->save();

        // ── Jis user ne data submit kiya tha usko reason ke sath notify karo ──
        sendNotification('regulatory_submission_rejected', [
            '[u.name]'     => optional($submission->user)->full_name,
            '[form.title]' => optional($submission->form)->title,
            '[reason]'     => $submission->rejection_reason,
            '[request.id]' => $submission->id,
            '[link]'       => url('/panel/setting/step/regulatory'),
        ], $submission->user_id);

        return back()->with(['toast' => [
            'title'  => trans('public.request_success'),
            'msg'    => trans('update.regulatory_submission_rejected'),
            'status' => 'success',
        ]]);
    }
}