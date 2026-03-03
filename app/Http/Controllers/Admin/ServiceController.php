<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\Service\StoreServiceRequest;
use App\Http\Requests\Admin\Service\UpdateServiceRequest;
use App\Http\Resources\Admin\ServiceResource;
use App\Models\Service;
use Symfony\Component\HttpFoundation\Response;

final class ServiceController
{
    public function index()
    {
        return ServiceResource::collection(Service::all());
    }

    public function store(StoreServiceRequest $request): Response
    {
        Service::query()->create($request->validated());

        return new Response(status: 201);
    }

    public function show(Service $service): ServiceResource
    {
        return new ServiceResource($service);
    }

    public function update(UpdateServiceRequest $request, Service $service): Response
    {
        $service->update($request->validated());

        return new Response(status: 204);
    }

    public function destroy(Service $service): Response
    {
        $service->delete();

        return new Response(status: 204);
    }
}
