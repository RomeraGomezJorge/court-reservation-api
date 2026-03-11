<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubRequest;
use App\Http\Resources\Club\ClubResource;
use App\Models\Club;
use App\Models\ClubUser;
use Illuminate\Support\Facades\Auth;

final class ClubController
{
    public function store(StoreClubRequest $request): ClubResource
    {

        /** @var Club $club */
        $club = Club::query()->create([
            'club_user_id' => Auth::id(),
            'is_active' => true,
            ...$request->validated(),
        ]);

        return new ClubResource($club);
    }
}

