<?php

use App\Enums\WebhookEventStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\ApiKeyService;
use Illuminate\Support\Facades\Http;

function apiHeaders(string $plainKey): array
{
    return ['Authorization' => "Bearer {$plainKey}"];
}

test('a key lists only its own projects webhook endpoints', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $otherProject = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    WebhookEndpoint::factory()->for($project)->create();
    WebhookEndpoint::factory()->for($otherProject)->create();

    $response = $this->getJson('/api/v1/webhook-endpoints', apiHeaders($plainKey));

    $response->assertOk()->assertJsonCount(1, 'data');
});

test('a key can list events for its own endpoint', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    WebhookEvent::factory()->count(2)->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);

    $response = $this->getJson("/api/v1/webhook-endpoints/{$endpoint->id}/events", apiHeaders($plainKey));

    $response->assertOk()->assertJsonCount(2, 'data');
});

test('a key cannot list events for an endpoint outside its project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $otherProject = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    $foreignEndpoint = WebhookEndpoint::factory()->for($otherProject)->create();

    $this->getJson("/api/v1/webhook-endpoints/{$foreignEndpoint->id}/events", apiHeaders($plainKey))
        ->assertNotFound();
});

test('a key can view an event with its deliveries', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);
    WebhookDelivery::factory()->for($event, 'event')->create();

    $response = $this->getJson("/api/v1/events/{$event->id}", apiHeaders($plainKey));

    $response->assertOk()
        ->assertJsonPath('data.id', $event->id)
        ->assertJsonCount(1, 'data.deliveries');
});

test('a key can replay a successful event', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    $endpoint = WebhookEndpoint::factory()->for($project)->create([
        'destination_url' => 'https://example.com/hooks',
    ]);
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
        'status' => WebhookEventStatus::Success,
    ]);

    $this->postJson("/api/v1/events/{$event->id}/replay", [], apiHeaders($plainKey))
        ->assertStatus(202);

    expect(WebhookDelivery::where('event_id', $event->id)->count())->toBe(1);
});

test('a key cannot replay an event outside its project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $otherProject = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    $foreignEndpoint = WebhookEndpoint::factory()->for($otherProject)->create();
    $foreignEvent = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $foreignEndpoint->id,
        'project_id' => $otherProject->id,
        'status' => WebhookEventStatus::Success,
    ]);

    $this->postJson("/api/v1/events/{$foreignEvent->id}/replay", [], apiHeaders($plainKey))
        ->assertNotFound();
});
