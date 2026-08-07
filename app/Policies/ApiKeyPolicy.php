<?php

namespace App\Policies;

use App\Models\ApiKey;
use App\Models\User;

class ApiKeyPolicy
{
    public function revoke(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->project->user_id;
    }

    public function delete(User $user, ApiKey $apiKey): bool
    {
        return $user->id === $apiKey->project->user_id;
    }
}
