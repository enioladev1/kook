<?php

namespace App\Repositories;

use App\Enums\EmailProvider;
use App\Models\EmailSetting;

class EmailSettingRepository
{
    public function current(): EmailSetting
    {
        /** @var EmailSetting */
        return EmailSetting::query()->firstOrCreate([], ['provider' => EmailProvider::Smtp]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EmailSetting $setting, array $data): EmailSetting
    {
        $setting->update($data);

        return $setting;
    }
}
