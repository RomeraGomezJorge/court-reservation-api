<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubRequest;
use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ClubController
{
    public function store(StoreClubRequest $request): Response
    {

        /** @var Club $club */
        $club = Club::query()->create([
            'club_user_id' => Auth::id(),
            'is_active' => true,
            ...$request->validated(),
        ]);

        return new Response(status: 201);
    }
}

