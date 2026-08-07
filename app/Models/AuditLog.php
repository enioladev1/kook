<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;

/**
 * @property string $id
 * @property string|null $user_id
 * @property string|null $project_id
 * @property string $action
 * @property string|null $auditable_type
 * @property string|null $auditable_id
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property array<string, mixed>|null $metadata
 * @property Carbon|null $created_at
 */
class AuditLog extends Model
{
    use HasUuids;

    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'project_id',
        'action',
        'auditable_type',
        'auditable_id',
        'ip_address',
        'user_agent',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Audit logs are append-only; the database enforces this too via a
     * trigger, but failing fast here avoids a raw SQL error reaching callers.
     */
    public function update(array $attributes = [], array $options = []): bool
    {
        throw new LogicException('Audit logs are append-only and cannot be updated.');
    }

    public function delete(): ?bool
    {
        throw new LogicException('Audit logs are append-only and cannot be deleted.');
    }
}
