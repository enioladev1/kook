<?php

namespace App\Providers;

use App\Mail\Transport\SendByteTransport;
use App\Models\ApiKey;
use App\Models\EmailSetting;
use App\Services\EmailSettingService;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use SendByte\SendByte;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureRateLimiting();
        $this->configureMacros();
        $this->configureMail();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }

    /**
     * Rate limits shared across the application (not Fortify-specific).
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('webhook-ingest', function (Request $request) {
            return Limit::perMinute(300)->by($request->route('ingestToken'));
        });

        RateLimiter::for('api-key', function (Request $request) {
            $apiKey = $request->apiKey();

            return Limit::perMinute(120)->by($apiKey instanceof ApiKey ? $apiKey->id : $request->ip());
        });
    }

    /**
     * Convenience accessor for the ApiKey resolved by AuthenticateApiKey.
     */
    protected function configureMacros(): void
    {
        Request::macro('apiKey', function (): ?ApiKey {
            /** @var Request $this */
            return $this->attributes->get('api_key');
        });
    }

    /**
     * Apply the admin's saved email settings to the mail config for this
     * request/command, and register the SendByte transport (no Symfony
     * Mailer bridge exists for it, unlike Resend/Postmark which Laravel
     * supports natively).
     *
     * Wrapped in a try/catch: this runs on every request and console command,
     * including `migrate` itself before the email_settings table exists on a
     * fresh install, so any failure here must fall back to the .env mail
     * config rather than break the whole app.
     */
    protected function configureMail(): void
    {
        // Short timeout + a single retry: the SDK's defaults (30s timeout,
        // 3 attempts) can block a request for well over a minute if the API
        // is unreachable, which - on a single-threaded server - freezes every
        // other page too. Fail fast instead.
        Mail::extend('sendbyte', fn (array $config) => new SendByteTransport(
            new SendByte($config['key'], maxAttempts: 2, timeoutSeconds: 8),
        ));

        try {
            // A plain read, not EmailSettingService::current() - booting must
            // never write to the database (firstOrCreate would), since boot
            // runs before the request/test transaction is open.
            $setting = EmailSetting::query()->first();

            if (! $setting || ! $setting->isConfigured()) {
                return;
            }

            $emailSettings = $this->app->make(EmailSettingService::class);

            $provider = $setting->provider->value;

            Config::set("mail.mailers.{$provider}", $emailSettings->buildMailerConfig([
                'provider' => $setting->provider,
                'api_key' => $setting->api_key,
                'smtp_host' => $setting->smtp_host,
                'smtp_port' => $setting->smtp_port,
                'smtp_username' => $setting->smtp_username,
                'smtp_password' => $setting->smtp_password,
                'smtp_encryption' => $setting->smtp_encryption,
            ]));
            Config::set('mail.default', $provider);

            if ($setting->from_address) {
                Config::set('mail.from.address', $setting->from_address);
                Config::set('mail.from.name', $setting->from_name ?? $setting->from_address);
            }
        } catch (Throwable $e) {
            Log::debug('Skipped applying saved email settings.', ['exception' => $e]);
        }
    }
}
