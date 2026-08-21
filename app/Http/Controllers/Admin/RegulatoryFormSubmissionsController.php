<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
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
                'formSubmission.items',
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

        $submission = RegulatoryFormSubmission::findOrFail($id);
        $submission->status = 'approved';
        $submission->rejection_reason = null;
        $submission->reviewed_by = auth()->id();
        $submission->reviewed_at = now();
        $submission->save();

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

        $submission = RegulatoryFormSubmission::findOrFail($id);
        $submission->status = 'rejected';
        $submission->rejection_reason = $request->get('rejection_reason');
        $submission->reviewed_by = auth()->id();
        $submission->reviewed_at = now();
        $submission->save();

        return back()->with(['toast' => [
            'title'  => trans('public.request_success'),
            'msg'    => trans('update.regulatory_submission_rejected'),
            'status' => 'success',
        ]]);
    }
}