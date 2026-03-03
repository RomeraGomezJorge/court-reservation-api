<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\LoginRequest;
use App\Models\User;

final class LoginController
{
    public function login(LoginRequest $request): string
    {
        /** @var User $user */
        $user = User::query()->where('email', $request->email)->firstOrFail();

        return $user->createToken($user->email)->plainTextToken;
    }
}
