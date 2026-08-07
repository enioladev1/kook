<?php

namespace App\Models;

use App\Enums\ProviderKey;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property ProviderKey $key
 * @property string $name
 * @property string|null $docs_url
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Provider extends Model
{
    use HasUuids;

    protected $fillable = ['key', 'name', 'docs_url', 'is_active'];

    protected function casts(): array
    {
        return [
            'key' => ProviderKey::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<WebhookEndpoint, $this>
     */
    public function webhookEndpoints(): HasMany
    {
        return $this->hasMany(WebhookEndpoint::class);
    }
}
