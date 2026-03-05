<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\LoginRequest;
use App\Models\Club;
use App\Models\User;

final class LoginController
{
    public function login(LoginRequest $request): string
    {
        /** @var Club $club */
        $club = Club::query()->where('email', $request->email)->firstOrFail();

        return $club->createToken($club->email)->plainTextToken;
    }
}
