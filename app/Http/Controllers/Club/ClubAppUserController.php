<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\IndexAppUserRequest;
use App\Http\Requests\Club\StoreAppUserRequest;
use App\Http\Requests\Club\UpdateAppUserRequest;
use App\Http\Resources\Club\AppUserResource;
use App\Models\AppUser;
use App\Models\Club;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ClubAppUserController
{
    private const string DEFAULT_PASSWORD = 'ChangeMe2026!';

    public function index(IndexAppUserRequest $request, Club $club): AnonymousResourceCollection
    {
        Gate::authorize('view', $club);

        $appUsers = $club->appUsers()
            ->latest(AppUser::query()->getModel()->getQualifiedKeyName())
            ->when($request->validated('name'), function (Builder $query, string $name): void {
                $query->whereLike('name', "%{$name}%");
            })
            ->when($request->validated('last_name'), function (Builder $query, string $lastName): void {
                $query->whereLike('last_name', "%{$lastName}%");
            })
            ->when($request->validated('phone_number'), function (Builder $query, string $phoneNumber): void {
                $query->whereLike('phone_number', "%{$phoneNumber}%");
            })
            ->when($request->validated('email'), function (Builder $query, string $email): void {
                $query->whereLike('email', "%{$email}%");
            })
            ->paginate();

        return AppUserResource::collection($appUsers);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreAppUserRequest $request, Club $club): JsonResponse
    {
        Gate::authorize('create', [AppUser::class, $club]);

        $appUser = DB::transaction(function () use ($request, $club): AppUser {
            $appUser = AppUser::query()->create([
                ...$request->validated(),
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]);

            $club->appUsers()->attach($appUser->id);

            return $appUser;
        });

        return new AppUserResource($appUser)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Club $club, AppUser $appUser): AppUserResource
    {
        Gate::authorize('view', [$appUser, $club]);

        return new AppUserResource($appUser);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateAppUserRequest $request, Club $club, AppUser $appUser): AppUserResource
    {
        Gate::authorize('update', [$appUser, $club]);

        $appUser->update($request->validated());

        return new AppUserResource($appUser->refresh());
    }

    /**
     * @throws Throwable
     */
    public function destroy(Club $club, AppUser $appUser): Response
    {
        Gate::authorize('delete', [$appUser, $club]);

        DB::transaction(function () use ($club, $appUser): void {
            $club->appUsers()->detach($appUser->id);
        });

        return new Response(status: 204);
    }
}
