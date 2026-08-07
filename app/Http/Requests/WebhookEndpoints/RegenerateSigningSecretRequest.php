<?php

namespace App\Http\Requests\WebhookEndpoints;

use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Http\FormRequest;

class RegenerateSigningSecretRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WebhookEndpoint $webhookEndpoint */
        $webhookEndpoint = $this->route('webhook_endpoint');

        return $this->user()?->can('update', $webhookEndpoint) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
