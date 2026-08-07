<?php

namespace Database\Factories;

use App\Enums\WebhookEndpointMode;
use App\Enums\WebhookEndpointStatus;
use App\Models\Project;
use App\Models\WebhookEndpoint;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<WebhookEndpoint>
 */
class WebhookEndpointFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => fake()->company(),
            'mode' => WebhookEndpointMode::Relay,
            'destination_url' => fake()->url(),
            'ingest_token' => Str::random(40),
            'signing_secret' => Str::random(32),
            'status' => WebhookEndpointStatus::Active,
        ];
    }
}
