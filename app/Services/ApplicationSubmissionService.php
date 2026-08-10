<?php

namespace App\Services;

use App\Mail\ApplicationSubmittedApplicant;
use App\Mail\ApplicationSubmittedRecruiter;
use App\Models\Applicant;
use App\Models\Application;
use App\Models\JobPosting;
use App\Models\NotificationRecord;
use App\Models\UploadedDocument;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class ApplicationSubmissionService
{
    protected AiRecommendationService $aiService;
    protected ActivityLogService $activityLogService;

    public function __construct(AiRecommendationService $aiService, ActivityLogService $activityLogService)
    {
        $this->aiService = $aiService;
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle complete job application submission workflow.
     */
    public function submit(Request $request, JobPosting $posting, Applicant $applicant): Application
    {
        // 1. Guard check: Posting must be published
        if ($posting->status !== 'published') {
            throw new \Exception('This job posting is no longer accepting applications.');
        }

        if ($posting->closing_date && \Carbon\Carbon::parse($posting->closing_date)->isPast()) {
            throw new \Exception('The deadline for this job posting has passed.');
        }

        // 2. Prevent duplicate application
        $existing = Application::where('applicant_id', $applicant->id)
            ->where('job_posting_id', $posting->id)
            ->first();

        if ($existing) {
            throw new \Exception('You have already applied for this position (Ref: ' . $existing->reference_code . ').');
        }

        return DB::transaction(function () use ($request, $posting, $applicant) {
            // 3. Handle position-specific custom resume upload
            $customResumePath = null;
            if ($request->hasFile('custom_resume')) {
                $file = $request->file('custom_resume');
                $customResumePath = $file->store('resumes/' . $applicant->id . '/applications', 'public');
            }

            // 4. Process Dynamic Screening Answers & Knockout Criteria
            $screeningAnswers = $request->input('screening_answers', []);
            $isKnockedOut = false;
            $knockoutReason = null;

            if (!empty($posting->screening_questions) && is_array($posting->screening_questions)) {
                foreach ($posting->screening_questions as $q) {
                    $qId = $q['id'] ?? null;
                    if (!$qId) continue;

                    $ans = $screeningAnswers[$qId] ?? null;
                    $qText = $q['question'] ?? 'Question';

                    // Check knockout value for Yes/No or Select
                    if (!empty($q['knockout_value']) && $ans !== null && $ans !== '') {
                        if (strtolower((string)$ans) === strtolower((string)$q['knockout_value'])) {
                            $isKnockedOut = true;
                            $knockoutReason = "Failed screening criteria for: \"{$qText}\" (Answered: \"{$ans}\")";
                            break;
                        }
                    }

                    // Check min numeric value requirement
                    if (isset($q['min_value']) && is_numeric($q['min_value']) && $ans !== null && $ans !== '') {
                        if ((float)$ans < (float)$q['min_value']) {
                            $isKnockedOut = true;
                            $knockoutReason = "Failed minimum criteria for: \"{$qText}\" (Required min: {$q['min_value']}, Answered: {$ans})";
                            break;
                        }
                    }
                }
            }

            // 5. Create Application record
            $application = Application::create([
                'applicant_id' => $applicant->id,
                'job_posting_id' => $posting->id,
                'status' => 'submitted',
                'applied_at' => now(),
                'cover_letter' => $request->input('cover_letter'),
                'custom_notes' => $request->input('custom_notes'),
                'custom_resume_path' => $customResumePath,
                'screening_answers' => $screeningAnswers,
                'is_knocked_out' => $isKnockedOut,
                'knockout_reason' => $knockoutReason,
            ]);

            // If a custom resume was uploaded, create an UploadedDocument record linked to this application
            if ($customResumePath && isset($file)) {
                UploadedDocument::create([
                    'applicant_id' => $applicant->id,
                    'application_id' => $application->id,
                    'document_type' => 'resume',
                    'document_name' => 'Custom Application Resume - ' . $posting->title,
                    'file_path' => $customResumePath,
                    'file_type' => $file->getClientOriginalExtension(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                    'status' => 'verified',
                ]);
            }

            // 5. Trigger immediate AI Pre-Screening Match Score calculation
            try {
                $this->aiService->generate($application);
            } catch (\Throwable $e) {
                Log::warning('AI Match calculation on submission skipped/failed: ' . $e->getMessage());
            }

            // Reload application with AI recommendation
            $application->load('aiRecommendation', 'jobPosting', 'applicant');

            // 6. Send in-app notification to Applicant
            NotificationRecord::create([
                'user_id' => auth()->id(),
                'type' => 'application_submitted',
                'title' => 'Application Received (' . $application->reference_code . ')',
                'message' => "Your application for '{$posting->title}' has been received. Reference Code: {$application->reference_code}.",
                'icon' => 'fa-paper-plane',
                'action_url' => route('applicant.track'),
            ]);

            // 7. Send in-app notification to Recruiters / HR Staff
            $hrUserIds = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'HR Administrator', 'Recruitment Officer']);
            })->pluck('id');

            foreach ($hrUserIds as $hrUserId) {
                if ($hrUserId !== auth()->id()) {
                    NotificationRecord::create([
                        'user_id' => $hrUserId,
                        'type' => 'new_application',
                        'title' => 'New Application: ' . $applicant->full_name,
                        'message' => "{$applicant->full_name} applied for '{$posting->title}' (Ref: {$application->reference_code}).",
                        'icon' => 'fa-user-check',
                        'action_url' => route('recruitment.applications.show', $application),
                    ]);
                }
            }

            // 8. Dispatch Transactional Emails
            $this->sendEmails($application);

            // 9. Log activity
            $this->activityLogService->log(
                'apply',
                'Applicant',
                "{$applicant->full_name} applied for '{$posting->title}' (Ref: {$application->reference_code}).",
                'Application',
                $application->id
            );

            return $application;
        });
    }

    /**
     * Safely send transactional email notifications.
     */
    protected function sendEmails(Application $application): void
    {
        try {
            // Email applicant
            if ($application->applicant->email) {
                Mail::to($application->applicant->email)->send(new ApplicationSubmittedApplicant($application));
            }

            // Email recruiters
            $hrEmails = User::whereHas('roles', function ($q) {
                $q->whereIn('name', ['Super Admin', 'HR Administrator', 'Recruitment Officer']);
            })->pluck('email')->filter()->toArray();

            if (!empty($hrEmails)) {
                Mail::to($hrEmails)->send(new ApplicationSubmittedRecruiter($application));
            }
        } catch (\Throwable $e) {
            Log::info('Mail sending skipped/deferred: ' . $e->getMessage());
        }
    }
}
