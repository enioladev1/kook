<?php

use App\Models\ApiKey;
use App\Models\AuditLog;
use App\Models\Project;
use App\Models\User;

test('a user can create an api key for their own project', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $response = $this->actingAs($user)->post("/projects/{$project->id}/api-keys", [
        'name' => 'CI pipeline',
    ]);

    $apiKey = ApiKey::first();

    $response->assertRedirect("/projects/{$project->id}?tab=api-keys");
    expect($apiKey)
        ->name->toBe('CI pipeline')
        ->project_id->toBe($project->id)
        ->key_prefix->toStartWith('kook_');

    $log = AuditLog::where('action', 'api_key.created')->first();
    expect($log)->not->toBeNull();
});

test('creating an api key requires a name', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->post("/projects/{$project->id}/api-keys", ['name' => ''])
        ->assertSessionHasErrors('name');

    expect(ApiKey::count())->toBe(0);
});

test('a user cannot create an api key on another users project', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();

    $this->actingAs($intruder)
        ->post("/projects/{$project->id}/api-keys", ['name' => 'Intruding key'])
        ->assertForbidden();

    expect(ApiKey::count())->toBe(0);
});

test('the plaintext key is never persisted, only its hash', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)->post("/projects/{$project->id}/api-keys", ['name' => 'Test key']);

    $apiKey = ApiKey::first();

    expect($apiKey->getAttributes())
        ->not->toHaveKey('key')
        ->hashed_key->toHaveLength(64);
});

test('a user can revoke their own api key', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $apiKey = ApiKey::factory()->for($project)->create();

    $this->actingAs($user)
        ->post("/api-keys/{$apiKey->id}/revoke")
        ->assertRedirect("/projects/{$project->id}?tab=api-keys");

    expect($apiKey->fresh()->revoked_at)->not->toBeNull();

    $log = AuditLog::where('action', 'api_key.revoked')->first();
    expect($log)->not->toBeNull();
});

test('a user cannot revoke another users api key', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    $apiKey = ApiKey::factory()->for($project)->create();

    $this->actingAs($intruder)
        ->post("/api-keys/{$apiKey->id}/revoke")
        ->assertForbidden();

    expect($apiKey->fresh()->revoked_at)->toBeNull();
});

test('a user can delete their own api key', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $apiKey = ApiKey::factory()->for($project)->create();

    $this->actingAs($user)
        ->delete("/api-keys/{$apiKey->id}")
        ->assertRedirect("/projects/{$project->id}?tab=api-keys");

    expect(ApiKey::find($apiKey->id))->toBeNull();

    $log = AuditLog::where('action', 'api_key.deleted')->first();
    expect($log)->not->toBeNull();
});

test('a user can delete a revoked api key', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $apiKey = ApiKey::factory()->for($project)->create(['revoked_at' => now()]);

    $this->actingAs($user)
        ->delete("/api-keys/{$apiKey->id}")
        ->assertRedirect("/projects/{$project->id}?tab=api-keys");

    expect(ApiKey::find($apiKey->id))->toBeNull();
});

test('a user cannot delete another users api key', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $project = Project::factory()->for($owner)->create();
    $apiKey = ApiKey::factory()->for($project)->create();

    $this->actingAs($intruder)
        ->delete("/api-keys/{$apiKey->id}")
        ->assertForbidden();

    expect(ApiKey::find($apiKey->id))->not->toBeNull();
});

test('after creating an api key the user lands back on the api keys tab with the new key flashed', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $redirect = $this->actingAs($user)->post("/projects/{$project->id}/api-keys", [
        'name' => 'CI pipeline',
    ]);

    $this->actingAs($user)
        ->get($redirect->headers->get('Location'))
        ->assertInertia(fn ($page) => $page
            ->component('projects/show')
            ->where('activeTab', 'api-keys')
            ->hasFlash('newApiKey')
        );
});

test('an api key response never exposes the hashed key', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    ApiKey::factory()->for($project)->create();

    $response = $this->actingAs($user)->get("/projects/{$project->id}");

    $response->assertInertia(fn ($page) => $page
        ->component('projects/show')
        ->missing('apiKeys.0.hashed_key')
    );
});
