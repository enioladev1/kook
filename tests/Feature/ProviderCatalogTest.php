<?php

use App\Models\User;
use Database\Seeders\ProviderSeeder;

test('guests cannot view the provider catalog', function () {
    $this->get('/providers')->assertRedirect('/login');
});

test('an authenticated user sees all six seeded providers', function () {
    $this->seed(ProviderSeeder::class);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/providers')
        ->assertInertia(fn ($page) => $page
            ->component('providers/index')
            ->has('providers', 6)
        );
});
