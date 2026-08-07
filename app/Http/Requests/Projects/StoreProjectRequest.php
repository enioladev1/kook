<?php

namespace App\Http\Requests\Projects;

use App\Concerns\ProjectValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
{
    use ProjectValidationRules;

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => $this->projectNameRules(),
        ];
    }
}
