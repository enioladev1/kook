<?php

namespace App\Http\Requests\Settings;

use App\Concerns\EmailSettingValidationRules;
use Illuminate\Foundation\Http\FormRequest;

class UpdateEmailSettingRequest extends FormRequest
{
    use EmailSettingValidationRules;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'provider' => $this->providerRules(),
            'from_address' => $this->fromAddressRules(),
            'from_name' => $this->fromNameRules(),
            'api_key' => $this->apiKeyRules(),
            'smtp_host' => $this->smtpHostRules(),
            'smtp_port' => $this->smtpPortRules(),
            'smtp_username' => $this->smtpUsernameRules(),
            'smtp_password' => $this->smtpPasswordRules(),
            'smtp_encryption' => $this->smtpEncryptionRules(),
        ];
    }
}
