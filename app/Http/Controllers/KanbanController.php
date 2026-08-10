<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\JobPosting;
use App\Services\ActivityLogService;
use App\Services\ApplicationStageNotificationService;
use Illuminate\Http\Request;

class KanbanController extends Controller
{
    /**
     * Ordered pipeline columns displayed on the Kanban board.
     */
    private const COLUMNS = [
        'submitted'     => ['label' => 'Submitted',    'color' => 'blue',   'icon' => 'fa-paper-plane'],
        'under_review'  => ['label' => 'Under Review', 'color' => 'indigo', 'icon' => 'fa-magnifying-glass'],
        'screening'     => ['label' => 'Screening',    'color' => 'purple', 'icon' => 'fa-clipboard-list'],
        'shortlisted'   => ['label' => 'Shortlisted',  'color' => 'emerald','icon' => 'fa-star'],
        'for_interview' => ['label' => 'Interview',    'color' => 'amber',  'icon' => 'fa-video'],
        'assessed'      => ['label' => 'Assessed',     'color' => 'teal',   'icon' => 'fa-chart-bar'],
        'recommended'   => ['label' => 'Recommended',  'color' => 'green',  'icon' => 'fa-thumbs-up'],
        'hired'         => ['label' => 'Hired',        'color' => 'emerald','icon' => 'fa-champagne-glasses'],
    ];

    public function index(Request $request)
    {
        $postings = JobPosting::orderBy('title')->get();
        $jobPostingId = $request->job_posting_id;

        $query = Application::with('applicant', 'jobPosting', 'aiRecommendation')
            ->whereNotIn('status', ['rejected', 'withdrawn'])
            ->when($jobPostingId, fn($q) => $q->where('job_posting_id', $jobPostingId))
            ->latest('applied_at');

        $all = $query->get();

        // Group by status into the ordered columns
        $columns = collect(self::COLUMNS)->map(function ($meta, $key) use ($all) {
            return array_merge($meta, [
                'status'       => $key,
                'applications' => $all->where('status', $key)->values(),
            ]);
        });

        return view('recruitment.applications.kanban', compact('columns', 'postings', 'jobPostingId'));
    }

    /**
     * AJAX: Move a card to a new column (drag-and-drop).
     */
    public function move(Request $request, Application $application)
    {
        $data = $request->validate([
            'status' => 'required|string|in:' . implode(',', array_keys(self::COLUMNS)),
        ]);

        $old = $application->status;
        $new = $data['status'];

        if ($old === $new) {
            return response()->json(['ok' => true]);
        }

        $application->update(['status' => $new]);

        // Trigger stage notification
        try {
            app(ApplicationStageNotificationService::class)->notifyStatusChange($application, $old, $new);
        } catch (\Throwable $e) {
            // non-fatal
        }

        app(ActivityLogService::class)->log(
            'kanban_move', 'Recruitment',
            "Application #{$application->id} moved from '{$old}' to '{$new}' via Kanban.",
            'Application', $application->id,
            ['status' => $old], ['status' => $new]
        );

        return response()->json(['ok' => true, 'new_status' => $new]);
    }

    /**
     * Bulk status update (from applicant pool table).
     */
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

        $notifier = app(ApplicationStageNotificationService::class);

        foreach ($applications as $application) {
            $old = $application->status;
            $updateData = ['status' => $newStatus];
            if ($newStatus === 'rejected' && $data['rejection_reason']) {
                $updateData['rejection_reason'] = $data['rejection_reason'];
            }
            $application->update($updateData);

            try {
                $notifier->notifyStatusChange($application, $old, $newStatus);
            } catch (\Throwable $e) {
                // non-fatal
            }
        }

        app(ActivityLogService::class)->log(
            'bulk_action', 'Recruitment',
            "Bulk action '{$newStatus}' applied to " . count($data['ids']) . " applications.",
            'Application', null
        );

        return back()->with('success', count($data['ids']) . ' applications updated to "' . str_replace('_', ' ', ucfirst($newStatus)) . '" and candidates notified.');
    }
}
