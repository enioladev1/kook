<?php

namespace App\Actions;

class WebhookForwardResult
{
    public function __construct(
        public readonly bool $successful,
        public readonly ?int $statusCode,
        public readonly string $responseBody,
        public readonly int $durationMs,
        public readonly ?string $errorMessage,
    ) {}
}
