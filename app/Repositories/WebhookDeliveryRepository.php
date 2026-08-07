<?php

namespace App\Repositories;

use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Collection;

class WebhookDeliveryRepository
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(WebhookEvent $event, array $data): WebhookDelivery
    {
        /** @var WebhookDelivery */
        return $event->deliveries()->create($data);
    }

    /**
     * @return Collection<int, WebhookDelivery>
     */
    public function forEvent(WebhookEvent $event): Collection
    {
        /** @var Collection<int, WebhookDelivery> */
        return $event->deliveries()->orderByDesc('attempt_number')->get();
    }
}
