<?php

use App\Models\User;

test('a 404 renders the friendly inertia error page without debug mode', function () {
    config(['app.debug' => false]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/webhook-endpoints/does-not-exist');

    $response->assertNotFound();
    $response->assertInertia(fn ($page) => $page
        ->component('errors/error')
        ->where('status', 404)
    );
    $response->assertDontSee('Illuminate\\', false);
    $response->assertDontSee('vendor/laravel', false);
});

test('a validation error never leaks internal exception details', function () {
    config(['app.debug' => false]);

    $user = User::factory()->create();

    $response = $this->actingAs($user)->postJson('/projects', ['name' => '']);

    $response->assertStatus(422);
    $response->assertJsonMissingPath('exception');
    $response->assertJsonMissingPath('file');
    $response->assertJsonMissingPath('trace');
});

test('an unauthenticated api request receives a generic json error, not a stack trace', function () {
    config(['app.debug' => false]);

    $response = $this->getJson('/api/v1/webhook-endpoints');

    $response->assertStatus(401);
    $response->assertJsonMissingPath('exception');
    $response->assertJsonMissingPath('trace');
});
