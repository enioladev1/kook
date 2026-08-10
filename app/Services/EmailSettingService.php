<?php

namespace App\Services;

use App\Enums\EmailProvider;
use App\Enums\SmtpEncryption;
use App\Mail\TestEmail;
use App\Models\EmailSetting;
use App\Models\User;
use App\Repositories\EmailSettingRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class EmailSettingService
{
    public function __construct(
        private readonly EmailSettingRepository $settings,
        private readonly AuditLogService $auditLog,
    ) {}

    public function current(): EmailSetting
    {
        return $this->settings->current();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): EmailSetting
    {
        // Blank means "leave the current secret alone" - secrets are never
        // redisplayed for the user to re-paste, mirroring WebhookEndpointService.
        foreach (['api_key', 'smtp_password'] as $secretField) {
            if (array_key_exists($secretField, $data) && ! $data[$secretField]) {
                unset($data[$secretField]);
            }
        }

        $setting = DB::transaction(function () use ($user, $data) {
            $setting = $this->settings->update($this->settings->current(), $data);

            $this->auditLog->record($user, null, 'email_settings.updated', $setting);

            return $setting;
        });

        // Queue workers boot once and keep running - AppServiceProvider's
        // mail config (built from this table) only applies at that one boot,
        // so a long-running worker never sees a settings change on its own.
        // Signal it to restart after its current job so queued mail (e.g.
        // password resets) picks up the new credentials instead of failing
        // with "Mailer [x] is not defined."
        Artisan::call('queue:restart');

        return $setting;
    }

    /**
     * Applies the given (possibly unsaved) settings for this request only
     * and sends a test email through them - lets someone verify credentials
     * before committing to save them.
     *
     * @param  array<string, mixed>  $data
     */
    public function sendTest(array $data, string $to): void
    {
        $current = $this->settings->current();

        // Same "blank means use what's already saved" fallback as update() -
        // a secret field is left blank in the form when testing already-saved,
        // unchanged credentials, since the value is never sent to the frontend.
        if (empty($data['api_key'])) {
            $data['api_key'] = $current->api_key;
        }

        if (empty($data['smtp_password'])) {
            $data['smtp_password'] = $current->smtp_password;
        }

        $provider = $this->normalizeProvider($data['provider']);

        Config::set("mail.mailers.{$provider->value}", $this->buildMailerConfig($data));

        Mail::mailer($provider->value)->to($to)->send(new TestEmail);
    }

    /**
     * Builds the mail.mailers.<name> config array for a given provider and
     * credentials. Shared between the boot-time apply (from saved settings)
     * and the test-send path (from unsaved form input).
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function buildMailerConfig(array $data): array
    {
        $provider = $this->normalizeProvider($data['provider']);

        return match ($provider) {
            EmailProvider::Resend => [
                'transport' => 'resend',
                'key' => $data['api_key'] ?? null,
            ],
            EmailProvider::Postmark => [
                'transport' => 'postmark',
                'token' => $data['api_key'] ?? null,
            ],
            EmailProvider::SendByte => [
                'transport' => 'sendbyte',
                'key' => $data['api_key'] ?? null,
            ],
            EmailProvider::Smtp => [
                'transport' => 'smtp',
                'host' => $data['smtp_host'] ?? null,
                'port' => $data['smtp_port'] ?? null,
                'username' => $data['smtp_username'] ?? null,
                'password' => $data['smtp_password'] ?? null,
                'scheme' => $this->normalizeEncryption($data['smtp_encryption'] ?? null) === SmtpEncryption::Ssl
                    ? 'smtps'
                    : 'smtp',
                // Without this, a wrong/unreachable host hangs on PHP's
                // default_socket_timeout (often 60s) before failing.
                'timeout' => 10,
            ],
        };
    }

    private function normalizeProvider(EmailProvider|string $provider): EmailProvider
    {
        return $provider instanceof EmailProvider ? $provider : EmailProvider::from($provider);
    }

    private function normalizeEncryption(SmtpEncryption|string|null $encryption): ?SmtpEncryption
    {
        if ($encryption === null || $encryption instanceof SmtpEncryption) {
            return $encryption;
        }

        return SmtpEncryption::from($encryption);
    }
}
