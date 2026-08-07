<?php

use App\Models\Project;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;

test('a user can see their own webhook endpoints events on its show page', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    WebhookEvent::factory()->count(3)->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->get("/webhook-endpoints/{$endpoint->id}")
        ->assertInertia(fn ($page) => $page
            ->component('webhook-endpoints/show')
            ->has('events.data', 3)
        );
});

test('a user cannot see another users webhook endpoint events', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();

    $this->actingAs($intruder)
        ->get("/webhook-endpoints/{$endpoint->id}")
        ->assertNotFound();
});

test('a user can view their own event with its deliveries', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);
    WebhookDelivery::factory()->for($event, 'event')->create(['attempt_number' => 1]);

    $this->actingAs($user)
        ->get("/events/{$event->id}")
        ->assertInertia(fn ($page) => $page
            ->component('events/show')
            ->where('event.id', $event->id)
            ->where('event.webhookEndpoint.id', $endpoint->id)
            ->where('event.webhookEndpoint.name', $endpoint->name)
            ->missing('event.data')
            ->has('deliveries', 1)
        );
});

test('delivery attempts are listed with the latest attempt first', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);
    WebhookDelivery::factory()->for($event, 'event')->create(['attempt_number' => 1]);
    WebhookDelivery::factory()->for($event, 'event')->create(['attempt_number' => 2]);
    WebhookDelivery::factory()->for($event, 'event')->create(['attempt_number' => 3]);

    $this->actingAs($user)
        ->get("/events/{$event->id}")
        ->assertInertia(fn ($page) => $page
            ->where('deliveries.0.attempt_number', 3)
            ->where('deliveries.1.attempt_number', 2)
            ->where('deliveries.2.attempt_number', 1)
        );
});

test('a user can view an event that has no delivery attempts yet', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->get("/events/{$event->id}")
        ->assertInertia(fn ($page) => $page
            ->component('events/show')
            ->where('event.id', $event->id)
            ->has('deliveries', 0)
        );
});

test('a user cannot view another users event', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($intruder)
        ->get("/events/{$event->id}")
        ->assertNotFound();
});

test('an event response never exposes the raw signing material of its endpoint', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);

    $response = $this->actingAs($user)->get("/events/{$event->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('events/show')
        ->missing('event.webhookEndpoint.signing_secret')
        ->missing('event.webhookEndpoint.provider_secret')
    );
});
