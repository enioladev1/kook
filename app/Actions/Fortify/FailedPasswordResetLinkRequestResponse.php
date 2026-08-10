<?php

namespace App\Actions\Fortify;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\FailedPasswordResetLinkRequestResponse as FailedPasswordResetLinkRequestResponseContract;

class FailedPasswordResetLinkRequestResponse implements FailedPasswordResetLinkRequestResponseContract
{
    // Deliberately identical to the success response - Fortify's default
    // here returns a field-specific "we can't find a user with that email"
    // error, which lets an attacker enumerate whether an account exists.
    public function __construct(protected string $status) {}

    public function toResponse($request)
    {
        return $request->wantsJson()
            ? new JsonResponse(['message' => SuccessfulPasswordResetLinkRequestResponse::MESSAGE], 200)
            : back()->with('status', SuccessfulPasswordResetLinkRequestResponse::MESSAGE);
    }
}
