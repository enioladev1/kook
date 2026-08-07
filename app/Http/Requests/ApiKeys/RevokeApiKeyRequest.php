<?php

namespace App\Http\Requests\ApiKeys;

use App\Models\ApiKey;
use Illuminate\Foundation\Http\FormRequest;

class RevokeApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ApiKey $apiKey */
        $apiKey = $this->route('api_key');

        return $this->user()?->can('revoke', $apiKey) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
