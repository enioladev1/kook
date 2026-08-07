<?php

namespace App\Services\Webhooks;

use App\Enums\WebhookEventStatus;
use App\Exceptions\EventNotReplayableException;
use App\Jobs\ForwardWebhookDeliveryJob;
use App\Models\ApiKey;
use App\Models\User;
use App\Models\WebhookEvent;
use App\Services\AuditLogService;
use Illuminate\Support\Facades\DB;

class ReplayService
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    /**
     * @throws EventNotReplayableException
     */
    public function replay(User $user, WebhookEvent $event): void
    {
        $this->dispatchReplay($event, function () use ($user, $event) {
            $this->auditLog->record($user, $event->project, 'webhook_event.replayed', $event, [
                'webhook_endpoint_id' => $event->webhook_endpoint_id,
            ]);
        });
    }

    /**
     * @throws EventNotReplayableException
     */
    public function replayViaApiKey(ApiKey $apiKey, WebhookEvent $event): void
    {
        $this->dispatchReplay($event, function () use ($apiKey, $event) {
            $this->auditLog->record(null, $event->project, 'webhook_event.replayed', $event, [
                'webhook_endpoint_id' => $event->webhook_endpoint_id,
                'api_key_id' => $apiKey->id,
                'api_key_name' => $apiKey->name,
            ]);
        });
    }

    /**
     * @throws EventNotReplayableException
     */
    private function dispatchReplay(WebhookEvent $event, callable $recordAudit): void
    {
        if ($event->status !== WebhookEventStatus::Success) {
            throw new EventNotReplayableException(
                'Only events that were successfully verified can be replayed.'
            );
        }

        DB::transaction(function () use ($event, $recordAudit) {
            $existingAttempts = $event->deliveries()->count();

            $recordAudit();

            ForwardWebhookDeliveryJob::dispatch($event->id, $existingAttempts)->afterCommit();
        });
    }
}
