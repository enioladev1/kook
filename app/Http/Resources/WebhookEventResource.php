<?php

namespace App\Http\Resources;

use App\Models\WebhookEvent;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WebhookEvent
 */
class WebhookEventResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'webhook_endpoint_id' => $this->webhook_endpoint_id,
            'idempotency_key' => $this->idempotency_key,
            'event_name' => $this->event_name,
            'headers' => $this->headers,
            'payload' => $this->payload,
            'signature_valid' => $this->signature_valid,
            'status' => $this->status,
            'received_at' => $this->received_at,
            'deliveries' => $this->when(
                $this->resource->relationLoaded('deliveries'),
                fn () => WebhookDeliveryResource::collection($this->deliveries),
            ),
            'webhookEndpoint' => $this->whenLoaded(
                'webhookEndpoint',
                fn () => [
                    'id' => $this->webhookEndpoint->id,
                    'name' => $this->webhookEndpoint->name,
                ],
            ),
        ];
    }
}
