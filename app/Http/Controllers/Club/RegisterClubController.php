<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubRequest;
use App\Models\Club;
use Symfony\Component\HttpFoundation\Response;

final class RegisterClubController
{
    public function store(StoreClubRequest $request): Response
    {
        /** @var Club $club */
        $club = Club::query()->create($request->validated());

        $club->sendEmailVerificationNotification();

        return new Response(status: 201);
    }
}

