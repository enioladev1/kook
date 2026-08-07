<?php

namespace App\Repositories;

use App\Models\ApiKey;
use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;

class ApiKeyRepository
{
    /**
     * @return Collection<int, ApiKey>
     */
    public function forProject(Project $project): Collection
    {
        /** @var Collection<int, ApiKey> */
        return $project->apiKeys()->latest()->get();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function make(Project $project, array $data): ApiKey
    {
        /** @var ApiKey */
        return $project->apiKeys()->make($data);
    }

    public function revoke(ApiKey $apiKey): void
    {
        $apiKey->update(['revoked_at' => now()]);
    }

    public function delete(ApiKey $apiKey): void
    {
        $apiKey->delete();
    }

    public function findByHashedKey(string $hashedKey): ?ApiKey
    {
        return ApiKey::query()->where('hashed_key', $hashedKey)->first();
    }
}
