<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ApplicationShortlistedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Application $application;

    public function __construct(Application $application)
    {
        $this->application = $application;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Congratulations! You have been shortlisted for ' . $this->application->jobPosting->title . ' (Ref: ' . $this->application->reference_code . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.application-shortlisted',
        );
    }
}
