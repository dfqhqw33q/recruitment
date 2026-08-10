<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Interview;
use App\Models\JobPosting;
use App\Models\OfferLetter;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // KPI Cards
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

        $kpis = compact(
            'totalApplicants', 'activeVacancies', 'candidatesInterviewed', 'applicantsHired',
            'offerAcceptanceRate', 'timeToHire', 'costPerHire', 'recruitmentSuccessRate'
        );

        // Charts data

        // Recruitment funnel
        $funnelStages = ['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'];
        $funnel = [];
        foreach ($funnelStages as $stage) {
            $funnel[$stage] = Application::where('status', $stage)->count();
        }

        // Applicants per position
        $applicantsPerPosition = JobPosting::withCount('applications')
            ->orderByDesc('applications_count')
            ->limit(8)
            ->get()
            ->map(fn($p) => ['label' => $p->title, 'count' => $p->applications_count]);

        // Monthly hiring trend (last 6 months)
        $monthlyHiring = Application::where('status', 'hired')
            ->where('applied_at', '>=', now()->subMonths(6))
            ->get()
            ->groupBy(fn($a) => $a->applied_at->format('Y-m'))
            ->map(fn($group) => ['label' => $group->first()->applied_at->format('M Y'), 'count' => $group->count()])
            ->values();

        // Department hiring
        $departmentHiring = JobPosting::with('department')
            ->withCount('applications')
            ->get()
            ->groupBy(fn($p) => $p->department?->name ?? 'Unassigned')
            ->map(fn($group) => ['label' => $group->first()->department?->name ?? 'Unassigned', 'count' => $group->sum('applications_count')])
            ->values();

        // Application status distribution
        $statusDistribution = Application::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        // Skills distribution
        $skills = Applicant::with('skills')->get()->flatMap(fn($a) => $a->skills->pluck('skill'));
        $skillsDistribution = $skills->countBy()->sortDesc()->take(10);

        // AI recommendation distribution
        $aiDistribution = AiRecommendation::selectRaw('recommendation, count(*) as total')
            ->groupBy('recommendation')
            ->pluck('total', 'recommendation');

        // Average scores
        $avgInterviewScore = Interview::where('status', 'completed')->with('assessment')->get()
            ->filter(fn($i) => $i->assessment)
            ->avg(fn($i) => $i->assessment->average_score);
        $avgMatchScore = AiRecommendation::avg('match_score');

        // Top recruitment sources
        $sources = JobPosting::select('source')->get()->groupBy('source')->map(fn($g) => ['label' => $g->first()->source, 'count' => $g->count()])->values();

        // Recent activities
        $recentApplications = Application::with('applicant')->latest()->limit(5)->get();
        $upcomingInterviews = Interview::with('application.applicant')->where('status', 'scheduled')->where('scheduled_at', '>=', now())->orderBy('scheduled_at')->limit(5)->get();
        $recentOffers = OfferLetter::with('application.applicant')->latest()->limit(5)->get();
        $recentAiRecommendations = AiRecommendation::with('application.applicant')->latest()->limit(5)->get();

        $avgInterviewScore = round($avgInterviewScore ?: 0, 1);
        $avgMatchScore = round($avgMatchScore ?: 0, 1);

        return view('analytics.index', compact(
            'kpis', 'funnel', 'applicantsPerPosition', 'monthlyHiring', 'departmentHiring',
            'statusDistribution', 'skillsDistribution', 'aiDistribution', 'avgInterviewScore',
            'avgMatchScore', 'sources', 'recentApplications', 'upcomingInterviews',
            'recentOffers', 'recentAiRecommendations'
        ));
    }
}
