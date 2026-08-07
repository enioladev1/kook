<?php

namespace Database\Factories;

use App\Enums\WebhookEventStatus;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WebhookEvent>
 */
class WebhookEventFactory extends Factory
{
    public function definition(): array
    {
        return [
            'webhook_endpoint_id' => WebhookEndpoint::factory(),
            'headers' => ['content-type' => 'application/json'],
            'payload' => ['event' => 'test.event'],
            'raw_body' => json_encode(['event' => 'test.event']),
            'status' => WebhookEventStatus::Pending,
            'received_at' => now(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (WebhookEvent $event) {
            $event->project_id ??= WebhookEndpoint::find($event->webhook_endpoint_id)?->project_id;
        });
    }
}
