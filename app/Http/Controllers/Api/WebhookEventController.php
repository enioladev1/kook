<?php

namespace App\Http\Controllers\Api;

use App\Exceptions\EventNotReplayableException;
use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookEventResource;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use App\Services\WebhookEventService;
use App\Services\Webhooks\ReplayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WebhookEventController extends Controller
{
    public function __construct(
        private readonly WebhookEventService $events,
        private readonly ReplayService $replay,
    ) {}

    public function index(Request $request, WebhookEndpoint $webhookEndpoint): AnonymousResourceCollection
    {
        abort_unless($webhookEndpoint->project_id === $request->apiKey()->project_id, 404);

        return WebhookEventResource::collection($this->events->listForEndpoint($webhookEndpoint));
    }

    public function show(Request $request, WebhookEvent $webhookEvent): WebhookEventResource
    {
        abort_unless($webhookEvent->project_id === $request->apiKey()->project_id, 404);

        $webhookEvent->load('deliveries');

        return new WebhookEventResource($webhookEvent);
    }

    public function replay(Request $request, WebhookEvent $webhookEvent): JsonResponse
    {
        abort_unless($webhookEvent->project_id === $request->apiKey()->project_id, 404);

        try {
            $this->replay->replayViaApiKey($request->apiKey(), $webhookEvent);
        } catch (EventNotReplayableException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['message' => 'Replay queued.'], 202);
    }
}
