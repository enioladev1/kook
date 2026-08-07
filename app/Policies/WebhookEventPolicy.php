<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebhookEvent;

class WebhookEventPolicy
{
    public function view(User $user, WebhookEvent $webhookEvent): bool
    {
        return $user->id === $webhookEvent->project->user_id;
    }

    public function replay(User $user, WebhookEvent $webhookEvent): bool
    {
        return $user->id === $webhookEvent->project->user_id;
    }
}
