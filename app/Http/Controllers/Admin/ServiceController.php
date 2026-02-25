<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\StoreServiceRequest;
use App\Http\Requests\Admin\UpdateServiceRequest;
use App\Http\Resources\Admin\ServiceResource;
use App\Models\Service;
use Symfony\Component\HttpFoundation\Response;

final class ServiceController
{
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    public function store(StoreServiceRequest $request)
    {
        Service::query()->create($request->validated());

        return new Response(status: 201);
    }

    public function show(Service $service)
    {
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        $service->update($request->validated());

        return new Response(status: 204);
    }

    public function destroy(Service $service)
    {
        $service->delete();

        return new Response(status: 204);
    }


}
