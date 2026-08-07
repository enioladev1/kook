<?php

namespace App\Services;

use App\Models\Project;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Repositories\WebhookDeliveryRepository;
use App\Repositories\WebhookEventRepository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class WebhookEventService
{
    public function __construct(
        private readonly WebhookEventRepository $events,
        private readonly WebhookDeliveryRepository $deliveries,
    ) {}

    /**
     * @return LengthAwarePaginator<int, WebhookEvent>
     */
    public function listForEndpoint(WebhookEndpoint $endpoint): LengthAwarePaginator
    {
        return $this->events->paginateForEndpoint($endpoint);
    }

    /**
     * @return LengthAwarePaginator<int, WebhookEvent>
     */
    public function listForProject(Project $project): LengthAwarePaginator
    {
        return $this->events->paginateForProject($project);
    }

    /**
     * @return Collection<int, WebhookDelivery>
     */
    public function deliveriesFor(WebhookEvent $event): Collection
    {
        return $this->deliveries->forEvent($event);
    }
}
