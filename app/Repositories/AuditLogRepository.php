<?php

namespace App\Repositories;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditLogRepository
{
    /**
     * @return LengthAwarePaginator<int, AuditLog>
     */
    public function paginateForUser(User $user, int $perPage = 25): LengthAwarePaginator
    {
        $projectIds = $user->projects()->pluck('id');

        return AuditLog::query()
            ->with('user:id,name,email')
            ->where(function ($query) use ($user, $projectIds) {
                $query->whereIn('project_id', $projectIds)
                    ->orWhere(function ($query) use ($user) {
                        $query->where('user_id', $user->id)->whereNull('project_id');
                    });
            })
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
