<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WebhookEndpoint;

class WebhookEndpointPolicy
{
    public function view(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->id === $webhookEndpoint->project->user_id;
    }

    public function update(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->id === $webhookEndpoint->project->user_id;
    }

    public function delete(User $user, WebhookEndpoint $webhookEndpoint): bool
    {
        return $user->id === $webhookEndpoint->project->user_id;
    }
}
