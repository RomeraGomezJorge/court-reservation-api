<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreClubRequest;
use App\Http\Requests\Club\UpdateClubRequest;
use App\Http\Resources\Club\ClubResource;
use App\Http\Resources\Club\ShowClubResource;
use App\Models\Club;
use App\Services\OwnershipVerifierService;
use DB;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

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

    /**
     * @throws Throwable
     */
    public function store(StoreClubRequest $request): Response
    {
        DB::transaction(function () use ($request): void {
            $club = Club::query()->create([
                'club_user_id' => Auth::id(),
                'is_active' => true,
                ...$request->clubData(),
            ]);

            $workingDays = collect($request->workingDays())
                ->map(fn (array $day) => [
                    'day' => $day['day'],
                    'opening_hour' => $day['opening_hour'],
                    'closing_hour' => $day['closing_hour'],
                ]);

            $club->workingDays()->createMany($workingDays->toArray());

            if ($request->has('services')) {
                $services = collect($request->services())
                    ->map(fn (string $service) => ['type' => $service]);

                $club->services()->createMany($services->toArray());
            }
        });

        return new Response(status: 201);
    }

    public function show(
        Club $club,
        OwnershipVerifierService $ownershipVerifier,
    ): ShowClubResource {
        $ownershipVerifier->handle($club);

        $club->loadMissing(['workingDays', 'services']);

        return new ShowClubResource($club);
    }

    /**
     * @throws Throwable
     */
    public function update(
        UpdateClubRequest $request,
        Club $club,
        OwnershipVerifierService $ownershipVerifier,
    ): Response {
        $ownershipVerifier->handle($club);

        DB::transaction(function () use ($request, $club): void {
            $club->update([...$request->clubData()]);

            if ($request->has('working_days')) {
                $club->workingDays()->delete();

                $workingDays = collect($request->workingDays())
                    ->map(fn (array $day) => [
                        'day' => $day['day'],
                        'opening_hour' => $day['opening_hour'],
                        'closing_hour' => $day['closing_hour'],
                    ]);

                $club->workingDays()->createMany($workingDays->toArray());
            }

            if ($request->has('services')) {
                $club->services()->delete();

                $services = collect($request->services())
                    ->map(fn (string $service) => ['type' => $service]);

                $club->services()->createMany($services->toArray());
            }
        });

        return new Response(status: 204);
    }

    /**
     * @throws Throwable
     */
    public function destroy(
        Club $club,
        OwnershipVerifierService $ownershipVerifier,
    ): Response {
        $ownershipVerifier->handle($club);

        DB::transaction(function () use ($club): void {
            $club->update([
                'organization_name' => "{$club->organization_name} (deleted #{$club->id})",
            ]);

            $club->delete();
        });

        return new Response(status: 204);
    }
}
