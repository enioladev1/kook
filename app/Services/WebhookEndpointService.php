<?php

namespace App\Services;

use App\Enums\WebhookEndpointStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookEndpoint;
use App\Repositories\WebhookEndpointRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WebhookEndpointService
{
    public function __construct(
        private readonly WebhookEndpointRepository $endpoints,
        private readonly AuditLogService $auditLog,
    ) {}

    /**
     * @return Collection<int, WebhookEndpoint>
     */
    public function listForProject(Project $project): Collection
    {
        return $this->endpoints->forProject($project);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(User $user, Project $project, array $data): WebhookEndpoint
    {
        return DB::transaction(function () use ($user, $project, $data) {
            $endpoint = $this->endpoints->make($project, $data);

            // Never mass-assignable: these are system-generated, not client input.
            $endpoint->forceFill([
                'ingest_token' => $this->uniqueIngestToken(),
                'signing_secret' => Str::random(48),
                'status' => WebhookEndpointStatus::Active,
            ]);

            $endpoint->save();

            $this->auditLog->record($user, $project, 'webhook_endpoint.created', $endpoint, [
                'name' => $endpoint->name,
                'mode' => $endpoint->mode->value,
            ]);

            return $endpoint;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, WebhookEndpoint $endpoint, array $data): WebhookEndpoint
    {
        // Blank means "leave the current provider secret alone" - the field
        // is optional on update since the existing value is never displayed
        // back for the user to re-paste.
        if (array_key_exists('provider_secret', $data) && ! $data['provider_secret']) {
            unset($data['provider_secret']);
        }

        return DB::transaction(function () use ($user, $endpoint, $data) {
            $endpoint = $this->endpoints->update($endpoint, $data);

            $this->auditLog->record($user, $endpoint->project, 'webhook_endpoint.updated', $endpoint);

            return $endpoint;
        });
    }

    public function regenerateSigningSecret(User $user, WebhookEndpoint $endpoint): WebhookEndpoint
    {
        return DB::transaction(function () use ($user, $endpoint) {
            $endpoint->forceFill(['signing_secret' => Str::random(48)])->save();

            $this->auditLog->record($user, $endpoint->project, 'webhook_endpoint.signing_secret_regenerated', $endpoint);

            return $endpoint;
        });
    }

    public function delete(User $user, WebhookEndpoint $endpoint): void
    {
        DB::transaction(function () use ($user, $endpoint) {
            $this->auditLog->record($user, $endpoint->project, 'webhook_endpoint.deleted', null, [
                'webhook_endpoint_id' => $endpoint->id,
                'name' => $endpoint->name,
            ]);

            $this->endpoints->delete($endpoint);
        });
    }

    private function uniqueIngestToken(): string
    {
        do {
            $token = Str::random(40);
        } while ($this->endpoints->findByIngestToken($token) !== null);

        return $token;
    }
}
