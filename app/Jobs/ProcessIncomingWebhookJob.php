<?php

namespace App\Jobs;

use App\Enums\WebhookEndpointMode;
use App\Enums\WebhookEventStatus;
use App\Models\WebhookEvent;
use App\Services\AuditLogService;
use App\Services\Webhooks\Verifiers\ProviderVerifierFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessIncomingWebhookJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public readonly string $webhookEventId) {}

    public function handle(ProviderVerifierFactory $verifierFactory, AuditLogService $auditLog): void
    {
        $event = WebhookEvent::with('webhookEndpoint.provider', 'webhookEndpoint.project')
            ->find($this->webhookEventId);

        if ($event === null) {
            return;
        }

        $event->update(['status' => WebhookEventStatus::Processing]);

        $endpoint = $event->webhookEndpoint;

        if ($endpoint->mode === WebhookEndpointMode::Managed) {
            $verified = $this->verifySignature($event, $verifierFactory);
            $event->update(['signature_valid' => $verified]);

            if (! $verified) {
                $event->update(['status' => WebhookEventStatus::Failed]);

                $auditLog->record(null, $endpoint->project, 'webhook_event.signature_invalid', $event, [
                    'webhook_endpoint_id' => $endpoint->id,
                ]);

                return;
            }
        }

        $event->update(['status' => WebhookEventStatus::Success]);

        ForwardWebhookDeliveryJob::dispatch($event->id)->afterCommit();
    }

    private function verifySignature(WebhookEvent $event, ProviderVerifierFactory $verifierFactory): bool
    {
        $endpoint = $event->webhookEndpoint;
        $provider = $endpoint->provider;

        if ($provider === null || $endpoint->provider_secret === null) {
            return false;
        }

        $verifier = $verifierFactory->make($provider->key);

        return $verifier->verify($event->raw_body, $event->headers, $endpoint->provider_secret);
    }
}
