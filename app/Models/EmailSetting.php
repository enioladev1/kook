<?php

namespace App\Models;

use App\Enums\EmailProvider;
use App\Enums\SmtpEncryption;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property EmailProvider $provider
 * @property string|null $from_address
 * @property string|null $from_name
 * @property string|null $api_key
 * @property string|null $smtp_host
 * @property int|null $smtp_port
 * @property string|null $smtp_username
 * @property string|null $smtp_password
 * @property SmtpEncryption|null $smtp_encryption
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read bool $has_api_key
 * @property-read bool $has_smtp_password
 */
class EmailSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'provider',
        'from_address',
        'from_name',
        'api_key',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
    ];

    // api_key and smtp_password are write-only: never redisplayed once saved.
    protected $hidden = ['api_key', 'smtp_password'];

    // Lets the frontend show "leave blank to keep the current key" only when
    // a secret actually exists, without ever exposing the secret itself.
    protected $appends = ['has_api_key', 'has_smtp_password'];

    protected function casts(): array
    {
        return [
            'provider' => EmailProvider::class,
            'smtp_port' => 'integer',
            'smtp_encryption' => SmtpEncryption::class,
            'api_key' => 'encrypted',
            'smtp_password' => 'encrypted',
        ];
    }

    /**
     * Whether credentials for the selected provider have actually been
     * entered yet - the row always exists (firstOrCreate default), but a
     * fresh install's row has nothing configured, and applying it as-is
     * would silently override valid .env-based mail config with nulls.
     */
    public function isConfigured(): bool
    {
        return match ($this->provider) {
            EmailProvider::Smtp => $this->smtp_host !== null,
            default => $this->api_key !== null,
        };
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function hasApiKey(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->api_key !== null,
        );
    }

    /**
     * @return Attribute<bool, never>
     */
    protected function hasSmtpPassword(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->smtp_password !== null,
        );
    }
}
