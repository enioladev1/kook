<?php

namespace App\Concerns;

trait ProjectValidationRules
{
    /**
     * @return array<int, string>
     */
    protected function projectNameRules(): array
    {
        return ['required', 'string', 'max:255'];
    }
}
