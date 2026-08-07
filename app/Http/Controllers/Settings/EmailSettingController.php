<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SendTestEmailRequest;
use App\Http\Requests\Settings\UpdateEmailSettingRequest;
use App\Services\EmailSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Log\LogManager;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class EmailSettingController extends Controller
{
    public function __construct(
        private readonly EmailSettingService $emailSettings,
        private readonly LogManager $log,
    ) {}

    public function edit(): Response
    {
        return Inertia::render('settings/email', [
            'emailSetting' => $this->emailSettings->current(),
        ]);
    }

    public function update(UpdateEmailSettingRequest $request): RedirectResponse
    {
        $this->emailSettings->update($request->user(), $request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => 'Email settings saved.']);

        return to_route('email-settings.edit');
    }

    public function test(SendTestEmailRequest $request): RedirectResponse
    {
        try {
            $this->emailSettings->sendTest($request->validated(), $request->string('to')->value());

            Inertia::flash('toast', ['type' => 'success', 'message' => 'Test email sent.']);
        } catch (Throwable $e) {
            $this->log->error('Failed to send test email.', ['exception' => $e]);

            Inertia::flash('toast', [
                'type' => 'error',
                'message' => "Couldn't send the test email. Double-check your credentials and try again.",
            ]);
        }

        return to_route('email-settings.edit');
    }
}
