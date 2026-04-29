<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Requests\App\StorePasswordResetRequest;
use App\Http\Requests\App\UpdatePasswordResetRequest;
use App\Models\AppUser;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class PasswordResetController
{
    public function store(StorePasswordResetRequest $request): Response
    {
        Password::broker('app_users')->sendResetLink(['email' => $request->email]);

        return new Response(status: 201);
    }

    public function update(UpdatePasswordResetRequest $request): Response
    {
        AppUser::query()->where('email', $request->email)->firstOrFail();

        $status = Password::broker('app_users')->reset(
            [
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function (AppUser $appUser, string $password): void {
                $appUser->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $appUser->save();
                event(new PasswordReset($appUser));
            },
        );

        if ($status !== Password::PASSWORD_RESET) {
            abort(422, __($status));
        }

        return new Response(status: 204);
    }
}
