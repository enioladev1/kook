<?php

use App\Models\Project;
use App\Models\User;
use App\Services\ApiKeyService;

test('a request without a bearer token is rejected', function () {
    $this->getJson('/api/v1/webhook-endpoints')->assertStatus(401);
});

test('a request with an unknown token is rejected', function () {
    $this->getJson('/api/v1/webhook-endpoints', [
        'Authorization' => 'Bearer not-a-real-key',
    ])->assertStatus(401);
});

test('a request with a revoked key is rejected', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [$apiKey, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');
    $apiKey->update(['revoked_at' => now()]);

    $this->getJson('/api/v1/webhook-endpoints', [
        'Authorization' => "Bearer {$plainKey}",
    ])->assertStatus(401);
});

test('a request with an expired key is rejected', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [$apiKey, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');
    $apiKey->update(['expires_at' => now()->subDay()]);

    $this->getJson('/api/v1/webhook-endpoints', [
        'Authorization' => "Bearer {$plainKey}",
    ])->assertStatus(401);
});

test('a valid key authenticates and updates last_used_at', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [$apiKey, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    expect($apiKey->last_used_at)->toBeNull();

    $this->getJson('/api/v1/webhook-endpoints', [
        'Authorization' => "Bearer {$plainKey}",
    ])->assertOk();

    expect($apiKey->fresh()->last_used_at)->not->toBeNull();
});

test('api requests are rate limited per key', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    [, $plainKey] = app(ApiKeyService::class)->generate($user, $project, 'Test key');

    for ($i = 0; $i < 120; $i++) {
        $this->getJson('/api/v1/webhook-endpoints', ['Authorization' => "Bearer {$plainKey}"]);
    }

    $this->getJson('/api/v1/webhook-endpoints', ['Authorization' => "Bearer {$plainKey}"])
        ->assertStatus(429);
});
