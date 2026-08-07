<?php

namespace App\Services;

use App\Enums\ProviderKey;
use App\Models\Provider;
use Illuminate\Support\Facades\Cache;

class ProviderCatalogService
{
    private const CACHE_KEY = 'providers.active';

    private const CACHE_TTL_SECONDS = 3600;

    /**
     * Cached as a plain array, not a Collection of models - the app's
     * cache.serializable_classes = false hardening rejects unserializing
     * arbitrary objects out of Redis, so only array/scalar data may be cached.
     *
     * @return array<int, array<string, mixed>>
     */
    public function active(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL_SECONDS,
            function () {
                $providers = Provider::query()->where('is_active', true)->orderBy('name')->get()->toArray();

                usort(
                    $providers,
                    fn (array $a, array $b) => ($b['key'] === ProviderKey::GenericHmac->value)
                        <=> ($a['key'] === ProviderKey::GenericHmac->value),
                );

                return $providers;
            },
        );
    }

    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
