<?php

namespace App\Http\Controllers;

use App\Models\AiRecommendation;
use App\Models\Application;
use App\Models\JobPosting;
use App\Services\ActivityLogService;
use App\Services\AiInsightService;
use App\Services\AiRecommendationService;
use Illuminate\Http\Request;

class AiRecommendationController extends Controller
{
    public function index(Request $request)
    {
        $query = AiRecommendation::with('application.applicant', 'jobPosting')
            ->when($request->recommendation, fn($q, $r) => $q->where('recommendation', $r))
            ->when($request->job_posting_id, fn($q, $j) => $q->where('job_posting_id', $j))
            ->orderByDesc('match_score');

        $recommendations = $query->paginate(15);
        $postings = JobPosting::all();

        return view('recruitment.ai.recommendations', compact('recommendations', 'postings'));
    }

    public function generate(Request $request, Application $application)
    {
        $rec = app(AiRecommendationService::class)->generate($application);

        app(ActivityLogService::class)->log(
            'ai_generate', 'AI Dashboard',
            "AI recommendation generated for application #{$application->id} (match score: {$rec->match_score}%).",
            'AiRecommendation', $rec->id
        );

        return back()->with('success', 'AI recommendation generated successfully.');
    }

public function generateForPosting(JobPosting $posting)
    {
        app(AiRecommendationService::class)->generateForPosting($posting);

        app(ActivityLogService::class)->log(
            'ai_generate', 'AI Dashboard',
            "AI recommendations generated for all applications of '{$posting->title}'.",
            'JobPosting', $posting->id
        );

        return back()->with('success', 'AI recommendations generated for all candidates.');
    }

public function generateAll()
    {
        // Generate aggregate pipeline insights with a SINGLE fast AI call.
        $insights = app(AiInsightService::class)->generate();

        app(ActivityLogService::class)->log(
            'ai_generate', 'AI Dashboard',
            'AI recruitment pipeline insights generated (' . count($insights) . ' insights).',
            'AiPipelineInsight', count($insights)
        );

        // Flag the session so the redirect back to the dashboard keeps the newly
        // generated insights visible for that request. A subsequent plain refresh
        // will clear them again (manual-only generation).
        session(['show_ai_insights' => true]);

        return back()->with('success', 'AI recruitment pipeline insights generated successfully.');
    }

    public function show(AiRecommendation $recommendation)
    {
        $recommendation->load(['application.applicant.skills', 'application.applicant.education', 'application.applicant.experiences', 'application.applicant.certifications', 'jobPosting']);
        return view('recruitment.ai.show', compact('recommendation'));
    }
}
