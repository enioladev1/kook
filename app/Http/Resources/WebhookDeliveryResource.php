<?php

namespace App\Http\Resources;

use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin WebhookDelivery
 */
class WebhookDeliveryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'attempt_number' => $this->attempt_number,
            'status' => $this->status,
            'http_status_code' => $this->http_status_code,
            'error_message' => $this->error_message,
            'duration_ms' => $this->duration_ms,
            'next_retry_at' => $this->next_retry_at,
            'delivered_at' => $this->delivered_at,
        ];
    }
}
