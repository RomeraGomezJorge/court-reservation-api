<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;
use Hash;

final class LoginController
{
    public function login(LoginRequest $request): string
    {
        /** @var User|null $user */
        $user = User::query()->where('email', $request->email)->first();

        if (! $user) {
            abort(403, __('auth.failed'));
        }

        if (! Hash::check($request->password, $user->password)) {
            abort(403, __('auth.failed'));
        }



        return $user->createToken($user->email)->plainTextToken;
    }
}
