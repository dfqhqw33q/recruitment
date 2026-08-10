<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Interview;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class InterviewInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public Application $application;
    public ?Interview $interview;

    public function __construct(Application $application, ?Interview $interview = null)
    {
        $this->application = $application;
        $this->interview = $interview;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Interview Invitation: ' . $this->application->jobPosting->title . ' (Ref: ' . $this->application->reference_code . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.interview-invitation',
        );
    }
}
