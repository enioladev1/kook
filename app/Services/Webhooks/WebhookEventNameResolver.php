<?php

namespace App\Services\Webhooks;

/**
 * Best-effort extraction of the provider's event type (e.g. "charge.success")
 * from the payload, so the dashboard can show it without the viewer having
 * to open the raw payload. Providers disagree on the field name, so a short
 * list of the most common ones is tried in order.
 */
class WebhookEventNameResolver
{
    private const PAYLOAD_FIELD_CANDIDATES = ['event', 'type', 'event_type'];

    /**
     * @param  array<string, mixed>  $payload
     */
    public function resolve(array $payload): ?string
    {
        foreach (self::PAYLOAD_FIELD_CANDIDATES as $field) {
            if (! empty($payload[$field]) && is_scalar($payload[$field])) {
                return (string) $payload[$field];
            }
        }

        return null;
    }
}
