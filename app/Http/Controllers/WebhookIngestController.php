<?php

namespace App\Http\Controllers;

use App\Exceptions\WebhookEndpointNotFoundException;
use App\Services\Webhooks\WebhookIngestService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookIngestController extends Controller
{
    public function __construct(private readonly WebhookIngestService $ingest) {}

    public function store(Request $request, string $ingestToken): JsonResponse
    {
        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $headers[strtolower($name)] = implode(', ', $values);
        }

        try {
            $event = $this->ingest->ingest($ingestToken, $request->getContent(), $headers);
        } catch (WebhookEndpointNotFoundException) {
            return response()->json(['message' => 'Not found.'], 404);
        }

        $status = $event->wasRecentlyCreated ? 202 : 200;

        return response()->json(['received' => true], $status);
    }
}
