<?php

use App\Enums\WebhookDeliveryStatus;
use App\Enums\WebhookEndpointMode;
use App\Enums\WebhookEndpointStatus;
use App\Enums\WebhookEventStatus;
use App\Models\Provider;
use App\Models\WebhookDelivery;
use App\Models\WebhookEndpoint;
use App\Models\WebhookEvent;
use Database\Seeders\ProviderSeeder;
use Illuminate\Support\Facades\Http;

test('an unknown ingest token is rejected', function () {
    $this->postJson('/webhooks/does-not-exist', ['foo' => 'bar'])
        ->assertNotFound();
});

test('a paused endpoint rejects incoming webhooks the same way as an unknown token', function () {
    $endpoint = WebhookEndpoint::factory()->create(['status' => WebhookEndpointStatus::Paused]);

    $this->postJson("/webhooks/{$endpoint->ingest_token}", ['foo' => 'bar'])
        ->assertNotFound();
});

test('a relay endpoint accepts, stores, and forwards a webhook', function () {
    Http::fake([
        'example.com/*' => Http::response(['ok' => true], 200),
    ]);

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
    ]);

    $response = $this->postJson("/webhooks/{$endpoint->ingest_token}", ['event' => 'order.created'], [
        'X-Custom-Header' => 'abc',
    ]);

    $response->assertStatus(202);

    $event = WebhookEvent::first();
    expect($event)
        ->webhook_endpoint_id->toBe($endpoint->id)
        ->status->toBe(WebhookEventStatus::Success)
        ->signature_valid->toBeNull()
        ->event_name->toBe('order.created');

    Http::assertSent(fn ($request) => $request->url() === 'https://example.com/hooks'
        && $request['event'] === 'order.created'
        && $request->hasHeader('X-Custom-Header', 'abc')
    );

    $delivery = WebhookDelivery::first();
    expect($delivery)
        ->status->toBe(WebhookDeliveryStatus::Delivered)
        ->http_status_code->toBe(200);
});

test('duplicate deliveries with the same idempotency key are not reprocessed', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
    ]);

    $headers = ['Idempotency-Key' => 'evt_123'];

    $this->postJson("/webhooks/{$endpoint->ingest_token}", ['event' => 'first'], $headers)
        ->assertStatus(202);

    $this->postJson("/webhooks/{$endpoint->ingest_token}", ['event' => 'first'], $headers)
        ->assertStatus(200);

    expect(WebhookEvent::count())->toBe(1);
    Http::assertSentCount(1);
});

test('a managed endpoint with a valid signature is verified and forwarded', function () {
    $this->seed(ProviderSeeder::class);
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $provider = Provider::query()->where('key', 'generic_hmac')->firstOrFail();
    $secret = 'super-secret';

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
        'mode' => WebhookEndpointMode::Managed,
        'provider_id' => $provider->id,
        'provider_secret' => $secret,
    ]);

    $body = json_encode(['event' => 'payment.success']);
    $signature = hash_hmac('sha256', $body, $secret);

    $response = $this->call('POST', "/webhooks/{$endpoint->ingest_token}", [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_WEBHOOK_SIGNATURE' => $signature,
    ], $body);

    $response->assertStatus(202);

    $event = WebhookEvent::first();
    expect($event)
        ->signature_valid->toBeTrue()
        ->status->toBe(WebhookEventStatus::Success);

    Http::assertSent(fn ($request) => $request->hasHeader('X-Kook-Signature'));
});

test('a managed endpoint with an invalid signature is rejected and never forwarded', function () {
    $this->seed(ProviderSeeder::class);
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $provider = Provider::query()->where('key', 'generic_hmac')->firstOrFail();

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
        'mode' => WebhookEndpointMode::Managed,
        'provider_id' => $provider->id,
        'provider_secret' => 'super-secret',
    ]);

    $body = json_encode(['event' => 'payment.success']);

    $response = $this->call('POST', "/webhooks/{$endpoint->ingest_token}", [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_WEBHOOK_SIGNATURE' => 'wrong-signature',
    ], $body);

    $response->assertStatus(202);

    $event = WebhookEvent::first();
    expect($event)
        ->signature_valid->toBeFalse()
        ->status->toBe(WebhookEventStatus::Failed);

    Http::assertNothingSent();
});

test('a stripe managed endpoint verifies the signed event end to end', function () {
    $this->seed(ProviderSeeder::class);
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $provider = Provider::query()->where('key', 'stripe')->firstOrFail();
    $secret = 'whsec_test';

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
        'mode' => WebhookEndpointMode::Managed,
        'provider_id' => $provider->id,
        'provider_secret' => $secret,
    ]);

    $body = json_encode(['id' => 'evt_1', 'type' => 'payment_intent.succeeded']);
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);

    $response = $this->call('POST', "/webhooks/{$endpoint->ingest_token}", [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ], $body);

    $response->assertStatus(202);

    $event = WebhookEvent::first();
    expect($event)
        ->signature_valid->toBeTrue()
        ->status->toBe(WebhookEventStatus::Success)
        ->idempotency_key->toBe('evt_1');

    Http::assertSent(fn ($request) => $request->hasHeader('X-Kook-Signature'));
});

test('a flutterwave managed endpoint verifies the hmac-signed event end to end', function () {
    $this->seed(ProviderSeeder::class);
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $provider = Provider::query()->where('key', 'flutterwave')->firstOrFail();
    $secret = 'my-secret-hash';

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
        'mode' => WebhookEndpointMode::Managed,
        'provider_id' => $provider->id,
        'provider_secret' => $secret,
    ]);

    $body = json_encode(['event' => 'charge.completed']);
    $signature = base64_encode(hash_hmac('sha256', $body, $secret, true));

    $response = $this->call('POST', "/webhooks/{$endpoint->ingest_token}", [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_FLUTTERWAVE_SIGNATURE' => $signature,
    ], $body);

    $response->assertStatus(202);

    $event = WebhookEvent::first();
    expect($event)
        ->signature_valid->toBeTrue()
        ->status->toBe(WebhookEventStatus::Success);

    Http::assertSent(fn ($request) => $request->hasHeader('X-Kook-Signature'));
});

test('a flutterwave managed endpoint accepts the legacy plain secret hash header', function () {
    $this->seed(ProviderSeeder::class);
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $provider = Provider::query()->where('key', 'flutterwave')->firstOrFail();
    $secret = 'my-secret-hash';

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
        'mode' => WebhookEndpointMode::Managed,
        'provider_id' => $provider->id,
        'provider_secret' => $secret,
    ]);

    $body = json_encode(['event' => 'charge.completed']);

    $response = $this->call('POST', "/webhooks/{$endpoint->ingest_token}", [], [], [], [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_VERIF_HASH' => $secret,
    ], $body);

    $response->assertStatus(202);

    expect(WebhookEvent::first())
        ->signature_valid->toBeTrue()
        ->status->toBe(WebhookEventStatus::Success);
});

test('the event name is resolved from a type field when no event field is present', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
    ]);

    $this->postJson("/webhooks/{$endpoint->ingest_token}", ['type' => 'charge.success'])
        ->assertStatus(202);

    expect(WebhookEvent::first())->event_name->toBe('charge.success');
});

test('the event name is null when the payload has no recognizable event field', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $endpoint = WebhookEndpoint::factory()->create([
        'status' => WebhookEndpointStatus::Active,
        'destination_url' => 'https://example.com/hooks',
    ]);

    $this->postJson("/webhooks/{$endpoint->ingest_token}", ['foo' => 'bar'])
        ->assertStatus(202);

    expect(WebhookEvent::first())->event_name->toBeNull();
});

test('ingestion is rate limited per endpoint', function () {
    $endpoint = WebhookEndpoint::factory()->create(['status' => WebhookEndpointStatus::Active]);

    Http::fake(['*' => Http::response('ok', 200)]);

    for ($i = 0; $i < 300; $i++) {
        $this->postJson("/webhooks/{$endpoint->ingest_token}", ['n' => $i]);
    }

    $this->postJson("/webhooks/{$endpoint->ingest_token}", ['n' => 'over-limit'])
        ->assertStatus(429);
});
