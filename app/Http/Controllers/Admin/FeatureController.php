<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Feature\StoreFeatureRequest;
use App\Http\Requests\Admin\Feature\UpdateFeatureRequest;
use App\Http\Resources\Admin\FeatureResource;
use App\Models\Feature;
use Symfony\Component\HttpFoundation\Response;

class FeatureController
{

    public function index()
    {
        return FeatureResource::collection(Feature::all());
    }


    public function store(StoreFeatureRequest $request)
    {
        Feature::query()->create($request->validated());

        return new Response(status: 201);
    }

    public function show(Feature $feature)
    {
        return new FeatureResource($feature);
    }

    public function update(UpdateFeatureRequest $request, Feature $feature)
    {
        $feature->update($request->validated());

        return new Response(status: 204);
    }

    public function destroy(Feature $feature)
    {
        $feature->delete();

        return new Response(status: 204);
    }
}
