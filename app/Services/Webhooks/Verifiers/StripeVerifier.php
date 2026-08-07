<?php

namespace App\Services\Webhooks\Verifiers;

/**
 * Verifies the "Stripe-Signature" header: "t=<timestamp>,v1=<signature>[,v1=<signature>...]".
 * The signed payload is "{timestamp}.{raw body}" and a timestamp tolerance
 * guards against replaying an old, otherwise-valid signature.
 *
 * @see https://docs.stripe.com/webhooks#verify-manually
 */
class StripeVerifier implements ProviderVerifierInterface
{
    private const TOLERANCE_SECONDS = 300;

    public function verify(string $rawBody, array $headers, string $secret): bool
    {
        $header = $headers['stripe-signature'] ?? null;

        if (! is_string($header) || $header === '') {
            return false;
        }

        [$timestamp, $signatures] = $this->parseHeader($header);

        if ($timestamp === null || $signatures === []) {
            return false;
        }

        if (abs(time() - $timestamp) > self::TOLERANCE_SECONDS) {
            return false;
        }

        $expected = hash_hmac('sha256', "{$timestamp}.{$rawBody}", $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expected, $signature)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{0: int|null, 1: list<string>}
     */
    private function parseHeader(string $header): array
    {
        $timestamp = null;
        $signatures = [];

        foreach (explode(',', $header) as $pair) {
            [$key, $value] = array_pad(explode('=', $pair, 2), 2, null);

            if ($key === 't' && is_string($value) && ctype_digit($value)) {
                $timestamp = (int) $value;
            } elseif ($key === 'v1' && is_string($value) && $value !== '') {
                $signatures[] = $value;
            }
        }

        return [$timestamp, $signatures];
    }
}
