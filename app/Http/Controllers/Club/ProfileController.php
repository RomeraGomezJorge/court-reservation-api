<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Resources\Club\ClubResource;
use App\Http\Resources\Club\UserResource;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;

final class ProfileController
{
    public function show(): ClubResource
    {
        /** @var Club $club */
        $club = Auth::user();

        return new ClubResource($club);
    }
}
