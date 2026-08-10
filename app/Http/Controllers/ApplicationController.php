<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\JobPosting;
use App\Services\ActivityLogService;
use App\Services\AiRecommendationService;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Application::with('applicant', 'jobPosting', 'aiRecommendation')
            ->when($request->search, function ($q, $s) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('reference_code', 'like', "%{$s}%")
                        ->orWhereHas('applicant', function ($a) use ($s) {
                            $a->where('first_name', 'like', "%{$s}%")
                              ->orWhere('last_name', 'like', "%{$s}%")
                              ->orWhere('email', 'like', "%{$s}%")
                              ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$s}%"]);
                        })
                        ->orWhereHas('jobPosting', function ($j) use ($s) {
                            $j->where('title', 'like', "%{$s}%");
                        });
                });
            })
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->job_posting_id, fn($q, $j) => $q->where('job_posting_id', $j))
            ->latest('applied_at');

        $applications = $query->paginate(10);
        $postings = JobPosting::all();
        $statuses = config('recruitment.application_statuses', []);

        return view('recruitment.applications.index', compact('applications', 'postings', 'statuses'));
    }

    public function show(Application $application)
    {
        $application->load([
            'applicant.education', 'applicant.experiences', 'applicant.skills',
            'applicant.certifications', 'applicant.documents',
            'jobPosting', 'interviews.assessment', 'aiRecommendation', 'offerLetter', 'documents',
        ]);
        return view('recruitment.applications.show', compact('application'));
    }

    public function updateStatus(Request $request, Application $application)
    {
        $data = $request->validate([
            'status' => 'required|string',
            'screening_notes' => 'nullable|string',
            'rejection_reason' => 'nullable|string',
        ]);

        $data['status'] = strtolower(str_replace([' ', '-'], '_', trim($data['status'])));
        if ($data['status'] === 'interview_scheduled') {
            $data['status'] = 'for_interview';
        }

        $old = $application->status;
        $application->update($data);

        if (in_array($data['status'], ['screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'hired']) && !$application->reviewed_at) {
            $application->update(['reviewed_at' => now(), 'reviewed_by' => auth()->id()]);
        }

        $application->refresh();

        // Auto-generate AI recommendation when shortlisted
        if (in_array($data['status'], ['shortlisted', 'for_interview', 'assessed', 'recommended'])) {
            app(AiRecommendationService::class)->generate($application);
        }

        // Notify applicant of status change via email and in-app notification
        app(\App\Services\ApplicationStageNotificationService::class)->notifyStatusChange($application, $old, $data['status']);

        app(ActivityLogService::class)->log(
            'status_change', 'Recruitment',
            "Application #{$application->id} status changed from '{$old}' to '{$data['status']}'.",
            'Application', $application->id, ['status' => $old], ['status' => $data['status']]
        );

        return back()->with('success', 'Application status updated and applicant notified.');
    }

    public function shortlist(Application $application)
    {
        $old = $application->status;
        $application->update(['status' => 'shortlisted']);
        app(AiRecommendationService::class)->generate($application);

        app(\App\Services\ApplicationStageNotificationService::class)->notifyStatusChange($application, $old, 'shortlisted');

        return back()->with('success', 'Candidate shortlisted and notified.');
    }

    public function reject(Request $request, Application $application)
    {
        $request->validate(['rejection_reason' => 'required|string']);
        $old = $application->status;
        $application->update(['status' => 'rejected', 'rejection_reason' => $request->rejection_reason]);

        app(\App\Services\ApplicationStageNotificationService::class)->notifyStatusChange($application, $old, 'rejected');

        return back()->with('success', 'Candidate rejected and notified.');
    }

    public function withdraw(Application $application)
    {
        $old = $application->status;
        $application->update(['status' => 'withdrawn']);

        app(\App\Services\ApplicationStageNotificationService::class)->notifyStatusChange($application, $old, 'withdrawn');

        return back()->with('success', 'Application marked as withdrawn.');
    }

    public function bulkAction(Request $request)
    {
        $data = $request->validate([
            'ids'    => 'required|array',
            'ids.*'  => 'integer|exists:applications,id',
            'action' => 'required|in:shortlist,reject,under_review,screening,for_interview,assessed,recommended,hired',
            'rejection_reason' => 'nullable|string',
        ]);

        $applications = Application::whereIn('id', $data['ids'])->get();
        $newStatus = $data['action'] === 'shortlist' ? 'shortlisted' : $data['action'];

        $notifier = app(\App\Services\ApplicationStageNotificationService::class);
        $count = 0;

        foreach ($applications as $application) {
            $old = $application->status;
            $updateData = ['status' => $newStatus];

            if ($newStatus === 'rejected' && !empty($data['rejection_reason'])) {
                $updateData['rejection_reason'] = $data['rejection_reason'];
            }

            $application->update($updateData);
            $count++;

            try {
                $notifier->notifyStatusChange($application, $old, $newStatus);
            } catch (\Throwable $e) {
                // Non-fatal
            }
        }

        app(ActivityLogService::class)->log(
            'bulk_action', 'Recruitment',
            "Bulk action '{$data['action']}' applied to {$count} application(s).",
            'Application', null
        );

        return back()->with('success', "Bulk action successfully applied to {$count} applicant(s).");
    }
}