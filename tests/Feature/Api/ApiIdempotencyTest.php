<?php

use App\Enums\WebhookEventStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\ApiKeyService;
use Illuminate\Support\Facades\Http;

test('replaying with the same idempotency key does not queue a second delivery attempt', function () {
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

    $headers = ['Authorization' => "Bearer {$plainKey}", 'Idempotency-Key' => 'replay-abc'];

    $this->postJson("/api/v1/events/{$event->id}/replay", [], $headers)->assertStatus(202);
    $this->postJson("/api/v1/events/{$event->id}/replay", [], $headers)->assertStatus(202);

    expect(WebhookDelivery::where('event_id', $event->id)->count())->toBe(1);
});

test('replaying without an idempotency key queues a new attempt every time', function () {
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

    $headers = ['Authorization' => "Bearer {$plainKey}"];

    $this->postJson("/api/v1/events/{$event->id}/replay", [], $headers)->assertStatus(202);
    $this->postJson("/api/v1/events/{$event->id}/replay", [], $headers)->assertStatus(202);

    expect(WebhookDelivery::where('event_id', $event->id)->count())->toBe(2);
});
