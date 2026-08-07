<?php

namespace App\Http\Requests\WebhookEvents;

use App\Models\WebhookEvent;
use Illuminate\Foundation\Http\FormRequest;

class ReplayWebhookEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var WebhookEvent $webhookEvent */
        $webhookEvent = $this->route('webhook_event');

        return $this->user()?->can('replay', $webhookEvent) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
