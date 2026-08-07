<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /**
     * Arbitrary fixed key for a Postgres advisory lock - serializes
     * concurrent registration attempts so two simultaneous submissions on a
     * fresh install can't both create an account before either commits.
     */
    private const REGISTRATION_LOCK_ID = 8_403_291;

    /**
     * Validate and create a newly registered user.
     *
     * Kook only supports a single admin account: registration is a one-time
     * setup step, not an ongoing feature, so this refuses to create a second
     * user even if the gated register view is somehow bypassed.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ])->validate();

        return DB::transaction(function () use ($input) {
            DB::statement('SELECT pg_advisory_xact_lock(?)', [self::REGISTRATION_LOCK_ID]);

            if (User::query()->exists()) {
                throw ValidationException::withMessages([
                    'email' => 'Registration is closed - an admin account already exists.',
                ]);
            }

            return User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
            ]);
        });
    }
}
