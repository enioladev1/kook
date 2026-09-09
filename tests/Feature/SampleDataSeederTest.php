<?php

use App\Enums\WebhookEndpointStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEvent;
use Database\Seeders\ProviderSeeder;
use Database\Seeders\SampleDataSeeder;

test('it seeds a sample project with endpoints, events, and deliveries for the first user', function () {
    $user = User::factory()->create();

    $this->seed(ProviderSeeder::class);
    $this->seed(SampleDataSeeder::class);

    $project = Project::where('user_id', $user->id)->where('slug', 'acme-payments')->first();

    expect($project)->not->toBeNull();
    expect($project->webhookEndpoints()->count())->toBe(3);
    expect($project->webhookEndpoints()->where('status', WebhookEndpointStatus::Paused)->count())->toBe(1);

    $events = WebhookEvent::where('project_id', $project->id)->get();
    expect($events)->toHaveCount(6);
    expect(WebhookDelivery::whereIn('event_id', $events->pluck('id'))->count())->toBe(7);
});

test('every seeded event keeps a valid raw body that carries the same data as the parsed payload', function () {
    User::factory()->create();
    $this->seed(ProviderSeeder::class);
    $this->seed(SampleDataSeeder::class);

    // raw_body is the source of truth for key order; payload (jsonb) may reorder,
    // so compare canonically rather than strictly.
    WebhookEvent::query()->each(function (WebhookEvent $event) {
        $decoded = json_decode($event->raw_body, true);
        expect(json_last_error())->toBe(JSON_ERROR_NONE);
        expect($decoded)->toEqualCanonicalizing($event->payload);
    });
});

test('it is idempotent and does not duplicate data on a second run', function () {
    User::factory()->create();
    $this->seed(ProviderSeeder::class);
    $this->seed(SampleDataSeeder::class);
    $this->seed(SampleDataSeeder::class);

    expect(Project::where('slug', 'acme-payments')->count())->toBe(1);
    expect(WebhookEvent::count())->toBe(6);
});

test('it skips cleanly when there is no user', function () {
    $this->seed(ProviderSeeder::class);
    $this->seed(SampleDataSeeder::class);

    expect(Project::count())->toBe(0);
});
