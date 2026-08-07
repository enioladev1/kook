<?php

namespace App\Models;

use App\Enums\WebhookEndpointMode;
use App\Enums\WebhookEndpointStatus;
use Database\Factories\WebhookEndpointFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $project_id
 * @property string $name
 * @property WebhookEndpointMode $mode
 * @property string $destination_url
 * @property string|null $provider_id
 * @property string|null $provider_secret
 * @property string $ingest_token
 * @property string $signing_secret
 * @property WebhookEndpointStatus $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WebhookEndpoint extends Model
{
    /** @use HasFactory<WebhookEndpointFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'mode',
        'destination_url',
        'provider_id',
        'provider_secret',
        'status',
    ];

    // provider_secret is write-only (Kook's input for verifying the provider,
    // never needs to be redisplayed). signing_secret is deliberately NOT
    // hidden: it's Kook's own outgoing signature for managed-mode forwarding,
    // and the endpoint owner needs to read it to verify requests on their
    // own server. Ownership/IDOR checks are what keep it from other users,
    // not this cast.
    protected $hidden = ['provider_secret'];

    protected function casts(): array
    {
        return [
            'mode' => WebhookEndpointMode::class,
            'status' => WebhookEndpointStatus::class,
            'provider_secret' => 'encrypted',
            'signing_secret' => 'encrypted',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<Provider, $this>
     */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    /**
     * @return HasMany<WebhookEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    /**
     * @return HasOne<WebhookEvent, $this>
     */
    public function latestEvent(): HasOne
    {
        // Not latestOfMany(): it always adds MAX(id) as a tiebreaker, and
        // ids are UUIDs here, which Postgres can't MAX(). A plain ordered
        // hasOne avoids the aggregate subquery entirely.
        return $this->hasOne(WebhookEvent::class)->orderByDesc('received_at');
    }
}
