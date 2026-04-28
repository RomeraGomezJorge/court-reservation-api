<?php

declare(strict_types=1);

namespace App\Http\Controllers\App;

use App\Http\Requests\App\LoginRequest;
use App\Models\AppUser;

final class LoginController
{
    public function login(LoginRequest $request): string
    {
        /** @var AppUser $appUser */
        $appUser = AppUser::query()->where('email', $request->email)->firstOrFail();

        return $appUser->createToken($appUser->email)->plainTextToken;
    }
}
