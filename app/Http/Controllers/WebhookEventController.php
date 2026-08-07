<?php

namespace App\Http\Controllers;

use App\Exceptions\EventNotReplayableException;
use App\Http\Requests\WebhookEvents\ReplayWebhookEventRequest;
use App\Http\Resources\WebhookDeliveryResource;
use App\Http\Resources\WebhookEventResource;
use App\Models\WebhookEvent;
use App\Services\WebhookEventService;
use App\Services\Webhooks\ReplayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WebhookEventController extends Controller
{
    public function __construct(
        private readonly WebhookEventService $events,
        private readonly ReplayService $replay,
    ) {}

    public function show(Request $request, WebhookEvent $webhookEvent): Response
    {
        abort_unless($request->user()->can('view', $webhookEvent), 404);

        return Inertia::render('events/show', [
            'event' => (new WebhookEventResource($webhookEvent->load('webhookEndpoint')))->resolve($request),
            'deliveries' => WebhookDeliveryResource::collection($this->events->deliveriesFor($webhookEvent))->toArray($request),
        ]);
    }

    public function replay(ReplayWebhookEventRequest $request, WebhookEvent $webhookEvent): RedirectResponse
    {
        try {
            $this->replay->replay($request->user(), $webhookEvent);
        } catch (EventNotReplayableException $e) {
            Inertia::flash('toast', ['type' => 'error', 'message' => $e->getMessage()]);

            return back();
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Replay queued.']);

        return back();
    }
}
