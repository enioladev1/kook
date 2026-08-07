<?php

namespace App\Jobs;

use App\Actions\ForwardWebhookAction;
use App\Enums\WebhookDeliveryStatus;
use App\Exceptions\WebhookDeliveryFailedException;
use App\Models\WebhookEvent;
use App\Repositories\WebhookDeliveryRepository;
use App\Services\AuditLogService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ForwardWebhookDeliveryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 6;

    /**
     * @param  int  $attemptOffset  Number of delivery attempts already recorded
     *                              for this event (nonzero on a replay), so the
     *                              stored attempt_number continues the sequence
     *                              instead of restarting at 1.
     */
    public function __construct(
        public readonly string $webhookEventId,
        public readonly int $attemptOffset = 0,
    ) {}

    /**
     * Delay in seconds before each retry: 30s, 2m, 10m, 30m, 1h.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [30, 120, 600, 1800, 3600];
    }

    public function handle(ForwardWebhookAction $forward, WebhookDeliveryRepository $deliveries): void
    {
        $event = WebhookEvent::with('webhookEndpoint')->find($this->webhookEventId);

        if ($event === null) {
            return;
        }

        $queueAttempt = $this->attempts();
        $isFinalAttempt = $queueAttempt >= $this->tries;

        $result = $forward->execute($event);

        $deliveries->create($event, [
            'attempt_number' => $this->attemptOffset + $queueAttempt,
            'status' => match (true) {
                $result->successful => WebhookDeliveryStatus::Delivered,
                $isFinalAttempt => WebhookDeliveryStatus::Failed,
                default => WebhookDeliveryStatus::Retrying,
            },
            'http_status_code' => $result->statusCode,
            'response_body' => $result->responseBody,
            'error_message' => $result->errorMessage,
            'duration_ms' => $result->durationMs,
            'delivered_at' => $result->successful ? now() : null,
            'next_retry_at' => $result->successful || $isFinalAttempt
                ? null
                : now()->addSeconds($this->nextBackoffSeconds($queueAttempt)),
        ]);

        if (! $result->successful) {
            throw new WebhookDeliveryFailedException($result->errorMessage ?? 'Webhook delivery failed.');
        }
    }

    public function failed(?Throwable $exception): void
    {
        $event = WebhookEvent::with('webhookEndpoint.project')->find($this->webhookEventId);

        if ($event === null) {
            return;
        }

        app(AuditLogService::class)->record(
            null,
            $event->webhookEndpoint->project,
            'webhook_delivery.exhausted',
            $event,
            ['webhook_endpoint_id' => $event->webhookEndpoint->id],
        );
    }

    private function nextBackoffSeconds(int $attemptNumber): int
    {
        $backoff = $this->backoff();

        return $backoff[$attemptNumber - 1] ?? $backoff[count($backoff) - 1];
    }
}
