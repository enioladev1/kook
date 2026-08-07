<?php

namespace App\Listeners;

use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Auth\Events\Login;

class LogSuccessfulLogin
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function handle(Login $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->auditLog->record($event->user, null, 'auth.login', $event->user, [
            'guard' => $event->guard,
        ]);
    }
}
