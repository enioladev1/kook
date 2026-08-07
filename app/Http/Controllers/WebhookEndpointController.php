<?php

namespace App\Http\Controllers;

use App\Http\Requests\WebhookEndpoints\DestroyWebhookEndpointRequest;
use App\Http\Requests\WebhookEndpoints\RegenerateSigningSecretRequest;
use App\Http\Requests\WebhookEndpoints\StoreWebhookEndpointRequest;
use App\Http\Requests\WebhookEndpoints\UpdateWebhookEndpointRequest;
use App\Models\Project;
use App\Models\WebhookEndpoint;
use App\Services\ProjectService;
use App\Services\WebhookEndpointService;
use App\Services\WebhookEventService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebhookEndpointController extends Controller
{
    public function __construct(
        private readonly WebhookEndpointService $endpoints,
        private readonly WebhookEventService $events,
        private readonly ProjectService $projects,
    ) {}

    public function show(Request $request, WebhookEndpoint $webhookEndpoint): Response
    {
        abort_unless($request->user()->can('view', $webhookEndpoint), 404);

        return Inertia::render('webhook-endpoints/show', [
            'project' => $webhookEndpoint->project,
            'projects' => $this->projects->listForUser($request->user()),
            'webhookEndpoint' => $webhookEndpoint->load([
                'provider',
                'latestEvent:id,webhook_endpoint_id,status,received_at',
                'latestEvent.latestDelivery:id,event_id,status,attempt_number',
            ]),
            'events' => $this->events->listForEndpoint($webhookEndpoint),
        ]);
    }

    public function store(StoreWebhookEndpointRequest $request, Project $project): RedirectResponse
    {
        $endpoint = $this->endpoints->create($request->user(), $project, $request->validated());

        return to_route('webhook-endpoints.show', $endpoint);
    }

    public function update(UpdateWebhookEndpointRequest $request, WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $this->endpoints->update($request->user(), $webhookEndpoint, $request->validated());

        return to_route('webhook-endpoints.show', $webhookEndpoint);
    }

    public function destroy(DestroyWebhookEndpointRequest $request, WebhookEndpoint $webhookEndpoint): RedirectResponse
    {
        $project = $webhookEndpoint->project;

        $this->endpoints->delete($request->user(), $webhookEndpoint);

        return to_route('projects.show', $project);
    }

    public function regenerateSigningSecret(
        RegenerateSigningSecretRequest $request,
        WebhookEndpoint $webhookEndpoint,
    ): RedirectResponse {
        $this->endpoints->regenerateSigningSecret($request->user(), $webhookEndpoint);

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Signing secret regenerated. Update it on your receiving server.',
        ]);

        return to_route('webhook-endpoints.show', $webhookEndpoint);
    }
}
