<?php

namespace App\Actions;

use App\Enums\WebhookEndpointMode;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;
use Throwable;

class ForwardWebhookAction
{
    private const RESPONSE_BODY_MAX_LENGTH = 10000;

    private const TIMEOUT_SECONDS = 15;

    /**
     * Headers that describe the hop to us, not the payload, so they must
     * never be replayed onto the outbound request to the customer's server.
     *
     * @var list<string>
     */
    private const STRIPPED_HEADERS = [
        'host',
        'content-length',
        'content-encoding',
        'connection',
        'transfer-encoding',
        'x-forwarded-for',
        'x-forwarded-host',
        'x-forwarded-proto',
    ];

    public function execute(WebhookEvent $event): WebhookForwardResult
    {
        $endpoint = $event->webhookEndpoint;
        $startedAt = microtime(true);

        try {
            $request = Http::timeout(self::TIMEOUT_SECONDS)
                ->withHeaders($this->buildHeaders($event));

            $response = $endpoint->mode === WebhookEndpointMode::Relay
                ? $request->withBody($event->raw_body, $this->contentType($event))->post($endpoint->destination_url)
                : $request->withBody(json_encode($event->payload) ?: '{}', 'application/json')->post($endpoint->destination_url);

            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            return new WebhookForwardResult(
                successful: $response->successful(),
                statusCode: $response->status(),
                responseBody: substr($response->body(), 0, self::RESPONSE_BODY_MAX_LENGTH),
                durationMs: $durationMs,
                errorMessage: $response->successful() ? null : "Destination responded with status {$response->status()}.",
            );
        } catch (Throwable) {
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            return new WebhookForwardResult(
                successful: false,
                statusCode: null,
                responseBody: '',
                durationMs: $durationMs,
                errorMessage: 'Unable to reach the destination URL.',
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function buildHeaders(WebhookEvent $event): array
    {
        $endpoint = $event->webhookEndpoint;

        if ($endpoint->mode === WebhookEndpointMode::Relay) {
            $headers = [];

            foreach ($event->headers as $name => $value) {
                if (! in_array(strtolower((string) $name), self::STRIPPED_HEADERS, true)) {
                    $headers[$name] = $value;
                }
            }

            return $headers;
        }

        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', "{$timestamp}.{$event->raw_body}", $endpoint->signing_secret);

        return [
            'X-Kook-Signature' => "t={$timestamp},v1={$signature}",
            'X-Kook-Event-Id' => $event->id,
        ];
    }

    private function contentType(WebhookEvent $event): string
    {
        return $event->headers['content-type'] ?? 'application/json';
    }
}
