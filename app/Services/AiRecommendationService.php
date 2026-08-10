<?php

namespace App\Services;

use App\Models\AiRecommendation;
use App\Models\Application;
use App\Models\JobPosting;
use Illuminate\Support\Facades\Log;

class AiRecommendationService
{
    /**
     * Generate an AI-assisted recommendation for an application.
     * The AI NEVER makes the final hiring decision - it only assists HR.
     */
    public function generate(Application $application): AiRecommendation
    {
        $provider = app(AiProviderClient::class);

        // Step 1: Data Collection - gather applicant & job data
        $posting = $application->jobPosting;
        $applicant = $application->applicant;

        $applicantData = $this->collectApplicantData($application);
        $jobData = $this->collectJobData($posting);
        $interviewData = $this->collectInterviewData($application);

        // Step 2-6: Use AI for qualification analysis when configured
        if ($provider->isConfigured()) {
            try {
                $aiResult = $this->analyzeWithAi($provider, $applicantData, $jobData, $interviewData);

                if ($aiResult !== null) {
                    return $this->storeAiRecommendation($application, $posting, $aiResult);
                }
            } catch (\Throwable $e) {
                Log::warning('AI analysis failed, falling back to rule-based engine', [
                    'message' => $e->getMessage(),
                ]);
            }
        }

        // Fallback: rule-based engine
        return $this->generateRuleBased($application);
    }

    /**
     * Generate recommendations for all applications of a posting.
     */
    public function generateForPosting(JobPosting $posting): void
    {
        foreach ($posting->applications as $application) {
            $this->generate($application);
        }

        // Rank candidates
        $recommendations = AiRecommendation::where('job_posting_id', $posting->id)
            ->orderByDesc('match_score')
            ->get();

        $rank = 1;
        foreach ($recommendations as $rec) {
            $rec->update(['rank' => $rank++]);
        }
    }

    /**
     * Step 1 - Collect applicant profile data.
     */
    protected function collectApplicantData(Application $application): array
    {
        $applicant = $application->applicant;

        return [
            'name' => $applicant->full_name,
            'summary' => $applicant->summary,
            'skills' => $applicant->skills->map(fn($s) => [
                'skill' => $s->skill,
                'proficiency' => $s->proficiency,
                'years' => $s->years_of_experience,
            ])->toArray(),
            'education' => $applicant->education->map(fn($e) => [
                'degree' => $e->degree,
                'field' => $e->field_of_study,
                'institution' => $e->institution,
                'gpa' => $e->gpa,
                'honors' => $e->honors,
            ])->toArray(),
            'experiences' => $applicant->experiences->map(fn($x) => [
                'job_title' => $x->job_title,
                'company' => $x->company,
                'years' => $x->start_date ? round($x->start_date->diffInYears($x->end_date ?: now()), 1) : null,
                'description' => $x->description,
            ])->toArray(),
            'certifications' => $applicant->certifications->map(fn($c) => $c->name)->toArray(),
            'total_years_experience' => $applicant->total_years_experience,
        ];
    }

    /**
     * Step 1 - Collect job posting data.
     */
    protected function collectJobData(JobPosting $posting): array
    {
        return [
            'title' => $posting->title,
            'position' => $posting->jobPosition?->title,
            'department' => $posting->department?->name,
            'employment_type' => $posting->employment_type,
            'summary' => $posting->summary,
            'description' => $posting->description,
            'required_skills' => $posting->required_skills ?? [],
            'preferred_skills' => $posting->preferred_skills ?? [],
            'requirements' => $posting->requirements,
            'qualifications' => $posting->qualifications,
        ];
    }

    /**
     * Step 1 - Collect interview assessment data.
     */
    protected function collectInterviewData(Application $application): array
    {
        $assessments = [];

        foreach ($application->interviews as $interview) {
            if ($interview->assessment) {
                $a = $interview->assessment;
                $assessments[] = [
                    'type' => $interview->type,
                    'communication_score' => $a->communication_score,
                    'technical_score' => $a->technical_score,
                    'experience_score' => $a->experience_score,
                    'cultural_fit_score' => $a->cultural_fit_score,
                    'overall_score' => $a->overall_score,
                    'average_score' => $a->average_score,
                    'comments' => $a->comments,
                ];
            }
        }

        return $assessments;
    }

    /**
     * Step 2-6 - Query the AI provider for qualification analysis.
     */
    protected function analyzeWithAi(AiProviderClient $provider, array $applicantData, array $jobData, array $interviewData): ?array
    {
        $prompt = $this->buildPrompt($applicantData, $jobData, $interviewData);

        $messages = [
            [
                'role' => 'system',
                'content' => 'You are an expert AI recruitment decision-support assistant. '
                    . 'You analyze job applicants against job requirements and produce a structured JSON analysis. '
                    . 'You NEVER make the final hiring decision; you only assist HR. '
                    . 'Respond ONLY with valid JSON matching the requested schema.',
            ],
            ['role' => 'user', 'content' => $prompt],
        ];

        $response = $provider->chat($messages);

        if (!$response) {
            return null;
        }

        return $this->parseAiResponse($response);
    }

    /**
     * Build the prompt for the AI provider.
     */
    protected function buildPrompt(array $applicantData, array $jobData, array $interviewData): string
    {
        $payload = [
            'job_posting' => $jobData,
            'applicant' => $applicantData,
            'interview_assessments' => $interviewData,
        ];

        return 'Analyze the following candidate against the job requirements. '
            . 'Compute match scores and provide a transparent, explainable recommendation. '
            . 'Return a JSON object with exactly these keys:\n'
            . '{\n'
            . '  "match_score": <int 0-100>,\n'
            . '  "skills_match_percentage": <int 0-100>,\n'
            . '  "experience_match_percentage": <int 0-100>,\n'
            . '  "education_match_percentage": <int 0-100>,\n'
            . '  "interview_score": <int 0-100>,\n'
            . '  "confidence_score": <number 0-100>,\n'
            . '  "recommendation": "<highly_recommended|recommended|moderately_recommended|not_recommended>",\n'
            . '  "strengths": ["..."],\n'
            . '  "weaknesses": ["..."],\n'
            . '  "missing_skills": ["..."],\n'
            . '  "qualification_gaps": ["..."],\n'
            . '  "explanation": "<multi-sentence natural-language explanation, referencing the candidate name>",\n'
            . '  "summary": "<one-sentence summary>",\n'
            . '  "score_breakdown": {\n'
            . '      "skills": <int>,\n'
            . '      "experience": <int>,\n'
            . '      "education": <int>,\n'
            . '      "interview": <int>\n'
            . '  }\n'
            . '}\n\n'
            . 'Data:\n' . json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Parse and validate the AI JSON response.
     */
    protected function parseAiResponse(string $response): ?array
    {
        // Strip code fences if present
        $cleaned = trim($response);
        if (preg_match('/```(?:json)?\s*(.*?)```/s', $cleaned, $m)) {
            $cleaned = trim($m[1]);
        }

        $data = json_decode($cleaned, true);

        if (!is_array($data)) {
            // Try to find the first JSON object
            if (preg_match('/\{.*\}/s', $cleaned, $m)) {
                $data = json_decode($m[0], true);
            }
        }

        if (!is_array($data)) {
            Log::warning('AI returned unparseable response', ['response' => substr($response, 0, 500)]);
            return null;
        }

        // Normalize & clamp values
        $data['match_score'] = $this->clamp((int)($data['match_score'] ?? 0), 0, 100);
        $data['skills_match_percentage'] = $this->clamp((int)($data['skills_match_percentage'] ?? 50), 0, 100);
        $data['confidence_score'] = $this->clamp((float)($data['confidence_score'] ?? 0), 0, 100);
        $data['experience_match_percentage'] = $this->clamp((int)($data['experience_match_percentage'] ?? 0), 0, 100);
        $data['education_match_percentage'] = $this->clamp((int)($data['education_match_percentage'] ?? 0), 0, 100);
        $data['interview_score'] = $this->clamp((int)($data['interview_score'] ?? 0), 0, 100);

        $data['strengths'] = (array)($data['strengths'] ?? []);
        $data['weaknesses'] = (array)($data['weaknesses'] ?? []);
        $data['missing_skills'] = (array)($data['missing_skills'] ?? []);
        $data['qualification_gaps'] = (array)($data['qualification_gaps'] ?? []);

        if (empty($data['recommendation']) || !in_array($data['recommendation'], ['highly_recommended', 'recommended', 'moderately_recommended', 'not_recommended'])) {
            $data['recommendation'] = $this->recommendationFor($data['match_score']);
        }

        // Ensure score_breakdown is an array
        $breakdown = $data['score_breakdown'] ?? [];
        $data['score_breakdown'] = [
            'skills' => $this->clamp((int)($breakdown['skills'] ?? $data['skills_match_percentage']), 0, 100),
            'experience' => $this->clamp((int)($breakdown['experience'] ?? $data['experience_match_percentage']), 0, 100),
            'education' => $this->clamp((int)($breakdown['education'] ?? $data['education_match_percentage']), 0, 100),
            'interview' => $this->clamp((int)($breakdown['interview'] ?? $data['interview_score']), 0, 100),
        ];

        return $data;
    }

    /**
     * Store an AI-generated recommendation.
     */
    protected function storeAiRecommendation(Application $application, JobPosting $posting, array $data): AiRecommendation
    {
        return AiRecommendation::updateOrCreate(
            ['application_id' => $application->id],
            [
                'job_posting_id' => $posting->id,
                'match_score' => $data['match_score'],
                'skills_match_percentage' => $data['skills_match_percentage'],
                'confidence_score' => $data['confidence_score'],
                'recommendation' => $data['recommendation'],
                'missing_skills' => $data['missing_skills'],
                'strengths' => $data['strengths'],
                'weaknesses' => $data['weaknesses'],
                'qualification_gaps' => $data['qualification_gaps'],
                'explanation' => $data['explanation'] ?? null,
                'summary' => $data['summary'] ?? 'AI-assisted analysis based on skills, experience, education, and interview performance.',
                'score_breakdown' => $data['score_breakdown'],
                'status' => 'generated',
            ]
        );
    }

    /**
     * Fallback rule-based recommendation engine.
     */
    protected function generateRuleBased(Application $application): AiRecommendation
    {
        $posting = $application->jobPosting;
        $applicant = $application->applicant;

        $applicantSkills = $applicant->skills->pluck('skill')->map(fn($s) => strtolower($s))->toArray();
        $requiredSkills = is_array($posting->required_skills) ? $posting->required_skills : [];
        $requiredSkills = array_map('strtolower', $requiredSkills);
        $preferredSkills = is_array($posting->preferred_skills) ? $posting->preferred_skills : [];
        $preferredSkills = array_map('strtolower', $preferredSkills);

        $matchedRequired = array_intersect($requiredSkills, $applicantSkills);
        $missingRequired = array_values(array_diff($requiredSkills, $applicantSkills));
        $matchedPreferred = array_intersect($preferredSkills, $applicantSkills);

        $requiredCount = count($requiredSkills);
        $skillsMatch = $requiredCount > 0 ? round((count($matchedRequired) / $requiredCount) * 100) : 50;

        $experienceYears = $applicant->total_years_experience;
        $experienceScore = min(100, $experienceYears * 10);

        $educationScore = $applicant->education->count() > 0 ? 80 : 40;

        $interviewScore = 0;
        foreach ($application->interviews as $interview) {
            if ($interview->assessment) {
                $interviewScore = max($interviewScore, $interview->assessment->average_score);
            }
        }

        $matchScore = round(
            ($skillsMatch * 0.45) +
            ($experienceScore * 0.25) +
            ($educationScore * 0.15) +
            ($interviewScore > 0 ? $interviewScore * 0.15 : 50 * 0.15)
        );

        $matchScore = max(0, min(100, $matchScore));

        $recommendation = $this->recommendationFor($matchScore);

        $strengths = [];
        if (count($matchedRequired) > 0) {
            $strengths[] = 'Matches ' . count($matchedRequired) . ' required skills';
        }
        if ($experienceYears >= 3) {
            $strengths[] = $experienceYears . ' years of relevant experience';
        }
        if ($applicant->certifications->count() > 0) {
            $strengths[] = $applicant->certifications->count() . ' relevant certifications';
        }
        if ($interviewScore >= 80) {
            $strengths[] = 'Excellent interview performance (' . round($interviewScore) . '%)';
        }
        if (empty($strengths)) {
            $strengths[] = 'Candidate has basic qualifications';
        }

        $weaknesses = [];
        foreach ($missingRequired as $missing) {
            $weaknesses[] = 'Missing required skill: ' . ucfirst($missing);
        }
        if ($experienceYears < 2) {
            $weaknesses[] = 'Limited work experience (' . $experienceYears . ' years)';
        }
        if ($interviewScore > 0 && $interviewScore < 60) {
            $weaknesses[] = 'Below-average interview score';
        }
        if (empty($weaknesses)) {
            $weaknesses[] = 'No major weaknesses identified';
        }

        $explanation = "Candidate match score is {$matchScore}%. ";
        $explanation .= "Skills match requirement at {$skillsMatch}%. ";
        if ($interviewScore > 0) {
            $explanation .= "Interview score: " . round($interviewScore) . "%. ";
        }
        $explanation .= count($missingRequired) > 0
            ? "Missing skills: " . implode(', ', array_map('ucfirst', $missingRequired)) . "."
            : "All required skills are present.";

        $scoreBreakdown = [
            'skills' => $skillsMatch,
            'experience' => $experienceScore,
            'education' => $educationScore,
            'interview' => $interviewScore > 0 ? round($interviewScore) : 50,
        ];

        $confidenceScore = round(($matchScore * 0.7) + (min(100, $skillsMatch) * 0.3), 2);

        return AiRecommendation::updateOrCreate(
            ['application_id' => $application->id],
            [
                'job_posting_id' => $posting->id,
                'match_score' => $matchScore,
                'skills_match_percentage' => $skillsMatch,
                'confidence_score' => $confidenceScore,
                'recommendation' => $recommendation,
                'missing_skills' => array_map('ucfirst', $missingRequired),
                'strengths' => $strengths,
                'weaknesses' => $weaknesses,
                'qualification_gaps' => $missingRequired,
                'explanation' => $explanation,
                'summary' => 'AI-assisted analysis based on skills, experience, education, and interview performance.',
                'score_breakdown' => $scoreBreakdown,
                'status' => 'generated',
            ]
        );
    }

    /**
     * Map a match score to a recommendation level.
     */
    protected function recommendationFor(int $score): string
    {
        return $score >= 85 ? 'highly_recommended'
            : ($score >= 70 ? 'recommended'
            : ($score >= 50 ? 'moderately_recommended' : 'not_recommended'));
    }

    /**
     * Clamp a value between a min and max.
     */
    protected function clamp($value, $min, $max)
    {
        return max($min, min($max, $value));
    }
}
