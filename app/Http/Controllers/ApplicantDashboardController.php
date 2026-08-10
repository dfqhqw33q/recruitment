<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\NotificationRecord;
use Illuminate\Http\Request;

class ApplicantDashboardController extends Controller
{
    public function index(Request $request)
    {
        $applicant = auth()->user()->applicant;

        if (!$applicant) {
            return redirect()->route('applicant.profile')->with('info', 'Please complete your profile first.');
        }

        $applications = Application::with('jobPosting', 'aiRecommendation')
            ->where('applicant_id', $applicant->id)
            ->latest()
            ->get();

        $stats = [
            'total_applications' => $applications->count(),
            'active_applications' => $applications->whereIn('status', ['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended'])->count(),
            'interviews' => Interview::whereHas('application', fn($q) => $q->where('applicant_id', $applicant->id))->count(),
            'offers' => $applications->where('status', 'hired')->count(),
        ];

        $recommendedJobs = JobPosting::with('department', 'jobPosition')
            ->where('status', 'published')
            ->whereNotIn('id', $applications->pluck('job_posting_id')->filter())
            ->latest()
            ->limit(6)
            ->get();

        $notifications = NotificationRecord::where('user_id', auth()->id())
            ->latest()
            ->limit(5)
            ->get();

        return view('applicant.dashboard', compact('applicant', 'applications', 'stats', 'recommendedJobs', 'notifications'));
    }
}
