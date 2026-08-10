<?php

use App\Mail\TestEmail;
use App\Models\AuditLog;
use App\Models\EmailSetting;
use App\Models\User;
use App\Services\EmailSettingService;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

test('a guest cannot view or mutate email settings', function () {
    $this->get('/settings/email')->assertRedirect('/login');
    $this->put('/settings/email', [])->assertRedirect('/login');
    $this->post('/settings/email/test', [])->assertRedirect('/login');
});

test('a user can view the email settings page', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/email')
        ->assertInertia(fn ($page) => $page
            ->component('settings/email')
            ->has('emailSetting')
        );
});

test('email settings never expose the api key or smtp password', function () {
    $user = User::factory()->create();
    EmailSetting::query()->create([
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 'sk_live_super_secret',
        'smtp_password' => 'super-secret-password',
    ]);

    $this->actingAs($user)
        ->get('/settings/email')
        ->assertInertia(fn ($page) => $page
            ->missing('emailSetting.api_key')
            ->missing('emailSetting.smtp_password')
            ->where('emailSetting.has_api_key', true)
            ->where('emailSetting.has_smtp_password', true)
        );
});

test('has_api_key and has_smtp_password are false until a secret is saved', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/settings/email')
        ->assertInertia(fn ($page) => $page
            ->where('emailSetting.has_api_key', false)
            ->where('emailSetting.has_smtp_password', false)
        );
});

test('the settings page still renders when a saved secret was encrypted under a since-rotated APP_KEY', function () {
    $user = User::factory()->create();
    $setting = EmailSetting::query()->create([
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 'sk_live_super_secret',
        'smtp_password' => 'super-secret-password',
    ]);

    // Simulate ciphertext encrypted under a different APP_KEY than the one
    // currently configured - decrypting it now fails with "The MAC is
    // invalid" instead of the usual "value not set" (null) case.
    $foreignEncrypter = new Encrypter(Encrypter::generateKey(config('app.cipher')), config('app.cipher'));
    DB::table('email_settings')->where('id', $setting->id)->update([
        'api_key' => $foreignEncrypter->encrypt('sk_live_super_secret', false),
        'smtp_password' => $foreignEncrypter->encrypt('super-secret-password', false),
    ]);

    $this->actingAs($user)
        ->get('/settings/email')
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('settings/email')
            ->where('emailSetting.has_api_key', false)
            ->where('emailSetting.has_smtp_password', false)
        );
});

test('a user can save resend settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'from_name' => 'Kook',
        'api_key' => 're_123456',
    ])->assertRedirect('/settings/email');

    $setting = EmailSetting::query()->first();
    expect($setting->provider->value)->toBe('resend');
    expect($setting->from_address)->toBe('hello@example.com');
    expect($setting->api_key)->toBe('re_123456');
});

test('a user can save postmark settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'postmark',
        'from_address' => 'hello@example.com',
        'from_name' => 'Kook',
        'api_key' => 'pm_123456',
    ])->assertRedirect('/settings/email');

    expect(EmailSetting::query()->first())
        ->provider->value->toBe('postmark')
        ->api_key->toBe('pm_123456');
});

test('a user can save sendbyte settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'sendbyte',
        'from_address' => 'hello@example.com',
        'from_name' => 'Kook',
        'api_key' => 'sk_test_123456',
    ])->assertRedirect('/settings/email');

    expect(EmailSetting::query()->first())
        ->provider->value->toBe('sendbyte')
        ->api_key->toBe('sk_test_123456');
});

test('a user can save smtp settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'smtp',
        'from_address' => 'hello@example.com',
        'from_name' => 'Kook',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_username' => 'kook',
        'smtp_password' => 'letmein',
        'smtp_encryption' => 'tls',
    ])->assertRedirect('/settings/email');

    expect(EmailSetting::query()->first())
        ->provider->value->toBe('smtp')
        ->smtp_host->toBe('smtp.example.com')
        ->smtp_port->toBe(587)
        ->smtp_password->toBe('letmein');
});

test('saving smtp requires a host, port, and encryption', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'smtp',
        'from_address' => 'hello@example.com',
    ])->assertSessionHasErrors(['smtp_host', 'smtp_port', 'smtp_encryption']);
});

test('saving requires a valid from address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'resend',
        'from_address' => 'not-an-email',
        'api_key' => 're_123456',
    ])->assertSessionHasErrors('from_address');
});

test('leaving the api key blank on update keeps the existing key', function () {
    $user = User::factory()->create();
    EmailSetting::query()->create([
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 'original-key',
    ]);

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => '',
    ])->assertRedirect('/settings/email');

    expect(EmailSetting::query()->first()->api_key)->toBe('original-key');
});

test('leaving the smtp password blank on update keeps the existing password', function () {
    $user = User::factory()->create();
    EmailSetting::query()->create([
        'provider' => 'smtp',
        'from_address' => 'hello@example.com',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'smtp_password' => 'original-password',
    ]);

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'smtp',
        'from_address' => 'hello@example.com',
        'smtp_host' => 'smtp.example.com',
        'smtp_port' => 587,
        'smtp_encryption' => 'tls',
        'smtp_password' => '',
    ])->assertRedirect('/settings/email');

    expect(EmailSetting::query()->first()->smtp_password)->toBe('original-password');
});

test('providing a new api key on update rotates it', function () {
    $user = User::factory()->create();
    EmailSetting::query()->create([
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 'original-key',
    ]);

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 'rotated-key',
    ])->assertRedirect('/settings/email');

    expect(EmailSetting::query()->first()->api_key)->toBe('rotated-key');
});

test('updating email settings writes an audit log entry', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put('/settings/email', [
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 're_123456',
    ]);

    $log = AuditLog::where('action', 'email_settings.updated')->first();
    expect($log)->not->toBeNull();
    expect($log->user_id)->toBe($user->id);
});

test('sending a test email dispatches it through the submitted provider', function () {
    Mail::fake();
    $user = User::factory()->create();

    $this->actingAs($user)->post('/settings/email/test', [
        'provider' => 'smtp',
        'from_address' => 'hello@example.com',
        'from_name' => 'Kook',
        'smtp_host' => '127.0.0.1',
        'smtp_port' => 1025,
        'smtp_encryption' => 'none',
        'to' => 'you@example.com',
    ])->assertRedirect('/settings/email');

    Mail::assertSent(TestEmail::class, fn ($mail) => $mail->hasTo('you@example.com'));
});

test('sending a test email falls back to the saved secret when the field is left blank', function () {
    Mail::fake();
    $user = User::factory()->create();
    EmailSetting::query()->create([
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => 'saved-key',
    ]);

    $this->actingAs($user)->post('/settings/email/test', [
        'provider' => 'resend',
        'from_address' => 'hello@example.com',
        'api_key' => '',
        'to' => 'you@example.com',
    ])->assertRedirect('/settings/email');

    Mail::assertSent(TestEmail::class);
});

test('a failed test email shows a generic error message without leaking exception details', function () {
    $user = User::factory()->create();

    Mail::shouldReceive('mailer')
        ->once()
        ->andThrow(new RuntimeException('smtp connection refused: password=hunter2'));

    $response = $this->actingAs($user)->post('/settings/email/test', [
        'provider' => 'smtp',
        'from_address' => 'hello@example.com',
        'smtp_host' => '127.0.0.1',
        'smtp_port' => 1025,
        'smtp_encryption' => 'none',
        'to' => 'you@example.com',
    ]);

    $response->assertRedirect('/settings/email');

    $toast = session('inertia.flash_data')['toast'];
    expect($toast['type'])->toBe('error');
    expect($toast['message'])->not->toContain('hunter2');
});

test('build mailer config produces the correct shape per provider', function () {
    $service = app(EmailSettingService::class);

    expect($service->buildMailerConfig([
        'provider' => 'resend',
        'api_key' => 're_123',
    ]))->toBe([
        'transport' => 'resend',
        'key' => 're_123',
    ]);

    expect($service->buildMailerConfig([
        'provider' => 'postmark',
        'api_key' => 'pm_123',
    ]))->toBe([
        'transport' => 'postmark',
        'token' => 'pm_123',
    ]);

    expect($service->buildMailerConfig([
        'provider' => 'sendbyte',
        'api_key' => 'sk_test_123',
    ]))->toBe([
        'transport' => 'sendbyte',
        'key' => 'sk_test_123',
    ]);

    expect($service->buildMailerConfig([
        'provider' => 'smtp',
        'smtp_host' => 'localhost',
        'smtp_port' => 1025,
        'smtp_username' => 'kook',
        'smtp_password' => 'secret',
        'smtp_encryption' => 'ssl',
    ]))->toBe([
        'transport' => 'smtp',
        'host' => 'localhost',
        'port' => 1025,
        'username' => 'kook',
        'password' => 'secret',
        'scheme' => 'smtps',
        'timeout' => 10,
    ]);
});
