<?php

namespace App\Services\Webhooks\Verifiers;

/**
 * Verifies the "X-Hub-Signature-256" header: "sha256=<hex hmac-sha256 of raw body>".
 *
 * @see https://docs.github.com/webhooks/using-webhooks/validating-webhook-deliveries
 */
class GitHubVerifier implements ProviderVerifierInterface
{
    private const PREFIX = 'sha256=';

    public function verify(string $rawBody, array $headers, string $secret): bool
    {
        $header = $headers['x-hub-signature-256'] ?? null;

        if (! is_string($header) || ! str_starts_with($header, self::PREFIX)) {
            return false;
        }

        $signature = substr($header, strlen(self::PREFIX));
        $expected = hash_hmac('sha256', $rawBody, $secret);

        return hash_equals($expected, $signature);
    }
}
