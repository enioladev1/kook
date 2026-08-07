<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Collection;

class WebhookEndpointRepository
{
    /**
     * @return Collection<int, WebhookEndpoint>
     */
    public function forProject(Project $project): Collection
    {
        /** @var Collection<int, WebhookEndpoint> */
        return $project->webhookEndpoints()
            ->with([
                'latestEvent:id,webhook_endpoint_id,status,received_at',
                'latestEvent.latestDelivery:id,event_id,status,attempt_number',
            ])
            ->latest()
            ->get();
    }

    /**
     * Builds an unsaved instance scoped to the project via mass-assignable
     * fields only, leaving system-generated attributes for the caller to
     * force-fill before saving.
     *
     * @param  array<string, mixed>  $data
     */
    public function make(Project $project, array $data): WebhookEndpoint
    {
        /** @var WebhookEndpoint */
        return $project->webhookEndpoints()->make($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(WebhookEndpoint $endpoint, array $data): WebhookEndpoint
    {
        $endpoint->update($data);

        return $endpoint;
    }

    public function delete(WebhookEndpoint $endpoint): void
    {
        $endpoint->delete();
    }

    public function findByIngestToken(string $ingestToken): ?WebhookEndpoint
    {
        return WebhookEndpoint::query()
            ->where('ingest_token', $ingestToken)
            ->first();
    }
}
