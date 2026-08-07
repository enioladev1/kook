<?php

namespace Database\Factories;

use App\Models\ApiKey;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ApiKey>
 */
class ApiKeyFactory extends Factory
{
    public function definition(): array
    {
        $secret = Str::random(40);

        return [
            'project_id' => Project::factory(),
            'name' => fake()->company(),
            'key_prefix' => Str::substr($secret, 0, 8),
            'hashed_key' => hash('sha256', $secret),
        ];
    }
}
