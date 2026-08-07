<?php

namespace App\Services\Webhooks;

use App\Enums\WebhookEndpointStatus;
use App\Enums\WebhookEventStatus;
use App\Exceptions\WebhookEndpointNotFoundException;
use App\Jobs\ProcessIncomingWebhookJob;
use App\Models\WebhookEvent;
use App\Repositories\WebhookEndpointRepository;
use App\Repositories\WebhookEventRepository;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class WebhookIngestService
{
    public function __construct(
        private readonly WebhookEndpointRepository $endpoints,
        private readonly WebhookEventRepository $events,
        private readonly WebhookIdempotencyKeyResolver $idempotency,
        private readonly WebhookEventNameResolver $eventName,
    ) {}

    /**
     * @param  array<string, string>  $headers
     *
     * @throws WebhookEndpointNotFoundException
     */
    public function ingest(string $ingestToken, string $rawBody, array $headers): WebhookEvent
    {
        $endpoint = $this->endpoints->findByIngestToken($ingestToken);

        if ($endpoint === null || $endpoint->status !== WebhookEndpointStatus::Active) {
            throw new WebhookEndpointNotFoundException;
        }

        $idempotencyKey = $this->idempotency->resolve($rawBody, $headers);

        if ($idempotencyKey !== null) {
            $existing = $this->events->findByIdempotencyKey($endpoint, $idempotencyKey);

            if ($existing !== null) {
                return $existing;
            }
        }

        $payload = json_decode($rawBody, true);
        $payload = is_array($payload) ? $payload : ['raw' => $rawBody];

        $attributes = [
            'project_id' => $endpoint->project_id,
            'idempotency_key' => $idempotencyKey,
            'event_name' => $this->eventName->resolve($payload),
            'headers' => $headers,
            'payload' => $payload,
            'raw_body' => $rawBody,
            'status' => WebhookEventStatus::Pending,
            'received_at' => now(),
        ];

        try {
            return DB::transaction(function () use ($endpoint, $attributes) {
                $event = $this->events->create($endpoint, $attributes);

                ProcessIncomingWebhookJob::dispatch($event->id)->afterCommit();

                return $event;
            });
        } catch (UniqueConstraintViolationException $e) {
            // Lost a race against a concurrent delivery of the same event;
            // whoever won already queued processing, so just return theirs.
            if ($idempotencyKey !== null) {
                $existing = $this->events->findByIdempotencyKey($endpoint, $idempotencyKey);

                if ($existing !== null) {
                    return $existing;
                }
            }

            throw $e;
        }
    }
}
