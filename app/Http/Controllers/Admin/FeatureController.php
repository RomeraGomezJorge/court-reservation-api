<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Feature\StoreFeatureRequest;
use App\Http\Requests\Admin\Feature\UpdateFeatureRequest;
use App\Http\Resources\Admin\FeatureResource;
use App\Models\Feature;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class FeatureController
{
    public function index(): AnonymousResourceCollection
    {
        return FeatureResource::collection(Feature::all());
    }

    public function store(StoreFeatureRequest $request): Response
    {
        Feature::query()->create([
            ...$request->validated(),
            'is_active' => true,
        ]);

        return new Response(status: 201);
    }

    public function show(Feature $feature): FeatureResource
    {
        return new FeatureResource($feature);
    }

    public function update(UpdateFeatureRequest $request, Feature $feature): Response
    {
        $feature->update($request->validated());

        return new Response(status: 204);
    }

    public function destroy(Feature $feature): Response
    {
        $feature->delete();

        return new Response(status: 204);
    }
}
