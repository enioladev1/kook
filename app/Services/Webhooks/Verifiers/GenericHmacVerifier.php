<?php

namespace App\Services\Webhooks\Verifiers;

/**
 * Verifies the "X-Webhook-Signature" header as a hex-encoded HMAC-SHA256
 * digest of the raw request body - the convention documented on the
 * provider's catalog entry for endpoints that don't match a named provider.
 */
class GenericHmacVerifier implements ProviderVerifierInterface
{
    public function verify(string $rawBody, array $headers, string $secret): bool
    {
        $signature = $headers['x-webhook-signature'] ?? null;

        if (! is_string($signature) || $signature === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
