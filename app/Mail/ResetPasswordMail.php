<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public readonly string $resetUrl;

    public readonly int $expiresInMinutes;

    public function __construct(string $token, string $email)
    {
        $this->resetUrl = url(route('password.reset', [
            'token' => $token,
            'email' => $email,
        ], false));

        $this->expiresInMinutes = (int) config('auth.passwords.users.expire', 60);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reset your Kook password',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.reset-password',
            text: 'emails.reset-password-text',
        );
    }
}
