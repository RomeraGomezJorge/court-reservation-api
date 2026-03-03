<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Resources\Admin\UserResource;
use Illuminate\Support\Facades\Auth;

final class ProfileController
{
    public function show(): UserResource
    {
        return new UserResource(Auth::user());
    }
}
