<?php

namespace App\Http\Requests\WebhookEndpoints;

use App\Concerns\WebhookEndpointValidationRules;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreWebhookEndpointRequest extends FormRequest
{
    use WebhookEndpointValidationRules;

    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');

        return $this->user()?->can('view', $project) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => $this->endpointNameRules(),
            'destination_url' => $this->destinationUrlRules(),
            'mode' => $this->modeRules(),
            'provider_id' => $this->providerIdRules(),
            'provider_secret' => $this->providerSecretRules(),
        ];
    }
}
