<?php

namespace App\Services\Webhooks\Verifiers;

/**
 * Verifies the "X-Shopify-Hmac-Sha256" header: a base64-encoded (not hex)
 * HMAC-SHA256 digest of the raw body.
 *
 * @see https://shopify.dev/docs/apps/build/webhooks/subscribe/https#step-5-verify-the-webhook
 */
class ShopifyVerifier implements ProviderVerifierInterface
{
    public function verify(string $rawBody, array $headers, string $secret): bool
    {
        $header = $headers['x-shopify-hmac-sha256'] ?? null;

        if (! is_string($header) || $header === '') {
            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $rawBody, $secret, true));

        return hash_equals($expected, $header);
    }
}
