<?php

namespace Database\Seeders;

use App\Enums\ProviderKey;
use App\Enums\WebhookDeliveryStatus;
use App\Enums\WebhookEndpointMode;
use App\Enums\WebhookEndpointStatus;
use App\Enums\WebhookEventStatus;
use App\Models\Project;
use App\Models\Provider;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// Demo data for the single admin account: one project, three endpoints, and a
// spread of events/deliveries. Idempotent - re-running leaves existing data be.
class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->oldest()->first();

        // Nothing to attach demo data to on a fresh install.
        if ($user === null) {
            return;
        }

        $project = Project::query()->firstOrCreate(
            ['user_id' => $user->id, 'slug' => 'acme-payments'],
            ['name' => 'Acme Payments'],
        );

        // Idempotent: bail if the sample project was already populated.
        if ($project->webhookEndpoints()->exists()) {
            return;
        }

        $stripe = $this->endpoint($project, 'Stripe production', WebhookEndpointMode::Managed, 'https://api.acme.test/webhooks/stripe', ProviderKey::Stripe, WebhookEndpointStatus::Active);
        $github = $this->endpoint($project, 'GitHub CI relay', WebhookEndpointMode::Relay, 'https://ci.acme.test/hooks/github', ProviderKey::GitHub, WebhookEndpointStatus::Active);
        $shopify = $this->endpoint($project, 'Shopify orders (paused)', WebhookEndpointMode::Relay, 'https://ops.acme.test/shopify', ProviderKey::Shopify, WebhookEndpointStatus::Paused);

        $this->deliveredEvent($stripe, 'payment_intent.succeeded', <<<'JSON'
        {
          "id": "evt_3Pload1234",
          "object": "event",
          "api_version": "2024-06-20",
          "created": 1757370000,
          "type": "payment_intent.succeeded",
          "livemode": true,
          "data": {
            "object": {
              "id": "pi_3Pload5678",
              "object": "payment_intent",
              "amount": 4200,
              "currency": "usd",
              "status": "succeeded",
              "customer": "cus_QabcXYZ"
            }
          }
        }
        JSON);

        $this->deliveredEvent($github, 'push', <<<'JSON'
        {
          "ref": "refs/heads/main",
          "before": "9f1c00e",
          "after": "a2b7d41",
          "repository": {
            "id": 812345678,
            "name": "acme-web",
            "full_name": "acme/acme-web",
            "private": true
          },
          "pusher": { "name": "eniola", "email": "eng@acme.test" },
          "commits": [
            { "id": "a2b7d41", "message": "fix: retry webhook delivery on 503", "distinct": true }
          ]
        }
        JSON);

        $this->failingEvent($stripe, 'charge.refunded', <<<'JSON'
        {
          "id": "evt_3Prefund9999",
          "type": "charge.refunded",
          "object": "event",
          "created": 1757373600,
          "livemode": true,
          "data": {
            "object": {
              "id": "ch_3Pxyz",
              "amount_refunded": 4200,
              "currency": "usd",
              "refunded": true
            }
          }
        }
        JSON);

        $this->retryingEvent($github, 'pull_request', <<<'JSON'
        {
          "action": "opened",
          "number": 147,
          "pull_request": {
            "id": 2298374,
            "state": "open",
            "title": "Colored payload viewer",
            "user": { "login": "eniola" },
            "head": { "ref": "feature/payload-viewer" },
            "base": { "ref": "main" }
          }
        }
        JSON);

        $this->pendingEvent($shopify, 'orders/create', <<<'JSON'
        {
          "id": 5123456789,
          "email": "buyer@example.test",
          "created_at": "2026-09-08T22:14:07-04:00",
          "currency": "USD",
          "total_price": "129.00",
          "financial_status": "paid",
          "line_items": [
            { "id": 111, "title": "Relay plan (annual)", "quantity": 1, "price": "129.00" }
          ]
        }
        JSON);

        $this->invalidSignatureEvent($stripe, 'invoice.payment_failed', <<<'JSON'
        {
          "id": "evt_3Pinvfail",
          "type": "invoice.payment_failed",
          "object": "event",
          "created": 1757377200,
          "data": { "object": { "id": "in_1Pabc", "attempt_count": 2, "paid": false } }
        }
        JSON);
    }

    private function endpoint(
        Project $project,
        string $name,
        WebhookEndpointMode $mode,
        string $destinationUrl,
        ProviderKey $providerKey,
        WebhookEndpointStatus $status,
    ): WebhookEndpoint {
        $endpoint = $project->webhookEndpoints()->make([
            'name' => $name,
            'mode' => $mode,
            'destination_url' => $destinationUrl,
            'provider_id' => Provider::query()->where('key', $providerKey)->value('id'),
        ]);

        $endpoint->forceFill([
            'ingest_token' => Str::random(40),
            'signing_secret' => Str::random(48),
            'status' => $status,
        ])->save();

        return $endpoint;
    }

    private function makeEvent(
        WebhookEndpoint $endpoint,
        string $eventName,
        string $rawBody,
        WebhookEventStatus $status,
        ?bool $signatureValid,
        int $minutesAgo,
    ): WebhookEvent {
        return WebhookEvent::factory()
            ->for($endpoint, 'webhookEndpoint')
            ->create([
                'event_name' => $eventName,
                'idempotency_key' => Str::uuid()->toString(),
                'headers' => [
                    'content-type' => 'application/json',
                    'user-agent' => 'Acme-Webhooks/1.0',
                ],
                'payload' => json_decode($rawBody, true),
                'raw_body' => $rawBody,
                'signature_valid' => $signatureValid,
                'status' => $status,
                'received_at' => now()->subMinutes($minutesAgo),
            ]);
    }

    private function deliveredEvent(WebhookEndpoint $endpoint, string $name, string $rawBody): void
    {
        $event = $this->makeEvent($endpoint, $name, $rawBody, WebhookEventStatus::Success, true, random_int(20, 400));

        WebhookDelivery::factory()->for($event, 'event')->create([
            'attempt_number' => 1,
            'status' => WebhookDeliveryStatus::Delivered,
            'http_status_code' => 200,
            'response_body' => '{"received":true}',
            'duration_ms' => random_int(80, 350),
            'delivered_at' => $event->received_at->addSeconds(2),
        ]);
    }

    private function failingEvent(WebhookEndpoint $endpoint, string $name, string $rawBody): void
    {
        $event = $this->makeEvent($endpoint, $name, $rawBody, WebhookEventStatus::Failed, true, 90);

        foreach ([500, 502, 503] as $i => $code) {
            WebhookDelivery::factory()->for($event, 'event')->create([
                'attempt_number' => $i + 1,
                'status' => WebhookDeliveryStatus::Failed,
                'http_status_code' => $code,
                'error_message' => "Destination returned HTTP {$code}",
                'duration_ms' => random_int(1000, 4000),
            ]);
        }
    }

    private function retryingEvent(WebhookEndpoint $endpoint, string $name, string $rawBody): void
    {
        $event = $this->makeEvent($endpoint, $name, $rawBody, WebhookEventStatus::Processing, true, 6);

        WebhookDelivery::factory()->for($event, 'event')->create([
            'attempt_number' => 1,
            'status' => WebhookDeliveryStatus::Failed,
            'error_message' => 'Connection timed out after 10000ms',
            'duration_ms' => 10000,
        ]);

        WebhookDelivery::factory()->for($event, 'event')->create([
            'attempt_number' => 2,
            'status' => WebhookDeliveryStatus::Retrying,
            'next_retry_at' => now()->addMinutes(4),
        ]);
    }

    private function pendingEvent(WebhookEndpoint $endpoint, string $name, string $rawBody): void
    {
        $this->makeEvent($endpoint, $name, $rawBody, WebhookEventStatus::Pending, null, 1);
    }

    private function invalidSignatureEvent(WebhookEndpoint $endpoint, string $name, string $rawBody): void
    {
        $this->makeEvent($endpoint, $name, $rawBody, WebhookEventStatus::Failed, false, 45);
    }
}
