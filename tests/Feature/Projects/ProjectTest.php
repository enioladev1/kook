<?php

use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\ApiKeyService;

test('guests cannot access projects', function () {
    $this->get('/projects')->assertRedirect('/login');
});

test('a user can create a project', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/projects', ['name' => 'Production API']);

    $project = Project::first();

    $response->assertRedirect("/projects/{$project->id}");
    expect($project)
        ->name->toBe('Production API')
        ->slug->toBe('production-api')
        ->user_id->toBe($user->id);
});

test('creating a project requires a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/projects', ['name' => ''])
        ->assertSessionHasErrors('name');

    expect(Project::count())->toBe(0);
});

test('duplicate project names for the same user get a unique slug', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/projects', ['name' => 'Billing']);
    $this->actingAs($user)->post('/projects', ['name' => 'Billing']);

    expect(Project::pluck('slug')->sort()->values()->all())
        ->toBe(['billing', 'billing-1']);
});

test('creating a project writes an audit log entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/projects', ['name' => 'Production']);

    $project = Project::first();

    $log = AuditLog::first();
    expect($log)
        ->action->toBe('project.created')
        ->user_id->toBe($user->id)
        ->project_id->toBe($project->id)
        ->auditable_type->toBe($project->getMorphClass())
        ->auditable_id->toBe($project->id);
});

test('a user only sees their own projects on the index page', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    $mine = Project::factory()->for($user)->create();
    Project::factory()->for($other)->create();

    $response = $this->actingAs($user)->get('/projects');

    $response->assertInertia(fn ($page) => $page
        ->component('projects/index')
        ->has('projects', 1)
        ->where('projects.0.id', $mine->id)
    );
});

test('the projects index shows each projects endpoint and api key counts', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    WebhookEndpoint::factory()->count(2)->for($project)->create();
    app(ApiKeyService::class)->generate($user, $project, 'Key one');
    app(ApiKeyService::class)->generate($user, $project, 'Key two');
    app(ApiKeyService::class)->generate($user, $project, 'Key three');

    $this->actingAs($user)
        ->get('/projects')
        ->assertInertia(fn ($page) => $page
            ->where('projects.0.webhook_endpoints_count', 2)
            ->where('projects.0.api_keys_count', 3)
        );
});

test('a user can view their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get("/projects/{$project->id}")
        ->assertInertia(fn ($page) => $page
            ->component('projects/show')
            ->where('project.id', $project->id)
            ->where('activeTab', 'endpoints')
        );
});

test('the project switcher lists all of a users projects', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $otherProject = Project::factory()->for($user)->create();
    $otherUsersProject = Project::factory()->create();

    $this->actingAs($user)
        ->get("/projects/{$project->id}")
        ->assertInertia(fn ($page) => $page
            ->has('projects', 2)
            ->where('projects.0.id', fn ($id) => in_array($id, [$project->id, $otherProject->id], true))
            ->missing('projects.2')
        );

    expect(Project::whereKey($otherUsersProject->id)->exists())->toBeTrue();
});

test('the active tab defaults to endpoints for an unrecognized tab query value', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get("/projects/{$project->id}?tab=not-a-real-tab")
        ->assertInertia(fn ($page) => $page->where('activeTab', 'endpoints'));
});

test('the active tab reflects a valid tab query value', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->get("/projects/{$project->id}?tab=settings")
        ->assertInertia(fn ($page) => $page->where('activeTab', 'settings'));
});

test('the endpoints tab shows each endpoints most recent event status', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
        'status' => 'success',
        'received_at' => now()->subDay(),
    ]);
    $latest = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
        'status' => 'failed',
        'received_at' => now(),
    ]);
    $delivery = WebhookDelivery::factory()->for($latest, 'event')->create([
        'attempt_number' => 1,
        'status' => 'retrying',
    ]);

    $this->actingAs($user)
        ->get("/projects/{$project->id}")
        ->assertInertia(fn ($page) => $page
            ->where('webhookEndpoints.0.latest_event.id', $latest->id)
            ->where('webhookEndpoints.0.latest_event.status', 'failed')
            ->where('webhookEndpoints.0.latest_event.latest_delivery.id', $delivery->id)
            ->where('webhookEndpoints.0.latest_event.latest_delivery.status', 'retrying')
        );
});

test('the events tab aggregates events across all of a projects endpoints', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $endpointA = WebhookEndpoint::factory()->for($project)->create();
    $endpointB = WebhookEndpoint::factory()->for($project)->create();
    WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpointA->id,
        'project_id' => $project->id,
    ]);
    WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpointB->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($user)
        ->get("/projects/{$project->id}?tab=events")
        ->assertInertia(fn ($page) => $page
            ->where('activeTab', 'events')
            ->has('events.data', 2)
            ->has('events.data.0.webhook_endpoint.name')
            ->missing('events.data.0.webhook_endpoint.signing_secret')
        );
});

test('the events tab does not include events from another users project', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    $endpoint = WebhookEndpoint::factory()->for($project)->create();
    WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $project->id,
    ]);

    $this->actingAs($intruder)
        ->get("/projects/{$project->id}?tab=events")
        ->assertNotFound();
});

test('a user cannot view another users project', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->get("/projects/{$project->id}")
        ->assertNotFound();
});

test('a user can update their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['name' => 'Old name']);

    $this->actingAs($user)
        ->put("/projects/{$project->id}", ['name' => 'New name'])
        ->assertRedirect("/projects/{$project->id}?tab=settings");

    expect($project->fresh()->name)->toBe('New name');
});

test('a user cannot update another users project', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create(['name' => 'Original']);

    $this->actingAs($intruder)
        ->put("/projects/{$project->id}", ['name' => 'Hacked'])
        ->assertForbidden();

    expect($project->fresh()->name)->toBe('Original');
});

test('a user can delete their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete("/projects/{$project->id}")
        ->assertRedirect('/projects');

    expect(Project::find($project->id))->toBeNull();
});

test('a user cannot delete another users project', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->delete("/projects/{$project->id}")
        ->assertForbidden();

    expect(Project::find($project->id))->not->toBeNull();
});
