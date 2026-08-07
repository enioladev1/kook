<?php

namespace App\Http\Requests\ApiKeys;

use App\Models\ApiKey;
use Illuminate\Foundation\Http\FormRequest;

class DestroyApiKeyRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var ApiKey $apiKey */
        $apiKey = $this->route('api_key');

        return $this->user()?->can('delete', $apiKey) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
