<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApiKeys\DestroyApiKeyRequest;
use App\Http\Requests\ApiKeys\RevokeApiKeyRequest;
use App\Http\Requests\ApiKeys\StoreApiKeyRequest;
use App\Models\ApiKey;
use App\Models\Project;
use App\Services\ApiKeyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class ApiKeyController extends Controller
{
    public function __construct(private readonly ApiKeyService $apiKeys) {}

    public function store(StoreApiKeyRequest $request, Project $project): RedirectResponse
    {
        [, $plainKey] = $this->apiKeys->generate($request->user(), $project, $request->validated('name'));

        Inertia::flash('newApiKey', $plainKey);

        return to_route('projects.show', ['project' => $project, 'tab' => 'api-keys']);
    }

    public function revoke(RevokeApiKeyRequest $request, ApiKey $apiKey): RedirectResponse
    {
        $project = $apiKey->project;

        $this->apiKeys->revoke($request->user(), $apiKey);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'API key revoked.']);

        return to_route('projects.show', ['project' => $project, 'tab' => 'api-keys']);
    }

    public function destroy(DestroyApiKeyRequest $request, ApiKey $apiKey): RedirectResponse
    {
        $project = $apiKey->project;

        $this->apiKeys->delete($request->user(), $apiKey);

        Inertia::flash('toast', ['type' => 'success', 'message' => 'API key deleted.']);

        return to_route('projects.show', ['project' => $project, 'tab' => 'api-keys']);
    }
}
