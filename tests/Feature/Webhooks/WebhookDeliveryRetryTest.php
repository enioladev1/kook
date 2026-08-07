<?php

use App\Enums\WebhookDeliveryStatus;
use App\Enums\WebhookEventStatus;
use App\Exceptions\WebhookDeliveryFailedException;
use App\Jobs\ForwardWebhookDeliveryJob;
use App\Models\AuditLog;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Illuminate\Support\Facades\Http;

test('the job is configured with the documented retry and backoff schedule', function () {
    $job = new ForwardWebhookDeliveryJob('irrelevant');

    expect($job->tries)->toBe(6);
    expect($job->backoff())->toBe([30, 120, 600, 1800, 3600]);
});

test('a failed non-final delivery attempt is recorded as retrying', function () {
    Http::fake(['example.com/*' => Http::response('server error', 500)]);

    $endpoint = WebhookEndpoint::factory()->create(['destination_url' => 'https://example.com/hooks']);
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $endpoint->project_id,
        'status' => WebhookEventStatus::Success,
    ]);

    $job = new ForwardWebhookDeliveryJob($event->id);

    expect(fn () => app()->call([$job, 'handle']))
        ->toThrow(WebhookDeliveryFailedException::class);

    $delivery = WebhookDelivery::first();
    expect($delivery)
        ->attempt_number->toBe(1)
        ->status->toBe(WebhookDeliveryStatus::Retrying)
        ->http_status_code->toBe(500);
    expect($delivery->next_retry_at)->not->toBeNull();
});

test('the final delivery attempt is marked failed without scheduling another retry', function () {
    Http::fake(['example.com/*' => Http::response('server error', 500)]);

    $endpoint = WebhookEndpoint::factory()->create(['destination_url' => 'https://example.com/hooks']);
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $endpoint->project_id,
        'status' => WebhookEventStatus::Success,
    ]);

    $job = new ForwardWebhookDeliveryJob($event->id);
    $job->tries = 1;

    expect(fn () => app()->call([$job, 'handle']))
        ->toThrow(WebhookDeliveryFailedException::class);

    $delivery = WebhookDelivery::first();
    expect($delivery)
        ->status->toBe(WebhookDeliveryStatus::Failed)
        ->next_retry_at->toBeNull();
});

test('exhausting all retries writes an audit log via the failed hook', function () {
    $endpoint = WebhookEndpoint::factory()->create();
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $endpoint->project_id,
    ]);

    $job = new ForwardWebhookDeliveryJob($event->id);
    $job->failed(new WebhookDeliveryFailedException('destination unreachable'));

    $log = AuditLog::where('action', 'webhook_delivery.exhausted')->first();

    expect($log)
        ->not->toBeNull()
        ->project_id->toBe($endpoint->project_id)
        ->auditable_id->toBe($event->id);
});

test('a successful delivery does not throw and requires no retry', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $endpoint = WebhookEndpoint::factory()->create(['destination_url' => 'https://example.com/hooks']);
    $event = WebhookEvent::factory()->create([
        'webhook_endpoint_id' => $endpoint->id,
        'project_id' => $endpoint->project_id,
        'status' => WebhookEventStatus::Success,
    ]);

    $job = new ForwardWebhookDeliveryJob($event->id);

    app()->call([$job, 'handle']);

    $delivery = WebhookDelivery::first();
    expect($delivery)
        ->status->toBe(WebhookDeliveryStatus::Delivered)
        ->next_retry_at->toBeNull();
    expect($delivery->delivered_at)->not->toBeNull();
});
