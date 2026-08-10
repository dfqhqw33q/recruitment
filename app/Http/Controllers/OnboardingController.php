<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\EmployeeProfile;
use App\Models\Onboarding;
use App\Models\OnboardingChecklist;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    public function index(Request $request)
    {
        $query = Onboarding::with('application.applicant', 'application.jobPosting', 'employee')
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->latest();

        $onboardings = $query->paginate(15);
        return view('recruitment.onboarding.index', compact('onboardings'));
    }

    public function create(Request $request)
    {
        $applications = Application::with('applicant', 'jobPosting')
            ->where('status', 'hired')
            ->doesntHave('onboarding')
            ->get();
        return view('recruitment.onboarding.create', compact('applications'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'application_id' => 'required|exists:applications,id|unique:onboarding,application_id',
            'start_date' => 'required|date',
            'orientation_date' => 'required|date',
            'training_start' => 'nullable|date',
            'training_end' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        $data['assigned_to'] = auth()->id();
        $data['status'] = 'in_progress';
        $data['progress'] = 0;

        $onboarding = Onboarding::create($data);

        app(ActivityLogService::class)->log(
            'create', 'Onboarding',
            "Onboarding process started for application #{$onboarding->application_id}.",
            'Onboarding', $onboarding->id
        );

        return redirect()->route('recruitment.onboarding.show', $onboarding)
            ->with('success', 'Onboarding started.');
    }

    public function show(Onboarding $onboarding)
    {
        $onboarding->load(['application.applicant.skills', 'application.applicant.experiences', 'application.jobPosting', 'employee', 'assignedOfficer']);
        // Global onboarding checklist template used as the standard requirements
        $checklistTemplate = OnboardingChecklist::where('status', 'active')->orderBy('sort_order')->get();
        return view('recruitment.onboarding.show', compact('onboarding', 'checklistTemplate'));
    }

    public function update(Request $request, Onboarding $onboarding)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'orientation_date' => 'nullable|date',
            'training_start' => 'nullable|date',
            'training_end' => 'nullable|date',
            'notes' => 'nullable|string',
            'progress' => 'required|integer|min:0|max:100',
            'status' => 'required|in:pending,in_progress,completed,on_hold',
        ]);

        if ($data['status'] === 'completed') {
            $data['completed_at'] = now();
            $data['progress'] = 100;
        }

        $onboarding->update($data);

        app(ActivityLogService::class)->log(
            'update', 'Onboarding',
            "Onboarding #{$onboarding->id} updated (progress: {$onboarding->progress}%).",
            'Onboarding', $onboarding->id
        );

        return back()->with('success', 'Onboarding details and progress updated.');
    }

    public function updateChecklist(Request $request, Onboarding $onboarding)
    {
        $checkedIds = array_map('intval', $request->input('checklist_ids', []));
        $onboarding->completed_checklist_ids = $checkedIds;
        
        // Auto-recalculate progress dynamically across the 5 20% milestones
        $onboarding->progress = $onboarding->calculateProgress();
        
        if ($onboarding->progress >= 100 && $onboarding->status !== 'completed') {
            $onboarding->status = 'completed';
            $onboarding->completed_at = now();
        }

        $onboarding->save();

        app(ActivityLogService::class)->log(
            'update_checklist', 'Onboarding',
            "Checklist updated for onboarding #{$onboarding->id} (progress: {$onboarding->progress}%).",
            'Onboarding', $onboarding->id
        );

        return back()->with('success', 'Checklist saved and progress automatically updated (' . $onboarding->progress . '%).');
    }

    public function createEmployeeProfile(Onboarding $onboarding)
    {
        $application = $onboarding->application;
        $applicant = $application->applicant;

        // Create employee profile — use null-safe fallbacks for fields that may
        // be empty on applicants who registered without completing their profile.
        $employeeProfile = EmployeeProfile::updateOrCreate(
            ['applicant_id' => $applicant->id],
            [
                'user_id'           => $applicant->user_id,
                'employee_id'       => 'EMP-' . strtoupper(Str::random(6)),
                'first_name'        => $applicant->first_name,
                'last_name'         => $applicant->last_name,
                'email'             => $applicant->email,
                'phone'             => $applicant->phone ?? '',
                'date_of_birth'     => $applicant->date_of_birth ?? now()->subYears(25)->toDateString(),
                'gender'            => $applicant->gender ?? 'prefer_not_to_say',
                'address'           => $applicant->address ?? 'N/A',
                'city'              => $applicant->city ?? 'N/A',
                'state'             => $applicant->state ?? 'N/A',
                'country'           => $applicant->country ?? 'Philippines',
                'nationality'       => $applicant->nationality ?? 'Filipino',
                'job_position_id'   => $application->job_posting_id,
                'department_id'     => $application->jobPosting->department_id,
                'employment_status' => match($application->jobPosting->employment_type ?? '') {
                    'part_time'  => 'part_time',
                    'internship' => 'intern',
                    'contract'   => 'contractual',
                    default      => 'probationary',
                },
                'hire_date' => $onboarding->start_date,
                'status'    => 'active',
            ]
        );

        // Link employee to onboarding
        $onboarding->update(['employee_id' => $applicant->user_id]);

        app(ActivityLogService::class)->log(
            'employee_profile', 'Onboarding',
            "Employee profile created for {$applicant->full_name} (code: {$employeeProfile->employee_id}).",
            'EmployeeProfile', $employeeProfile->id
        );

        return back()->with('success', 'Employee profile created.');
    }

    public function destroy(Onboarding $onboarding)
    {
        $onboarding->delete();
        return redirect()->route('recruitment.onboarding.index')->with('success', 'Onboarding deleted.');
    }
}
