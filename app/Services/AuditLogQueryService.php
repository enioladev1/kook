<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use App\Repositories\AuditLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogQueryService
{
    public function __construct(private readonly AuditLogRepository $auditLogs) {}

    /**
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function listForUser(User $user): LengthAwarePaginator
    {
        return $this->auditLogs->paginateForUser($user);
    }
}
