<?php

namespace App\Concerns;

use App\Enums\WebhookEndpointMode;
use App\Rules\PublicHttpUrl;
use Illuminate\Validation\Rule;

trait WebhookEndpointValidationRules
{
    /**
     * @return array<int, mixed>
     */
    protected function endpointNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function destinationUrlRules(): array
    {
        return ['required', 'string', 'max:2048', new PublicHttpUrl];
    }

    /**
     * @return array<int, mixed>
     */
    protected function modeRules(): array
    {
        return ['required', Rule::enum(WebhookEndpointMode::class)];
    }

    /**
     * @return array<int, mixed>
     */
    protected function providerIdRules(): array
    {
        return ['nullable', 'required_if:mode,managed', 'uuid', 'exists:providers,id'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function providerSecretRules(): array
    {
        return ['nullable', 'required_if:mode,managed', 'string', 'max:1024'];
    }

    /**
     * Optional on update: a blank value means "keep the current secret",
     * since the existing value is never redisplayed for the user to re-paste.
     *
     * @return array<int, mixed>
     */
    protected function providerSecretUpdateRules(): array
    {
        return ['nullable', 'string', 'max:1024'];
    }
}
