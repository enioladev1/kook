<?php

namespace App\Http\Requests\ApiKeys;

use App\Concerns\ApiKeyValidationRules;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;

class StoreApiKeyRequest extends FormRequest
{
    use ApiKeyValidationRules;

    public function authorize(): bool
    {
        /** @var Project $project */
        $project = $this->route('project');

        return $this->user()?->can('view', $project) ?? false;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => $this->apiKeyNameRules(),
        ];
    }
}
