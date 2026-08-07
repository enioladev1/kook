<?php

namespace App\Models;

use App\Enums\WebhookEventStatus;
use Database\Factories\WebhookEventFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * @property string $id
 * @property string $webhook_endpoint_id
 * @property string $project_id
 * @property string|null $idempotency_key
 * @property string|null $event_name
 * @property array<string, mixed> $headers
 * @property array<string, mixed> $payload
 * @property string $raw_body
 * @property bool|null $signature_valid
 * @property WebhookEventStatus $status
 * @property Carbon $received_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class WebhookEvent extends Model
{
    /** @use HasFactory<WebhookEventFactory> */
    use HasFactory, HasUuids;

    protected $fillable = [
        'project_id',
        'idempotency_key',
        'event_name',
        'headers',
        'payload',
        'raw_body',
        'signature_valid',
        'status',
        'received_at',
    ];

    protected function casts(): array
    {
        return [
            'headers' => 'array',
            'payload' => 'array',
            'signature_valid' => 'boolean',
            'status' => WebhookEventStatus::class,
            'received_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<WebhookEndpoint, $this>
     */
    public function webhookEndpoint(): BelongsTo
    {
        return $this->belongsTo(WebhookEndpoint::class);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return HasMany<WebhookDelivery, $this>
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(WebhookDelivery::class, 'event_id');
    }

    /**
     * @return HasOne<WebhookDelivery, $this>
     */
    public function latestDelivery(): HasOne
    {
        // Not latestOfMany(): same UUID/MAX() issue as
        // WebhookEndpoint::latestEvent(), avoided the same way.
        return $this->hasOne(WebhookDelivery::class, 'event_id')->orderByDesc('attempt_number');
    }
}
