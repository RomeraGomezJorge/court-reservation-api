<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\LoginRequest;
use App\Models\ClubUser;

final class LoginController
{
    public function login(LoginRequest $request): string
    {
        /** @var ClubUser $clubUser */
        $clubUser = ClubUser::query()->where('email', $request->email)->firstOrFail();

        return $clubUser->createToken($clubUser->email)->plainTextToken;
    }
}
