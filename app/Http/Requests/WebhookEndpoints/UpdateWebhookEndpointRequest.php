<?php

namespace App\Http\Requests\WebhookEndpoints;

use App\Concerns\WebhookEndpointValidationRules;
use App\Enums\WebhookEndpointStatus;
use App\Models\WebhookEndpoint;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWebhookEndpointRequest extends FormRequest
{
    use WebhookEndpointValidationRules;

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
        return [
            'name' => $this->endpointNameRules(),
            'destination_url' => $this->destinationUrlRules(),
            'status' => ['required', Rule::enum(WebhookEndpointStatus::class)],
            'provider_secret' => $this->providerSecretUpdateRules(),
        ];
    }
}
