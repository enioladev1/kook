<?php

use App\Services\Webhooks\Verifiers\FlutterwaveVerifier;
use App\Services\Webhooks\Verifiers\GenericHmacVerifier;
use App\Services\Webhooks\Verifiers\GitHubVerifier;
use App\Services\Webhooks\Verifiers\PaystackVerifier;
use App\Services\Webhooks\Verifiers\ShopifyVerifier;
use App\Services\Webhooks\Verifiers\StripeVerifier;

// -- Stripe -----------------------------------------------------------------

test('stripe verifier accepts a correctly signed payload', function () {
    $secret = 'whsec_test';
    $body = '{"id":"evt_1"}';
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);

    $verifier = new StripeVerifier;

    expect($verifier->verify($body, [
        'stripe-signature' => "t={$timestamp},v1={$signature}",
    ], $secret))->toBeTrue();
});

test('stripe verifier rejects a tampered payload', function () {
    $secret = 'whsec_test';
    $timestamp = time();
    $signature = hash_hmac('sha256', "{$timestamp}.".'{"id":"evt_1"}', $secret);

    $verifier = new StripeVerifier;

    expect($verifier->verify('{"id":"evt_2"}', [
        'stripe-signature' => "t={$timestamp},v1={$signature}",
    ], $secret))->toBeFalse();
});

test('stripe verifier rejects an expired timestamp', function () {
    $secret = 'whsec_test';
    $body = '{"id":"evt_1"}';
    $timestamp = time() - 600;
    $signature = hash_hmac('sha256', "{$timestamp}.{$body}", $secret);

    $verifier = new StripeVerifier;

    expect($verifier->verify($body, [
        'stripe-signature' => "t={$timestamp},v1={$signature}",
    ], $secret))->toBeFalse();
});

test('stripe verifier rejects a missing header', function () {
    expect((new StripeVerifier)->verify('{}', [], 'secret'))->toBeFalse();
});

// -- GitHub -------------------------------------------------------------

test('github verifier accepts a correctly signed payload', function () {
    $secret = 'gh_secret';
    $body = '{"action":"opened"}';
    $signature = 'sha256='.hash_hmac('sha256', $body, $secret);

    expect((new GitHubVerifier)->verify($body, [
        'x-hub-signature-256' => $signature,
    ], $secret))->toBeTrue();
});

test('github verifier rejects an incorrect secret', function () {
    $body = '{"action":"opened"}';
    $signature = 'sha256='.hash_hmac('sha256', $body, 'right-secret');

    expect((new GitHubVerifier)->verify($body, [
        'x-hub-signature-256' => $signature,
    ], 'wrong-secret'))->toBeFalse();
});

test('github verifier rejects a header missing the sha256 prefix', function () {
    $body = '{"action":"opened"}';
    $signature = hash_hmac('sha256', $body, 'secret');

    expect((new GitHubVerifier)->verify($body, [
        'x-hub-signature-256' => $signature,
    ], 'secret'))->toBeFalse();
});

// -- Shopify ------------------------------------------------------------

test('shopify verifier accepts a correctly signed payload', function () {
    $secret = 'shpss_secret';
    $body = '{"id":123}';
    $signature = base64_encode(hash_hmac('sha256', $body, $secret, true));

    expect((new ShopifyVerifier)->verify($body, [
        'x-shopify-hmac-sha256' => $signature,
    ], $secret))->toBeTrue();
});

test('shopify verifier rejects a tampered payload', function () {
    $secret = 'shpss_secret';
    $signature = base64_encode(hash_hmac('sha256', '{"id":123}', $secret, true));

    expect((new ShopifyVerifier)->verify('{"id":456}', [
        'x-shopify-hmac-sha256' => $signature,
    ], $secret))->toBeFalse();
});

// -- Paystack -----------------------------------------------------------

test('paystack verifier accepts a correctly signed payload', function () {
    $secret = 'sk_test_paystack';
    $body = '{"event":"charge.success"}';
    $signature = hash_hmac('sha512', $body, $secret);

    expect((new PaystackVerifier)->verify($body, [
        'x-paystack-signature' => $signature,
    ], $secret))->toBeTrue();
});

test('paystack verifier rejects an incorrect secret', function () {
    $body = '{"event":"charge.success"}';
    $signature = hash_hmac('sha512', $body, 'right-secret');

    expect((new PaystackVerifier)->verify($body, [
        'x-paystack-signature' => $signature,
    ], 'wrong-secret'))->toBeFalse();
});

// -- Flutterwave ----------------------------------------------------------

test('flutterwave verifier accepts a correctly signed payload', function () {
    $secret = 'my-secret-hash';
    $body = '{"event":"charge.completed"}';
    $signature = base64_encode(hash_hmac('sha256', $body, $secret, true));

    expect((new FlutterwaveVerifier)->verify($body, [
        'flutterwave-signature' => $signature,
    ], $secret))->toBeTrue();
});

test('flutterwave verifier rejects a tampered payload', function () {
    $secret = 'my-secret-hash';
    $signature = base64_encode(hash_hmac('sha256', '{"event":"charge.completed"}', $secret, true));

    expect((new FlutterwaveVerifier)->verify('{"event":"charge.failed"}', [
        'flutterwave-signature' => $signature,
    ], $secret))->toBeFalse();
});

test('flutterwave verifier falls back to a matching legacy shared secret hash', function () {
    expect((new FlutterwaveVerifier)->verify('{}', [
        'verif-hash' => 'my-secret-hash',
    ], 'my-secret-hash'))->toBeTrue();
});

test('flutterwave verifier rejects a mismatched legacy hash', function () {
    expect((new FlutterwaveVerifier)->verify('{}', [
        'verif-hash' => 'wrong-hash',
    ], 'my-secret-hash'))->toBeFalse();
});

test('flutterwave verifier prefers the hmac signature over a legacy header when both are present', function () {
    $secret = 'my-secret-hash';
    $body = '{}';
    $signature = base64_encode(hash_hmac('sha256', $body, $secret, true));

    expect((new FlutterwaveVerifier)->verify($body, [
        'flutterwave-signature' => $signature,
        'verif-hash' => 'wrong-hash',
    ], $secret))->toBeTrue();
});

test('flutterwave verifier rejects a missing header', function () {
    expect((new FlutterwaveVerifier)->verify('{}', [], 'my-secret-hash'))->toBeFalse();
});

// -- Generic HMAC ---------------------------------------------------------

test('generic hmac verifier accepts a correctly signed payload', function () {
    $secret = 'generic-secret';
    $body = '{"event":"ping"}';
    $signature = hash_hmac('sha256', $body, $secret);

    expect((new GenericHmacVerifier)->verify($body, [
        'x-webhook-signature' => $signature,
    ], $secret))->toBeTrue();
});

test('generic hmac verifier rejects an incorrect secret', function () {
    $body = '{"event":"ping"}';
    $signature = hash_hmac('sha256', $body, 'right-secret');

    expect((new GenericHmacVerifier)->verify($body, [
        'x-webhook-signature' => $signature,
    ], 'wrong-secret'))->toBeFalse();
});
