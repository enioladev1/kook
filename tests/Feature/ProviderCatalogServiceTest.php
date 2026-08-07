<?php

use App\Models\Provider;
use App\Services\ProviderCatalogService;
use Database\Seeders\ProviderSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

test('the active provider list is cached', function () {
    $this->seed(ProviderSeeder::class);
    $service = app(ProviderCatalogService::class);

    $service->active();

    DB::enableQueryLog();
    $service->active();

    expect(DB::getQueryLog())->toBeEmpty();
});

test('generic hmac is always listed first, ahead of the named providers', function () {
    $this->seed(ProviderSeeder::class);
    $service = app(ProviderCatalogService::class);

    $providers = $service->active();

    expect($providers[0]['key'])->toBe('generic_hmac');

    $rest = array_slice(array_column($providers, 'name'), 1);
    expect($rest)->toBe(collect($rest)->sort()->values()->all());
});

test('forgetting the cache causes the next call to hit the database again', function () {
    $this->seed(ProviderSeeder::class);
    $service = app(ProviderCatalogService::class);

    $service->active();
    $service->forget();

    Provider::query()->where('key', 'shopify')->update(['is_active' => false]);

    expect($service->active())->toHaveCount(5);
});

afterEach(function () {
    Cache::flush();
});
