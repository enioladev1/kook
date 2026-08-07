<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class TestEmail extends Mailable
{
    use Queueable;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kook test email',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.test',
            text: 'emails.test-text',
        );
    }
}
