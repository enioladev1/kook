<?php

namespace Tests\Feature\Auth;

use App\Mail\ResetPasswordMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Laravel\Fortify\Features;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::resetPasswords());
    }

    public function test_reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get(route('password.request'));

        $response->assertOk();
    }

    public function test_reset_password_link_can_be_requested()
    {
        Mail::fake();

        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Mail::assertQueued(ResetPasswordMail::class, fn ($mail) => str_contains($mail->resetUrl, urlencode($user->email)));
    }

    public function test_the_reset_link_request_shows_the_same_message_whether_or_not_the_email_is_registered()
    {
        Mail::fake();

        $user = User::factory()->create();

        $known = $this->post(route('password.email'), ['email' => $user->email]);
        $knownMessage = session('status');

        $unknown = $this->post(route('password.email'), ['email' => 'nobody@example.com']);
        $unknownMessage = session('status');

        $known->assertSessionHasNoErrors();
        $unknown->assertSessionHasNoErrors();
        expect($knownMessage)->not->toBeNull();
        expect($unknownMessage)->toBe($knownMessage);

        Mail::assertQueuedCount(1);
    }

    public function test_reset_password_screen_can_be_rendered()
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]));

        $response->assertOk();
    }

    public function test_password_can_be_reset_with_valid_token()
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login'));
    }

    public function test_password_cannot_be_reset_with_invalid_token(): void
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
