<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\SportType\StoreSportTypeRequest;
use App\Http\Requests\Admin\SportType\UpdateSportTypeRequest;
use App\Http\Resources\Admin\SportTypeResource;
use App\Models\SportType;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class SportTypeController
{
    public function index(): AnonymousResourceCollection
    {
        return SportTypeResource::collection(SportType::all());
    }

    public function store(StoreSportTypeRequest $request): Response
    {
        SportType::query()->create($request->validated());

        return new Response(status: 201);
    }

    public function show(SportType $sportType): SportTypeResource
    {
        return new SportTypeResource($sportType);
    }

    public function update(UpdateSportTypeRequest $request, SportType $sportType): Response
    {
        $sportType->update($request->validated());

        return new Response(status: 204);
    }

    public function destroy(SportType $sportType): Response
    {
        $sportType->delete();

        return new Response(status: 204);
    }
}
