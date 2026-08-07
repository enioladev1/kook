<?php

namespace App\Repositories;

use App\Models\Project;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Pagination\LengthAwarePaginator;

class WebhookEventRepository
{
    public function findByIdempotencyKey(WebhookEndpoint $endpoint, string $idempotencyKey): ?WebhookEvent
    {
        return $endpoint->events()
            ->where('idempotency_key', $idempotencyKey)
            ->first();
    }

    /**
     * @return LengthAwarePaginator<int, WebhookEvent>
     */
    public function paginateForEndpoint(WebhookEndpoint $endpoint, int $perPage = 25): LengthAwarePaginator
    {
        return $endpoint->events()
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return LengthAwarePaginator<int, WebhookEvent>
     */
    public function paginateForProject(Project $project, int $perPage = 25): LengthAwarePaginator
    {
        // Column-limited eager load: avoids decrypting/serializing the
        // endpoint's signing_secret on every row of a list view.
        return $project->events()
            ->with('webhookEndpoint:id,name')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(WebhookEndpoint $endpoint, array $data): WebhookEvent
    {
        /** @var WebhookEvent */
        return $endpoint->events()->create($data);
    }
}
