<?php

namespace App\Services;

use App\Models\AiPipelineInsight;
use App\Models\AiRecommendation;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\Interview;
use App\Models\InterviewAssessment;
use App\Models\JobPosting;
use App\Models\OfferLetter;
use App\Models\Onboarding;
use Illuminate\Support\Facades\Log;

class AiInsightService
{
    /**
     * Generate evidence-based, categorized, priority-ranked aggregate recruitment
     * insights for the decision-support dashboard. Makes a SINGLE API call.
     */
    public function generate(): array
    {
        $provider = app(AiProviderClient::class);
        $data = $this->collectAggregateData();

if ($provider->isConfigured()) {
            try {
                $insights = $this->analyzeWithAi($provider, $data);
                if (!empty($insights)) {
                    $this->storeInsights($insights, $data);
                    return $insights;
                }
            } catch (\Throwable $e) {
                Log::warning('Pipeline insight AI generation failed, using fallback', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        $fallback = $this->generateFallbackInsights($data);
        $this->storeInsights($fallback, $data);
        return $fallback;
    }

    /**
     * Collect aggregated recruitment data (no personal applicant data).
     */
    protected function collectAggregateData(): array
    {
        $postings = JobPosting::with(['applications', 'department', 'jobPosition'])->get();
        $assessments = Interview::where('status', 'completed')->with('assessment')->get()
            ->filter(fn($i) => $i->assessment)
            ->map(fn($i) => $i->assessment);

        return [
            'totals' => [
                'total_applicants' => Applicant::count(),
                'total_applications' => Application::count(),
                'active_vacancies' => JobPosting::where('status', 'published')->count(),
                'total_vacancies' => (int) $postings->sum('vacancies_count'),
                'hired' => Application::where('status', 'hired')->count(),
                'rejected' => Application::where('status', 'rejected')->count(),
                'shortlisted' => Application::whereIn('status', ['shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'])->count(),
                'for_interview' => Application::where('status', 'for_interview')->count(),
                'interviewed' => Application::where('status', 'interviewed')->count(),
                'assessed' => Application::where('status', 'assessed')->count(),
                'recommended' => Application::where('status', 'recommended')->count(),
            ],
            'funnel' => $this->funnel(),
            'per_position_stats' => $postings->map(fn($p) => [
                'title' => $p->title,
                'department' => $p->department?->name ?? 'Unassigned',
                'position' => $p->jobPosition?->title ?? $p->title,
                'applications' => $p->applications->count(),
                'vacancies' => (int) $p->vacancies_count,
                'status' => $p->status,
                'source' => $p->source,
                'days_since_posting' => $p->posted_date ? now()->diffInDays($p->posted_date) : null,
                'estimated_cost' => (float) $p->estimated_cost,
                'required_skills' => $p->required_skills ?? [],
                'preferred_skills' => $p->preferred_skills ?? [],
            ])->toArray(),
            'skills' => [
                'skills_gap' => $this->skillsGap(),
                'most_required_skills' => $this->mostRequiredSkills(),
                'preferred_skills_demand' => $this->preferredSkillsDemand(),
                'applicant_skills_distribution' => $this->applicantSkillsDistribution(),
            ],
            'interviews' => [
                'performance' => [
                    'communication' => round($assessments->avg('communication_score') ?? 0, 1),
                    'technical' => round($assessments->avg('technical_score') ?? 0, 1),
                    'experience' => round($assessments->avg('experience_score') ?? 0, 1),
                    'cultural_fit' => round($assessments->avg('cultural_fit_score') ?? 0, 1),
                    'overall' => round($assessments->avg('average_score') ?? 0, 1),
                    'count' => $assessments->count(),
                ],
                'scheduled' => Interview::where('status', 'scheduled')->count(),
                'completed' => Interview::where('status', 'completed')->count(),
                'completion_rate' => $this->interviewCompletionRate(),
            ],
            'ai_distribution' => AiRecommendation::selectRaw('recommendation, count(*) as total')
                ->groupBy('recommendation')->pluck('total', 'recommendation')->toArray(),
            'sourcing' => $this->sourcingAnalysis(),
            'offers' => [
                'total' => OfferLetter::count(),
                'accepted' => OfferLetter::where('status', 'accepted')->count(),
                'rejected' => OfferLetter::where('status', 'rejected')->count(),
                'pending' => OfferLetter::where('status', 'pending')->count(),
                'acceptance_rate' => $this->offerAcceptanceRate(),
            ],
            'cost' => [
                'estimated_total' => (float) JobPosting::sum('estimated_cost'),
                'cost_per_hire' => $this->costPerHire(),
            ],
            'time_to_hire_days' => $this->timeToHire(),
            'onboarding' => $this->onboardingAnalysis(),
            'trend' => $this->trendAnalysis(),
            'kpis' => $this->kpis(),
        ];
    }

    protected function funnel(): array
    {
        $stages = ['submitted', 'under_review', 'screening', 'shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'];
        $funnel = [];
        foreach ($stages as $stage) {
            $funnel[$stage] = Application::where('status', $stage)->count();
        }
        return $funnel;
    }

    protected function skillsGap(): array
    {
        $postings = JobPosting::all();
        $applicantSkills = Applicant::with('skills')->get()->flatMap(fn($a) => $a->skills->pluck('skill'))
            ->map(fn($s) => strtolower(trim($s)))->unique()->values()->all();

        $gap = [];
        foreach ($postings as $posting) {
            foreach (($posting->required_skills ?? []) as $skill) {
                $key = strtolower(trim($skill));
                if (!in_array($key, $applicantSkills)) {
                    $gap[$key] = ($gap[$key] ?? 0) + 1;
                }
            }
        }
        arsort($gap);
        return array_slice($gap, 0, 10, true);
    }

    protected function mostRequiredSkills(): array
    {
        $agg = [];
        foreach (JobPosting::all() as $posting) {
            foreach (($posting->required_skills ?? []) as $skill) {
                $key = strtolower(trim($skill));
                $agg[$key] = ($agg[$key] ?? 0) + 1;
            }
        }
        arsort($agg);
        return array_slice($agg, 0, 10, true);
    }

    protected function preferredSkillsDemand(): array
    {
        $agg = [];
        foreach (JobPosting::all() as $posting) {
            foreach (($posting->preferred_skills ?? []) as $skill) {
                $key = strtolower(trim($skill));
                $agg[$key] = ($agg[$key] ?? 0) + 1;
            }
        }
        arsort($agg);
        return array_slice($agg, 0, 10, true);
    }

    protected function applicantSkillsDistribution(): array
    {
        return Applicant::with('skills')->get()->flatMap(fn($a) => $a->skills->pluck('skill'))
            ->countBy()->sortDesc()->take(10)->toArray();
    }

    protected function interviewCompletionRate(): int
    {
        $scheduled = Interview::count();
        $completed = Interview::where('status', 'completed')->count();
        return $scheduled > 0 ? round(($completed / $scheduled) * 100) : 0;
    }

    protected function sourcingAnalysis(): array
    {
        $sources = JobPosting::select('source')->distinct()->pluck('source')->filter();
        $result = [];
        foreach ($sources as $source) {
            $ids = JobPosting::where('source', $source)->pluck('id');
            $apps = Application::whereIn('job_posting_id', $ids)->get();
            $result[$source] = [
                'applications' => $apps->count(),
                'shortlisted' => $apps->whereIn('status', ['shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'])->count(),
                'interviewed' => $apps->whereIn('status', ['interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'])->count(),
                'hired' => $apps->where('status', 'hired')->count(),
            ];
        }
        return $result;
    }

    protected function offerAcceptanceRate(): int
    {
        $offers = OfferLetter::count();
        return $offers > 0 ? round((OfferLetter::where('status', 'accepted')->count() / $offers) * 100) : 0;
    }

    protected function costPerHire(): int
    {
        $hired = Application::where('status', 'hired')->count();
        if ($hired === 0) return -1; // not calculable
        $cost = JobPosting::sum('estimated_cost');
        return round($cost / $hired);
    }

    protected function timeToHire(): int
    {
        $hired = Application::where('status', 'hired')->whereNotNull('reviewed_at')->get();
        return $hired->count() > 0 ? round($hired->avg(fn($a) => $a->applied_at->diffInDays($a->reviewed_at))) : -1;
    }

    protected function onboardingAnalysis(): array
    {
        $onboardings = Onboarding::all();
        $completed = $onboardings->where('status', 'completed')->count();
        return [
            'total' => $onboardings->count(),
            'in_progress' => $onboardings->where('status', 'in_progress')->count(),
            'pending' => $onboardings->where('status', 'pending')->count(),
            'completed' => $completed,
            'completion_rate' => $onboardings->count() > 0 ? round(($completed / $onboardings->count()) * 100) : 0,
        ];
    }

    protected function trendAnalysis(): array
    {
        $currentMonth = Application::where('applied_at', '>=', now()->startOfMonth())->count();
        $lastMonth = Application::where('applied_at', '>=', now()->subMonth()->startOfMonth())
            ->where('applied_at', '<', now()->startOfMonth())->count();
        $rejected = Application::where('status', 'rejected')->count();
        $total = max(Application::count(), 1);

        return [
            'applications_this_month' => $currentMonth,
            'applications_last_month' => $lastMonth,
            'application_change_pct' => $lastMonth > 0 ? round((($currentMonth - $lastMonth) / $lastMonth) * 100) : null,
            'rejection_rate' => round(($rejected / $total) * 100),
            'has_history' => $lastMonth > 0 || $currentMonth > 0,
        ];
    }

    protected function kpis(): array
    {
        $total = max(Application::count(), 1);
        $shortlisted = Application::whereIn('status', ['shortlisted', 'for_interview', 'interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'])->count();
        $interviewed = Application::whereIn('status', ['interviewed', 'assessed', 'recommended', 'offer_sent', 'hired'])->count();
        $hired = Application::where('status', 'hired')->count();
        $assessmentsCount = InterviewAssessment::count();
        $assessmentsDone = Interview::where('status', 'completed')->has('assessment')->count();

        return [
            'application_conversion_rate' => round(($shortlisted / $total) * 100),
            'screening_to_shortlist_rate' => round(($shortlisted / $total) * 100),
            'interview_rate' => round(($interviewed / $total) * 100),
            'assessment_completion_rate' => $assessmentsCount > 0 ? 100 : 0,
            'hiring_rate' => round(($hired / $total) * 100),
            'vacancy_fill_rate' => $this->vacancyFillRate(),
        ];
    }

    protected function vacancyFillRate(): int
    {
        $postings = JobPosting::all();
        $totalVacancies = (int) $postings->sum('vacancies_count');
        if ($totalVacancies === 0) return 0;
        $hired = Application::where('status', 'hired')->count();
        return round(min(100, ($hired / $totalVacancies) * 100));
    }

    protected function analyzeWithAi(AiProviderClient $provider, array $data): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->systemPrompt()],
            ['role' => 'user', 'content' => $this->buildPrompt($data)],
        ];

        $response = $provider->chat($messages);
        if (!$response) return [];

        return $this->parseAiResponse($response);
    }

    protected function systemPrompt(): string
    {
        return 'You are an expert AI recruitment and onboarding decision-support analyst. '
            . 'You analyze AGGREGATE recruitment pipeline data and produce evidence-based, actionable insights for HR management. '
            . 'You NEVER make final hiring decisions, evaluate individual applicants, or rank candidates in these insights. '
            . 'You ONLY use the database data provided - never invent metrics, applicants, postings, or trends. '
            . 'Each insight must be tied to concrete evidence from the data. '
            . 'Return ONLY valid JSON in the exact schema requested. '
            . 'Categorize each insight, assign a priority (HIGH/MEDIUM/LOW) based on measurable impact, and give a clear recommended action.';
    }

    protected function buildPrompt(array $data): string
    {
        $prompt = 'Analyze the following aggregated recruitment and onboarding data and produce 3 to 6 evidence-based insights for HR. ';
        $prompt .= "Only generate categories where the data provides meaningful evidence. Do not invent data.\n\n";
        $prompt .= 'Return a JSON object with exactly this shape: ';
        $prompt .= '{"insights":[{"category":"<one of Recruitment Pipeline|Skills Gap|Job Posting Performance|Candidate Sourcing|Screening Efficiency|Interview Performance|Hiring Performance|Recruitment Cost|Onboarding Readiness|Strategic Recommendation>",';
        $prompt .= '"title":"<short actionable title>","priority":"HIGH|MEDIUM|LOW","icon":"<emoji>",';
        $prompt .= '"summary":"<2-3 sentence explanation of what is happening and why, grounded in evidence>",';
        $prompt .= '"evidence":["<metric/fact>","<metric/fact>"],';
        $prompt .= '"impact":"<why it matters>","recommendation":"<what HR should do next>",';
$prompt .= '"explanation":"<why this insight was generated, tied to the data>"}]}';
        $prompt .= "\n\nDATASET (aggregate recruitment data):\n" . json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        return $prompt;
    }

    protected function parseAiResponse(string $response): array
    {
        $cleaned = trim($response);
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $cleaned, $m)) {
            $cleaned = trim($m[1]);
        }
        $data = json_decode($cleaned, true);
        if (!is_array($data) && preg_match('/\{.*\}/s', $cleaned, $m)) {
            $data = json_decode($m[0], true);
        }
        if (!is_array($data) || !isset($data['insights']) || !is_array($data['insights'])) {
            Log::warning('AI returned unparseable pipeline insights', ['response' => substr($response, 0, 500)]);
            return [];
        }

        $insights = [];
        foreach ($data['insights'] as $i => $insight) {
            if (!is_array($insight)) continue;
            $title = trim((string)($insight['title'] ?? ('Insight ' . ($i + 1))));
            $summary = trim((string)($insight['summary'] ?? ''));
            if ($title === '' || $summary === '') continue;

            $priority = strtoupper(trim((string)($insight['priority'] ?? 'MEDIUM')));
            if (!in_array($priority, ['HIGH', 'MEDIUM', 'LOW'])) $priority = 'MEDIUM';

            $evidence = $insight['evidence'] ?? [];
            if (!is_array($evidence)) $evidence = [];

            $insights[] = [
                'title' => $title,
                'category' => trim((string)($insight['category'] ?? 'Strategic Recommendation')),
                'priority' => $priority,
                'icon' => trim((string)($insight['icon'] ?? '📊')),
                'summary' => $summary,
                'evidence' => $evidence,
                'impact' => trim((string)($insight['impact'] ?? '')),
                'recommendation' => trim((string)($insight['recommendation'] ?? '')),
                'explanation' => trim((string)($insight['explanation'] ?? '')),
                'content' => $summary,
                'sort_order' => $i,
            ];
        }

        return $insights;
    }

    protected function storeInsights(array $insights, array $data = []): void
    {
        $signature = $this->computeSignature($data);

        AiPipelineInsight::query()->delete();
        foreach ($insights as $insight) {
            AiPipelineInsight::create(array_merge($insight, [
                'data_signature' => $signature,
                'generated_at' => now(),
            ]));
        }
    }

    /**
     * Compute a signature of the current aggregate data so we can detect
     * whether stored insights are stale (data has changed).
     */
    public function computeSignature(array $data = []): string
    {
        if (empty($data)) {
            $data = $this->collectAggregateData();
        }

        return md5(json_encode($data));
    }

    /**
     * Determine if stored insights are stale relative to the current data.
     */
    public function insightsAreStale(): bool
    {
        $latest = AiPipelineInsight::latest('generated_at')->first();
        if (!$latest) return true;

        $current = $this->computeSignature();
        return $latest->data_signature !== $current;
    }

    /**
     * Public entry point for the dashboard auto-refresh.
     * Returns true if a regeneration occurred.
     */
    public function regenerateIfStale(): bool
    {
        if (!$this->insightsAreStale()) {
            return false;
        }

        $this->generate();
        return true;
    }

    protected function generateFallbackInsights(array $data): array
    {
        $insights = [];
        $funnel = $data['funnel'] ?? [];
        $totals = $data['totals'] ?? [];

        // Handle empty datasets
        if (($totals['total_applications'] ?? 0) === 0) {
            return [[
                'title' => 'Insufficient Data',
                'category' => 'Strategic Recommendation',
                'priority' => 'LOW',
                'icon' => '📊',
                'summary' => 'More recruitment data is needed to generate reliable AI insights.',
                'evidence' => ['No applications are currently recorded.'],
                'impact' => 'Reliable insights require at least one candidate application in the pipeline.',
                'recommendation' => 'Continue collecting applications and recruitment data, then regenerate insights.',
                'explanation' => 'The AI avoids making judgments without sufficient evidence.',
                'content' => 'More recruitment data is needed to generate reliable AI insights.',
                'sort_order' => 0,
            ]];
        }

        // High priority: postings with vacancies but zero applicants
        foreach (($data['per_position_stats'] ?? []) as $posting) {
            if (($posting['vacancies'] ?? 0) > 0 && ($posting['applications'] ?? 0) === 0) {
                $insights[] = [
                    'title' => $posting['title'] . ' Applicant Shortage',
                    'category' => 'Recruitment Pipeline',
                    'priority' => 'HIGH',
                    'icon' => '🔍',
                    'summary' => "The {$posting['title']} position has " . ($posting['vacancies'] ?? 0) . " open vacancy/vacancies but currently has zero applicants.",
                    'evidence' => [
                        "Vacancies: " . ($posting['vacancies'] ?? 0),
                        "Applicants: " . ($posting['applications'] ?? 0),
                        "Required skills: " . implode(', ', array_slice($posting['required_skills'] ?? [], 0, 3)),
                        "Source: " . ($posting['source'] ?? 'N/A'),
                    ],
                    'impact' => 'The vacancy may remain open longer and delay staffing requirements.',
                    'recommendation' => 'Review the job posting, expand recruitment sources, and consider targeted sourcing for the required skills.',
                    'explanation' => 'A published position with vacancies but no applicants indicates a sourcing or job-posting issue.',
                    'content' => "The {$posting['title']} position currently has no applicants despite having an active vacancy.",
                    'sort_order' => count($insights),
                ];
            }
        }

        // Skills gap
        $skillsGap = $data['skills']['skills_gap'] ?? [];
        if (!empty($skillsGap)) {
            $topGaps = array_slice(array_keys($skillsGap), 0, 3);
            $insights[] = [
                'title' => 'Critical Skills Gap',
                'category' => 'Skills Gap',
                'priority' => 'HIGH',
                'icon' => '🎯',
                'summary' => implode(', ', array_map('ucfirst', $topGaps)) . ' are required by job postings but no current applicant demonstrates these skills.',
                'evidence' => array_map(fn($s) => ucfirst($s) . ' required by ' . $skillsGap[$s] . ' posting(s)', array_slice(array_keys($skillsGap), 0, 5)),
                'impact' => 'Roles requiring these skills may be difficult to fill, extending time-to-hire.',
                'recommendation' => 'Invest in targeted sourcing, employer branding, and training initiatives for the missing skills.',
                'explanation' => 'The AI compared required skills across all postings against the aggregate applicant skill pool and found these skills entirely absent.',
                'content' => implode(', ', array_map('ucfirst', $topGaps)) . ' represent a recruitment skill gap.',
                'sort_order' => count($insights),
            ];
        }

        // Interview bottleneck
        $scheduled = $data['interviews']['scheduled'] ?? 0;
        $completed = $data['interviews']['completed'] ?? 0;
        $completionRate = $data['interviews']['completion_rate'] ?? 0;
        if ($scheduled > 0 && $completionRate < 70) {
            $insights[] = [
                'title' => 'Interview Completion Bottleneck',
                'category' => 'Interview Performance',
                'priority' => 'HIGH',
                'icon' => '🗓',
                'summary' => "Only {$completionRate}% of scheduled interviews have been completed, which may indicate scheduling or assessment bottlenecks.",
                'evidence' => [
                    "Scheduled interviews: {$scheduled}",
                    "Completed interviews: {$completed}",
                    "Completion rate: {$completionRate}%",
                ],
                'impact' => 'Delays in interview completion slow down the recruitment pipeline.',
                'recommendation' => 'Review interview scheduling practices and follow up on pending interviews to clear the bottleneck.',
                'explanation' => 'A low completion rate relative to scheduled interviews suggests stalled candidates in the interview stage.',
                'content' => "Several applications have reached the interview stage, but completed assessments are limited.",
                'sort_order' => count($insights),
            ];
        }

        // Screening bottleneck
        $screening = $funnel['screening'] ?? 0;
        if ($screening > 5 && (($funnel['shortlisted'] ?? 0) + ($funnel['for_interview'] ?? 0)) < 2) {
            $insights[] = [
                'title' => 'Screening Bottleneck',
                'category' => 'Screening Efficiency',
                'priority' => 'MEDIUM',
                'icon' => '🔎',
                'summary' => "A large number of applications ({$screening}) are stuck in the screening stage with few progressing to shortlisting.",
                'evidence' => ["Applications in screening: {$screening}"],
                'impact' => 'Slow screening delays the entire recruitment funnel.',
                'recommendation' => 'Re-evaluate screening criteria and processing capacity to move qualified candidates forward.',
                'explanation' => 'A high count of applications remaining in screening suggests a processing bottleneck.',
                'content' => "A large number of applications are stuck in screening.",
                'sort_order' => count($insights),
            ];
        }

        // Offer acceptance
        $acceptanceRate = $data['offers']['acceptance_rate'] ?? 0;
        $offerCount = $data['offers']['total'] ?? 0;
        if ($offerCount > 0 && $acceptanceRate < 50) {
            $insights[] = [
                'title' => 'Low Offer Acceptance',
                'category' => 'Hiring Performance',
                'priority' => 'HIGH',
                'icon' => '💼',
                'summary' => "Only {$acceptanceRate}% of offers have been accepted, which is below a healthy benchmark.",
                'evidence' => ["Offers sent: {$offerCount}", "Acceptance rate: {$acceptanceRate}%"],
                'impact' => 'Low acceptance rates may indicate issues with offer competitiveness or candidate expectations.',
                'recommendation' => 'Review offer packages, salary benchmarks, and candidate communication to improve acceptance.',
                'explanation' => 'A low acceptance percentage relative to offers sent indicates a hiring performance concern.',
                'content' => "Offer acceptance rate is low at {$acceptanceRate}%.",
                'sort_order' => count($insights),
            ];
        }

        // Cost per hire
        $costPerHire = $data['cost']['cost_per_hire'] ?? -1;
        if ($costPerHire < 0) {
            $insights[] = [
                'title' => 'Cost-per-Hire Not Calculable',
                'category' => 'Recruitment Cost',
                'priority' => 'LOW',
                'icon' => '💲',
                'summary' => 'Cost-per-Hire cannot currently be calculated because no completed hires are recorded.',
                'evidence' => ['No hired applicants recorded.'],
                'impact' => 'Recruitment cost efficiency cannot be assessed until at least one hire is completed.',
                'recommendation' => 'Track recruitment costs and regenerate insights once hires are recorded.',
                'explanation' => 'Calculating cost-per-hire requires at least one completed hire for a valid division.',
                'content' => 'Cost-per-Hire cannot currently be calculated because no completed hires are recorded.',
                'sort_order' => count($insights),
            ];
        } elseif (count($insights) < 3) {
            $insights[] = [
                'title' => 'Recruitment Health',
                'category' => 'Strategic Recommendation',
                'priority' => 'MEDIUM',
                'icon' => '📊',
                'summary' => "The pipeline currently has {$totals['total_applications']} applications and {$totals['active_vacancies']} active vacancies, with {$costPerHire} as the estimated cost per hire.",
                'evidence' => [
                    "Total applications: {$totals['total_applications']}",
                    "Active vacancies: {$totals['active_vacancies']}",
                    "Cost per hire: {$costPerHire}",
                ],
                'impact' => 'Provides a baseline for assessing recruitment efficiency.',
                'recommendation' => 'Continue monitoring funnel progression and sourcing effectiveness.',
                'explanation' => 'This insight summarizes the overall recruitment pipeline health from aggregate counts.',
                'content' => "The pipeline has {$totals['total_applications']} applications across {$totals['active_vacancies']} active vacancies.",
                'sort_order' => count($insights),
            ];
        }

        return array_slice($insights, 0, 6);
    }
}
