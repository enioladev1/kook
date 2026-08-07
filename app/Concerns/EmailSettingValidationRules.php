<?php

namespace App\Concerns;

use App\Enums\EmailProvider;
use App\Enums\SmtpEncryption;
use Illuminate\Validation\Rule;

trait EmailSettingValidationRules
{
    /**
     * @return array<int, mixed>
     */
    protected function providerRules(): array
    {
        return ['required', Rule::enum(EmailProvider::class)];
    }

    /**
     * @return array<int, mixed>
     */
    protected function fromAddressRules(): array
    {
        return ['required', 'email', 'max:255'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function fromNameRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Optional: a blank value means "keep the current key", since the
     * existing value is never redisplayed for the user to re-paste.
     *
     * @return array<int, mixed>
     */
    protected function apiKeyRules(): array
    {
        return ['nullable', 'string', 'max:1024'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function smtpHostRules(): array
    {
        return ['nullable', 'required_if:provider,smtp', 'string', 'max:255'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function smtpPortRules(): array
    {
        return ['nullable', 'required_if:provider,smtp', 'integer', 'between:1,65535'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function smtpUsernameRules(): array
    {
        return ['nullable', 'string', 'max:255'];
    }

    /**
     * Optional: a blank value means "keep the current password", same as apiKeyRules().
     *
     * @return array<int, mixed>
     */
    protected function smtpPasswordRules(): array
    {
        return ['nullable', 'string', 'max:1024'];
    }

    /**
     * @return array<int, mixed>
     */
    protected function smtpEncryptionRules(): array
    {
        return ['nullable', 'required_if:provider,smtp', Rule::enum(SmtpEncryption::class)];
    }
}
