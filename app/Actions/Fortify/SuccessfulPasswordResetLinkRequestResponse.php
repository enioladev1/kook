<?php

namespace App\Actions\Fortify;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\SuccessfulPasswordResetLinkRequestResponse as SuccessfulPasswordResetLinkRequestResponseContract;

class SuccessfulPasswordResetLinkRequestResponse implements SuccessfulPasswordResetLinkRequestResponseContract
{
    // Shown regardless of whether the email matched an account - see
    // FailedPasswordResetLinkRequestResponse, which returns the exact same
    // message so this form can't be used to enumerate the admin's email.
    public const MESSAGE = "If that email is registered, we've sent a password reset link.";

    public function __construct(protected string $status) {}

    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => self::MESSAGE], 200)
            : back()->with('status', self::MESSAGE);
    }
}
