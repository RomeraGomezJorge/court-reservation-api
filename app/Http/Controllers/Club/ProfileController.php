<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Resources\Club\ClubUserResource;
use App\Models\ClubUser;
use Illuminate\Support\Facades\Auth;

final class ProfileController
{
    public function show(): ClubUserResource
    {
        /** @var ClubUser $clubUser */
        $clubUser = Auth::guard('club_users')->user();

        return new ClubUserResource($clubUser);
    }
}
