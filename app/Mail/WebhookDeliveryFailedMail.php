<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WebhookDeliveryFailedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $projectName,
        public readonly string $endpointName,
        public readonly string $destinationUrl,
        public readonly ?string $eventName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Webhook delivery failing: {$this->endpointName}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.webhook-delivery-failed',
            text: 'emails.webhook-delivery-failed-text',
        );
    }
}
