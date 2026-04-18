<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\StoreCourtRequest;
use App\Http\Requests\Club\UpdateCourtRequest;
use App\Http\Resources\Club\ShowCourtResource;
use App\Models\Club;
use App\Models\Court;
use Gate;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class CourtController
{
    /**
     * @throws Throwable
     */
    public function store(StoreCourtRequest $request, Club $club): Response
    {
        Gate::authorize('create', [Court::class, $club]);

        DB::transaction(function () use ($request, $club): void {
            $court = $club->courts()->create([
                ...$request->courtData(),
                'is_available' => true,
            ]);

            if ($request->has('features')) {
                $court->features()->sync($request->featureIds());
            }
        });

        return new Response(status: 201);
    }

    public function show(Club $club, Court $court): ShowCourtResource
    {
        Gate::authorize('view', [$court, $club]);

        $court->loadMissing([
            'sportType',
            'features',
        ]);

        return new ShowCourtResource($court);
    }

    /**
     * @throws Throwable
     */
    public function update(
        UpdateCourtRequest $request,
        Club $club,
        Court $court,
    ): Response {

        Gate::authorize('update', [$court, $club]);

        DB::transaction(function () use ($request, $court): void {
            $court->update($request->courtData());

            if ($request->has('features')) {
                $court->features()->sync($request->featureIds());
            }
        });

        return new Response(status: 204);
    }

    /**
     * @throws Throwable
     */
    public function destroy(Club $club, Court $court): Response
    {

        Gate::authorize('delete', [$court, $club]);

        DB::transaction(function () use ($court): void {
            $court->update([
                'name' => "{$court->name} (deleted #{$court->id})",
            ]);

            $court->delete();
        });

        return new Response(status: 204);
    }
}
