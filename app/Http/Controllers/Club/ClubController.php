<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubRequest;
use App\Http\Requests\Club\UpdateClubRequest;
use App\Http\Resources\Club\ClubResource;
use App\Http\Resources\Club\ShowClubResource;
use App\Models\Club;
use App\Models\ClubWorkingDay;
use App\Services\OwnershipVerifierService;
use DB;
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
        DB::transaction(function () use ($request): void {
            $club = Club::query()->create([
                'club_user_id' => Auth::id(),
                'is_active' => true,
                ...$request->clubData(),
            ]);

            $workingDays = collect($request->workingDays())
                ->map(fn(array $day) => [
                    'day' => $day['day'],
                    'opening_hour' => $day['opening_hour'],
                    'closing_hour' => $day['closing_hour'],
                ]);

            $club->workingDays()->createMany($workingDays->toArray());
        });

        return new Response(status: 201);
    }

    public function show(
        Club $club,
        OwnershipVerifierService $ownershipVerifier,
    ): ShowClubResource {
        $ownershipVerifier->handle($club);

        return new ShowClubResource($club);
    }

    public function update(
        UpdateClubRequest $request,
        Club $club,
        OwnershipVerifierService $ownershipVerifier,
    ): Response {
        $ownershipVerifier->handle($club);

        DB::transaction(function () use ($request, $club): void {
            $club->update(...$request->clubData());

            if ($request->has('working_days')) {
                $club->workingDays()->delete();

                $workingDays = collect($request->workingDays())
                    ->map(fn(array $day) => [
                        'day' => $day['day'],
                        'opening_hour' => $day['opening_hour'],
                        'closing_hour' => $day['closing_hour'],
                    ]);

                $club->workingDays()->createMany($workingDays->toArray());
            }
        });

        return new Response(status: 204);
    }

    public
    function destroy(
        Club $club,
        OwnershipVerifierService $ownershipVerifier,
    ): Response {
        $ownershipVerifier->handle($club);

        $club->delete();

        return new Response(status: 204);
    }
}
