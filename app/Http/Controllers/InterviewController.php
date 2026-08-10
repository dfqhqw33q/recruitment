<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Interview;
use App\Models\InterviewAssessment;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class InterviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Interview::with('application.applicant', 'application.jobPosting', 'interviewer')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->from && $request->to, fn($q) => $q->whereBetween('scheduled_at', [$request->from, $request->to]))
            ->orderBy('scheduled_at');

        $interviews = $query->paginate(15);
        return view('recruitment.interviews.index', compact('interviews'));
    }

    public function create(Request $request)
    {
        $applications = Application::with('applicant', 'jobPosting')
            ->whereIn('status', ['shortlisted', 'for_interview', 'assessed'])
            ->get();
        $interviewers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Recruitment Officer', 'Department Head', 'HR Administrator']);
        })->get();
        return view('recruitment.interviews.create', compact('applications', 'interviewers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id',
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'type' => 'required|in:technical,behavioral,panel,hr',
            'round' => 'required|integer|min:1',
            'duration_minutes' => 'required|integer|min:15',
            'notes' => 'nullable|string',
        ]);

        $data['scheduled_by'] = auth()->id();
        $data['status'] = 'scheduled';

        $interview = Interview::create($data);

        // Update application status
        $application = Application::find($data['application_id']);
        if ($application && in_array($application->status, ['shortlisted', 'under_review', 'screening'])) {
            $application->update(['status' => 'for_interview']);
        }

        app(ActivityLogService::class)->log(
            'schedule', 'Interviews',
            "Interview scheduled for application #{$interview->application_id}.",
            'Interview', $interview->id
        );

        // Send interview invitation email + in-app notification to applicant
        try {
            $interview->load('application.applicant', 'application.jobPosting');
            app(\App\Services\ApplicationStageNotificationService::class)->notifyInterviewScheduled($interview);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::info('Interview notification skipped: ' . $e->getMessage());
        }

        return redirect()->route('recruitment.interviews.show', $interview)
            ->with('success', 'Interview scheduled and applicant notified.');
    }

    public function show(Interview $interview)
    {
        $interview->load(['application.applicant', 'application.jobPosting', 'interviewer', 'assessment']);
        return view('recruitment.interviews.show', compact('interview'));
    }

    public function edit(Interview $interview)
    {
        $interviewers = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['Recruitment Officer', 'Department Head', 'HR Administrator']);
        })->get();
        return view('recruitment.interviews.edit', compact('interview', 'interviewers'));
    }

    public function update(Request $request, Interview $interview)
    {
        $data = $request->validate([
            'interviewer_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'location' => 'nullable|string',
            'meeting_link' => 'nullable|url',
            'type' => 'required|in:technical,behavioral,panel,hr',
            'duration_minutes' => 'required|integer|min:15',
            'notes' => 'nullable|string',
            'status' => 'required|in:scheduled,completed,cancelled,no_show',
        ]);

        $interview->update($data);

        app(ActivityLogService::class)->log(
            'update', 'Interviews',
            "Interview #{$interview->id} updated.",
            'Interview', $interview->id
        );

        return redirect()->route('recruitment.interviews.show', $interview)
            ->with('success', 'Interview updated.');
    }

    public function destroy(Interview $interview)
    {
        $interview->delete();
        return redirect()->route('recruitment.interviews.index')->with('success', 'Interview deleted.');
    }

    public function calendar(Request $request)
    {
        $interviews = Interview::with('application.applicant', 'application.jobPosting')
            ->where('status', 'scheduled')
            ->get();
        return view('recruitment.interviews.calendar', compact('interviews'));
    }
}
