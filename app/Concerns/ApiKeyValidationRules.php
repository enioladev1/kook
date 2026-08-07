<?php

namespace App\Concerns;

trait ApiKeyValidationRules
{
    /**
     * @return array<int, string>
     */
    protected function apiKeyNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
