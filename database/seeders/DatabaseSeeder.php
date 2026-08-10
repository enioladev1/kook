<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // No user is seeded on purpose - Kook only ever has one admin
        // account, and a fresh install with zero users lands on the
        // registration form instead of login (see
        // FortifyServiceProvider::configureViews()).
        $this->call(ProviderSeeder::class);
    }
}
