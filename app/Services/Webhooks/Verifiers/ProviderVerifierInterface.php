<?php

namespace App\Services\Webhooks\Verifiers;

interface ProviderVerifierInterface
{
    /**
     * Verify the incoming webhook's signature against the endpoint's configured secret.
     *
     * @param  array<string, string>  $headers
     */
    public function verify(string $rawBody, array $headers, string $secret): bool;
}
