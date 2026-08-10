<?php

namespace App\Mail;

use App\Models\OfferLetter;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OfferIssuedMail extends Mailable
{
    use Queueable, SerializesModels;

    public OfferLetter $offer;

    public function __construct(OfferLetter $offer)
    {
        $this->offer = $offer;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Job Offer Letter: ' . $this->offer->jobPosting->title . ' - ' . config('app.name', 'Recruitment System'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.offer-issued',
        );
    }
}
