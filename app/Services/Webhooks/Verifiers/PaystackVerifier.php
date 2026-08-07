<?php

namespace App\Services\Webhooks\Verifiers;

/**
 * Verifies the "x-paystack-signature" header: a hex HMAC-SHA512 digest of the raw body.
 *
 * @see https://paystack.com/docs/payments/webhooks/#verifying-events
 */
class PaystackVerifier implements ProviderVerifierInterface
{
    public function verify(string $rawBody, array $headers, string $secret): bool
    {
        $header = $headers['x-paystack-signature'] ?? null;

        if (! is_string($header) || $header === '') {
            return false;
        }

        $expected = hash_hmac('sha512', $rawBody, $secret);

        return hash_equals($expected, $header);
    }
}
