<?php

namespace App\Services\Webhooks;

/**
 * Best-effort extraction of a provider-supplied event id so retried
 * deliveries of the same event can be deduped. When no reliable id is
 * present we deliberately return null rather than hashing the body -
 * dropping a legitimately repeated payload is worse than double-processing
 * an unlikely hash collision.
 */
class WebhookIdempotencyKeyResolver
{
    private const HEADER_CANDIDATES = [
        'idempotency-key',
        'x-idempotency-key',
        'x-github-delivery',
        'x-shopify-webhook-id',
        'x-request-id',
    ];

    private const PAYLOAD_FIELD_CANDIDATES = ['id', 'event_id', 'eventId'];

    /**
     * @param  array<string, string>  $headers
     */
    public function resolve(string $rawBody, array $headers): ?string
    {
        foreach (self::HEADER_CANDIDATES as $header) {
            if (! empty($headers[$header])) {
                return (string) $headers[$header];
            }
        }

        $payload = json_decode($rawBody, true);

        if (is_array($payload)) {
            foreach (self::PAYLOAD_FIELD_CANDIDATES as $field) {
                if (! empty($payload[$field]) && is_scalar($payload[$field])) {
                    return (string) $payload[$field];
                }
            }
        }

        return null;
    }
}
