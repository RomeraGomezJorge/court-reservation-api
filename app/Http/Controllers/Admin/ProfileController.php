<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

final class ProfileController
{
    public function show(): UserResource
    {
        /** @var User $user */
        $user = Auth::user();

        return new UserResource($user);
    }
}
