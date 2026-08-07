<?php

namespace App\Http\Middleware;

use App\Repositories\ApiKeyRepository;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiKey
{
    public function __construct(private readonly ApiKeyRepository $apiKeys) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $apiKey = $this->apiKeys->findByHashedKey(hash('sha256', $token));

        if ($apiKey === null || $apiKey->isRevoked() || $apiKey->isExpired()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $apiKey->forceFill(['last_used_at' => now()])->saveQuietly();

        $request->attributes->set('api_key', $apiKey);

        return $next($request);
    }
}
