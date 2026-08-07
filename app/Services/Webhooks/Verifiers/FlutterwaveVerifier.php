<?php

namespace App\Services\Webhooks\Verifiers;

/**
 * Flutterwave signs the raw body with HMAC-SHA256 (secret hash as key,
 * base64-encoded) and sends it in the "flutterwave-signature" header.
 * Older/legacy accounts instead send a plain, unhashed copy of the
 * configured secret hash in a "verif-hash" header; both are accepted since
 * Flutterwave still supports either depending on when the account was set up.
 *
 * @see https://developer.flutterwave.com/docs/webhooks
 */
class FlutterwaveVerifier implements ProviderVerifierInterface
{
    public function verify(string $rawBody, array $headers, string $secret): bool
    {
        $signatureHeader = $headers['flutterwave-signature'] ?? null;

        if (is_string($signatureHeader) && $signatureHeader !== '') {
            $expected = base64_encode(hash_hmac('sha256', $rawBody, $secret, true));

            return hash_equals($expected, $signatureHeader);
        }

        $legacyHeader = $headers['verif-hash'] ?? null;

        if (is_string($legacyHeader) && $legacyHeader !== '') {
            return hash_equals($secret, $legacyHeader);
        }

        return false;
    }
}
