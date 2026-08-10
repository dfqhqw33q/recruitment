<?php

namespace App\Services;

use App\Mail\ApplicationHiredMail;
use App\Mail\ApplicationRejectedMail;
use App\Mail\ApplicationShortlistedMail;
use App\Mail\InterviewInvitationMail;
use App\Mail\OfferIssuedMail;
use App\Models\Application;
use App\Models\Interview;
use App\Models\NotificationRecord;
use App\Models\OfferLetter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ApplicationStageNotificationService
{
    /**
     * Handle stage change notification when application status updates.
     */
    public function notifyStatusChange(Application $application, string $oldStatus, string $newStatus): void
    {
        if ($oldStatus === $newStatus) return;

        $applicant = $application->applicant;
        if (!$applicant || !$applicant->user_id) return;

        $userId = $applicant->user_id;
        $jobTitle = $application->jobPosting->title ?? 'Position';
        $refCode = $application->reference_code;

        switch ($newStatus) {
            case 'shortlisted':
                NotificationRecord::create([
                    'user_id' => $userId,
                    'type' => 'status_shortlisted',
                    'title' => 'Application Shortlisted!',
                    'message' => "Congratulations! Your application for '{$jobTitle}' (Ref: {$refCode}) has been shortlisted.",
                    'icon' => 'fa-star',
                    'action_url' => route('applicant.track'),
                ]);
                $this->sendMail($applicant->email, new ApplicationShortlistedMail($application));
                break;

            case 'for_interview':
            case 'interviewed':
                NotificationRecord::create([
                    'user_id' => $userId,
                    'type' => 'status_interview',
                    'title' => 'Interview Stage Update',
                    'message' => "Your application for '{$jobTitle}' (Ref: {$refCode}) is in the interview stage.",
                    'icon' => 'fa-video',
                    'action_url' => route('applicant.track'),
                ]);
                $this->sendMail($applicant->email, new InterviewInvitationMail($application));
                break;

            case 'offer_sent':
                NotificationRecord::create([
                    'user_id' => $userId,
                    'type' => 'status_offer',
                    'title' => 'Job Offer Received!',
                    'message' => "An official offer letter has been issued for '{$jobTitle}'. Please review and respond in your candidate portal.",
                    'icon' => 'fa-file-contract',
                    'action_url' => route('applicant.track'),
                ]);
                if ($application->offerLetter) {
                    $this->sendMail($applicant->email, new OfferIssuedMail($application->offerLetter));
                }
                break;

            case 'hired':
                NotificationRecord::create([
                    'user_id' => $userId,
                    'type' => 'status_hired',
                    'title' => 'Welcome Aboard! Official Hire Confirmation',
                    'message' => "Congratulations! You have been officially hired for '{$jobTitle}'!",
                    'icon' => 'fa-champagne-glasses',
                    'action_url' => route('applicant.track'),
                ]);
                $this->sendMail($applicant->email, new ApplicationHiredMail($application));
                break;

            case 'rejected':
                NotificationRecord::create([
                    'user_id' => $userId,
                    'type' => 'status_rejected',
                    'title' => 'Application Status Update',
                    'message' => "Your application for '{$jobTitle}' (Ref: {$refCode}) was reviewed and updated.",
                    'icon' => 'fa-circle-info',
                    'action_url' => route('applicant.track'),
                ]);
                $this->sendMail($applicant->email, new ApplicationRejectedMail($application));
                break;

            default:
                NotificationRecord::create([
                    'user_id' => $userId,
                    'type' => 'status_update',
                    'title' => 'Application Status Updated',
                    'message' => "Your application status for '{$jobTitle}' (Ref: {$refCode}) changed to " . ucfirst(str_replace('_', ' ', $newStatus)) . ".",
                    'icon' => 'fa-circle-notch',
                    'action_url' => route('applicant.track'),
                ]);
                break;
        }
    }

    /**
     * Notify candidate when an interview is explicitly scheduled.
     */
    public function notifyInterviewScheduled(Interview $interview): void
    {
        $application = $interview->application;
        if (!$application) return;

        $applicant = $application->applicant;
        if (!$applicant || !$applicant->user_id) return;

        NotificationRecord::create([
            'user_id' => $applicant->user_id,
            'type' => 'interview_scheduled',
            'title' => 'Interview Scheduled: ' . ucfirst($interview->type),
            'message' => "An interview for '{$application->jobPosting->title}' is scheduled for " . \Carbon\Carbon::parse($interview->scheduled_at)->format('M d, Y g:i A') . ".",
            'icon' => 'fa-calendar-check',
            'action_url' => route('applicant.track'),
        ]);

        $this->sendMail($applicant->email, new InterviewInvitationMail($application, $interview));
    }

    /**
     * Notify candidate when an offer letter is sent.
     */
    public function notifyOfferSent(OfferLetter $offer): void
    {
        $application = $offer->application;
        if (!$application) return;

        $applicant = $application->applicant;
        if (!$applicant || !$applicant->user_id) return;

        NotificationRecord::create([
            'user_id' => $applicant->user_id,
            'type' => 'offer_sent',
            'title' => 'Job Offer Letter Issued',
            'message' => "You have received a job offer letter for '{$offer->jobPosting->title}' (Offer Ref: {$offer->offer_number}).",
            'icon' => 'fa-file-contract',
            'action_url' => route('applicant.track'),
        ]);

        $this->sendMail($applicant->email, new OfferIssuedMail($offer));
    }

    /**
     * Safely send emails wrapping in try-catch.
     */
    protected function sendMail(?string $email, $mailable): void
    {
        if (!$email) return;

        try {
            Mail::to($email)->send($mailable);
        } catch (\Throwable $e) {
            Log::info('Stage change mail notification deferred: ' . $e->getMessage());
        }
    }
}
