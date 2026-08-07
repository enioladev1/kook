<?php

namespace App\Services;

use App\Models\ApiKey;
use App\Models\Project;
use App\Models\User;
use App\Repositories\ApiKeyRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ApiKeyService
{
    private const KEY_PREFIX = 'kook_';

    public function __construct(
        private readonly ApiKeyRepository $apiKeys,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @return Collection<int, ApiKey>
     */
    public function listForProject(Project $project): Collection
    {
        return $this->apiKeys->forProject($project);
    }

    /**
     * @return array{0: ApiKey, 1: string} The persisted key and its one-time plaintext value.
     */
    public function generate(User $user, Project $project, string $name): array
    {
        return DB::transaction(function () use ($user, $project, $name) {
            $plainKey = self::KEY_PREFIX.Str::random(40);

            $apiKey = $this->apiKeys->make($project, ['name' => $name]);
            $apiKey->forceFill([
                'key_prefix' => Str::substr($plainKey, 0, 12),
                'hashed_key' => hash('sha256', $plainKey),
            ]);
            $apiKey->save();

            $this->auditLog->record($user, $project, 'api_key.created', $apiKey, [
                'name' => $apiKey->name,
            ]);

            return [$apiKey, $plainKey];
        });
    }

    public function revoke(User $user, ApiKey $apiKey): void
    {
        DB::transaction(function () use ($user, $apiKey) {
            $this->apiKeys->revoke($apiKey);

            $this->auditLog->record($user, $apiKey->project, 'api_key.revoked', $apiKey, [
                'name' => $apiKey->name,
            ]);
        });
    }

    public function delete(User $user, ApiKey $apiKey): void
    {
        DB::transaction(function () use ($user, $apiKey) {
            $project = $apiKey->project;

            $this->auditLog->record($user, $project, 'api_key.deleted', null, [
                'api_key_id' => $apiKey->id,
                'name' => $apiKey->name,
            ]);

            $this->apiKeys->delete($apiKey);
        });
    }
}
