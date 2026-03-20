<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubRequest;
use App\Http\Requests\Club\UpdateClubRequest;
use App\Http\Resources\Club\ClubResource;
use App\Http\Resources\Club\ShowClubResource;
use App\Models\Club;
use App\Services\OwnershipVerifierService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ClubController
{
    public function index(): AnonymousResourceCollection
    {

        return ClubResource::collection(
            Club::query()
                ->where('club_user_id', Auth::id())
                ->get(),
        );
    }

    public function store(StoreClubRequest $request): Response
    {
        Club::query()->create([
            'club_user_id' => Auth::id(),
            'is_active' => true,
            ...$request->validated(),
        ]);

        return new Response(status: 201);
    }

    public function show(Club $club, OwnershipVerifierService $ownershipVerifier): ShowClubResource
    {
        $ownershipVerifier->handle($club);

        return new ShowClubResource($club);
    }

    public function update(
        UpdateClubRequest $request,
        Club $club,
        OwnershipVerifierService $ownershipVerifier
    ): Response {
        $ownershipVerifier->handle($club);

        $club->update($request->validated());

        return new Response(status: 204);
    }

    public function destroy(Club $club, OwnershipVerifierService $ownershipVerifier): Response
    {
        $ownershipVerifier->handle($club);

        $club->delete();

        return new Response(status: 204);
    }
}
