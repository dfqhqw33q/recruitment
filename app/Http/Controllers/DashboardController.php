<?php

namespace App\Http\Controllers;

use App\Models\AiPipelineInsight;
use App\Models\AiRecommendation;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\OfferLetter;
use App\Services\AiInsightService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        if ($user->hasRole('Employee')) {
            return redirect()->route('employee.dashboard');
        }

        if ($user->hasRole('Applicant')) {
            return app(ApplicantDashboardController::class)->index($request);
        }

        // ---- KPI Cards ----
        $totalApplicants = Applicant::count();
        $activeVacancies = JobPosting::where('status', 'published')->count();
        $candidatesInterviewed = Interview::where('status', 'completed')->count();
        $applicantsHired = Application::where('status', 'hired')->count();

        $totalApplications = max(Application::count(), 1);
        $offers = OfferLetter::count();
        $offerAcceptanceRate = $offers > 0 ? round((OfferLetter::where('status', 'accepted')->count() / $offers) * 100) : 0;

        // Time-to-hire (avg days from apply to hired)
        $hired = Application::where('status', 'hired')->whereNotNull('reviewed_at')->get();
        $timeToHire = $hired->count() > 0 ? round($hired->avg(fn($a) => $a->applied_at->diffInDays($a->reviewed_at))) : 0;

        // Cost per hire
        $totalCost = JobPosting::sum('estimated_cost');
        $costPerHire = $applicantsHired > 0 ? round($totalCost / $applicantsHired) : 0;

        // Recruitment success rate
        $recruitmentSuccessRate = $totalApplications > 0 ? round(($applicantsHired / $totalApplications) * 100) : 0;

        // ---- AI Insights ----
// AI recommendation distribution (aggregate)
        $aiDistribution = AiRecommendation::selectRaw('recommendation, count(*) as total')
            ->groupBy('recommendation')
            ->pluck('total', 'recommendation')
            ->toArray();

        $avgMatchScore = round(AiRecommendation::avg('match_score') ?? 0, 1);
        $avgConfidence = round(AiRecommendation::avg('confidence_score') ?? 0, 1);

        // ---- Charts Data ----
        // Recruitment funnel
        $funnelStages = ['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'];
        $funnel = [];
        foreach ($funnelStages as $stage) {
            $funnel[$stage] = Application::where('status', $stage)->count();
        }

        // Applicants per position
        $applicantsPerPosition = JobPosting::withCount('applications')
            ->orderByDesc('applications_count')
            ->limit(6)
            ->get()
            ->map(fn($p) => ['label' => $p->title, 'count' => $p->applications_count]);

// Skills distribution
        $skills = Applicant::with('skills')->get()->flatMap(fn($a) => $a->skills->pluck('skill'));
        $skillsDistribution = $skills->countBy()->sortDesc()->take(8);

// Average scores
        $avgInterviewScore = Interview::where('status', 'completed')->with('assessment')->get()
            ->filter(fn($i) => $i->assessment)
            ->avg(fn($i) => $i->assessment->average_score);
        $avgInterviewScore = round($avgInterviewScore ?: 0, 1);

        // ---- Skills Gap Analysis ----
        // Compare required skills across postings vs. applicant skills
        $allApplicantSkills = Applicant::with('skills')->get()->flatMap(fn($a) => $a->skills->pluck('skill'))
            ->map(fn($s) => strtolower(trim($s)))->unique()->values();

        $skillsGap = [];
        $postings = JobPosting::all();
        foreach ($postings as $posting) {
            $required = $posting->required_skills ?? [];
            foreach ($required as $skill) {
                $skillKey = strtolower(trim($skill));
                if (!in_array($skillKey, $allApplicantSkills->all())) {
                    $skillsGap[$skillKey] = ($skillsGap[$skillKey] ?? 0) + 1;
                }
            }
        }
        arsort($skillsGap);
        $skillsGap = collect($skillsGap)->take(12);

        // Applicant supply per skill (for gap severity)
        $skillSupply = Applicant::with('skills')->get()->flatMap(fn($a) => $a->skills->pluck('skill'))
            ->map(fn($s) => strtolower(trim($s)))->countBy()->sortDesc()->take(12);

        // ---- Missing Skills Summary (from AI recommendations) ----
        $missingSkillsSummary = [];
        $aiRecs = AiRecommendation::whereNotNull('missing_skills')->get();
        foreach ($aiRecs as $rec) {
            foreach ($rec->missing_skills as $skill) {
                $skillKey = strtolower(trim($skill));
                $missingSkillsSummary[$skillKey] = ($missingSkillsSummary[$skillKey] ?? 0) + 1;
            }
        }
        arsort($missingSkillsSummary);
        $missingSkillsSummary = collect($missingSkillsSummary)->take(12);

        // ---- Interview Performance Analysis ----
        $assessments = Interview::where('status', 'completed')->with('assessment')->get()
            ->filter(fn($i) => $i->assessment)
            ->map(fn($i) => $i->assessment);

        $interviewPerformance = [
            'communication' => round($assessments->avg('communication_score') ?? 0, 1),
            'technical' => round($assessments->avg('technical_score') ?? 0, 1),
            'experience' => round($assessments->avg('experience_score') ?? 0, 1),
            'cultural_fit' => round($assessments->avg('cultural_fit_score') ?? 0, 1),
            'overall' => round($assessments->avg('average_score') ?? 0, 1),
            'count' => $assessments->count(),
        ];

        // ---- Recent Activity ----
        $upcomingInterviews = Interview::with('application.applicant', 'application.jobPosting')
            ->where('status', 'scheduled')
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $recentApplications = Application::with('applicant', 'jobPosting')
            ->latest()
            ->limit(5)
            ->get();

$recentOffers = OfferLetter::with('application.applicant')->latest()->limit(5)->get();

// ---- AI Pipeline Insights (aggregate narrative insights) ----
        // Manual-only behavior: on a normal page refresh the previously generated
        // AI insights are cleared so they only appear after clicking
        // "Generate AI Insights". The generate action stores insights and sets a
        // session flag so the redirect keeps them visible for that request.
        if (session()->pull('show_ai_insights')) {
            $pipelineInsights = AiPipelineInsight::orderBy('sort_order')->get();
        } else {
            AiPipelineInsight::query()->delete();
            $pipelineInsights = AiPipelineInsight::orderBy('sort_order')->get();
        }

// Prevent the browser from caching the dashboard page so a refresh
        // always shows the latest AI insights from the database.
        return response()
            ->view('dashboard', compact(
                'totalApplicants', 'activeVacancies', 'candidatesInterviewed', 'applicantsHired',
                'totalApplications', 'offers', 'offerAcceptanceRate', 'timeToHire', 'costPerHire', 'recruitmentSuccessRate',
                'aiDistribution', 'avgMatchScore', 'avgConfidence',
                'funnel', 'applicantsPerPosition',
                'skillsDistribution', 'avgInterviewScore', 'skillsGap',
                'missingSkillsSummary', 'interviewPerformance', 'pipelineInsights',
                'upcomingInterviews', 'recentApplications', 'recentOffers'
            ))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}
