<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Honors an optional "Idempotency-Key" header on mutating actions (replay,
 * etc.) so a retried request with the same key returns the original result
 * instead of repeating the side effect. A short-lived lock prevents two
 * concurrent requests with the same key from both executing.
 */
class EnsureIdempotentRequest
{
    private const CACHE_TTL_HOURS = 24;

    private const LOCK_TIMEOUT_SECONDS = 10;

    public function handle(Request $request, Closure $next): Response
    {
        $key = $request->header('Idempotency-Key');

        if (! is_string($key) || $key === '') {
            return $next($request);
        }

        $cacheKey = 'idempotency:'.sha1($request->method().'|'.$request->path().'|'.$key);

        $cached = Cache::get($cacheKey);

        if (is_array($cached)) {
            return response($cached['content'], $cached['status'])
                ->header('Content-Type', $cached['content_type']);
        }

        $lock = Cache::lock($cacheKey.':lock', self::LOCK_TIMEOUT_SECONDS);

        return $lock->block(self::LOCK_TIMEOUT_SECONDS, function () use ($cacheKey, $next, $request) {
            $cached = Cache::get($cacheKey);

            if (is_array($cached)) {
                return response($cached['content'], $cached['status'])
                    ->header('Content-Type', $cached['content_type']);
            }

            $response = $next($request);

            if ($response->isSuccessful() || $response->isClientError()) {
                Cache::put($cacheKey, [
                    'status' => $response->getStatusCode(),
                    'content' => $response->getContent(),
                    'content_type' => $response->headers->get('Content-Type', 'application/json'),
                ], now()->addHours(self::CACHE_TTL_HOURS));
            }

            return $response;
        });
    }
}
