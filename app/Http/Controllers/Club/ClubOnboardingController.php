<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubOnboardingRequest;
use App\Http\Resources\Club\ClubResource;
use App\Models\Club;
use App\Models\ClubUser;
use Illuminate\Support\Facades\Auth;

final class ClubOnboardingController
{
    public function store(StoreClubOnboardingRequest $request): ClubResource
    {
        /** @var ClubUser $clubUser */
        $clubUser = Auth::guard('club_users')->user();

        /** @var Club $club */
        $club = Club::query()->create([
            'club_user_id' => $clubUser->id,
            ...$request->validated(),
        ]);

        return new ClubResource($club);
    }
}

