<?php

namespace App\Services;

use App\Mail\WebhookDeliveryFailedMail;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Mail;

class WebhookFailureNotificationService
{
    /**
     * Email the project owner once a delivery has exhausted all retries.
     * Requires webhookEndpoint.project.user to already be eager-loaded.
     */
    public function notifyExhausted(WebhookEvent $event): void
    {
        $endpoint = $event->webhookEndpoint;
        $project = $endpoint->project;
        $user = $project->user;

        if (! $project->failure_emails_enabled) {
            return;
        }

        if (! $user || ! $user->email) {
            return;
        }

        Mail::to($user->email)->queue(new WebhookDeliveryFailedMail(
            projectName: $project->name,
            endpointName: $endpoint->name,
            destinationUrl: $endpoint->destination_url,
            eventName: $event->event_name,
        ));
    }
}
