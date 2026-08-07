<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\WebhookEndpointResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WebhookEndpointController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $project = $request->apiKey()->project;

        return WebhookEndpointResource::collection(
            $project->webhookEndpoints()->latest()->get()
        );
    }
}
