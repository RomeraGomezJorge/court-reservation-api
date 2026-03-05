<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StorePasswordResetRequest;
use App\Http\Requests\Club\UpdatePasswordResetRequest;
use App\Models\Club;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

final class PasswordResetController
{
    public function store(StorePasswordResetRequest $request): Response
    {
        info('sendResetLinkStatus', [
            Password::broker('clubs')->sendResetLink(['email' => $request->email]),
        ]);

        return new Response(status: 201);
    }

    public function update(UpdatePasswordResetRequest $request): Response
    {
        Club::query()->where('email', $request->email)->firstOrFail();

        Password::reset(
            [
                'email' => $request->email,
                'password' => $request->password,
                'password_confirmation' => $request->password_confirmation,
                'token' => $request->token,
            ],
            function (Club $club, string $password): void {
                $club->forceFill(['password' => $password])->setRememberToken(Str::random(60));
                $club->save();
                event(new PasswordReset($club));
            },
        );

        return new Response(status: 204);
    }
}
