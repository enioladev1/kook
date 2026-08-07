<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Request;

class AuditLogService
{
    /**
     * @param  array<string, mixed>  $metadata
     */
    public function record(
        ?User $user,
        ?Project $project,
        string $action,
        ?Model $auditable = null,
        array $metadata = [],
    ): AuditLog {
        return AuditLog::create([
            'user_id' => $user?->id,
            'project_id' => $project?->id,
            'action' => $action,
            'auditable_type' => $auditable?->getMorphClass(),
            'auditable_id' => $auditable?->getKey(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'metadata' => $metadata,
        ]);
    }
}
