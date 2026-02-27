<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StorePasswordResetRequest;
use App\Http\Requests\Admin\UpdatePasswordResetRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class PasswordResetController
{
    public function store(StorePasswordResetRequest $request): Response
    {
        User::query()->where('email', $request->email)->firstOrFail();

        Password::sendResetLink(['email' => $request->email]);

        return new Response(status: 201);
    }

    public function update(UpdatePasswordResetRequest $request): Response
    {
        User::query()->where('email', $request->email)->firstOrFail();

        Password::reset(
            [
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function ($user, $password): void {
                $user->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            },
        );

        return new Response(status: 204);
    }
}
