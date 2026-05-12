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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class AppUserController
{
    public function index(IndexAppUserRequest $request): AnonymousResourceCollection
    {
        $clubIds = Club::query()
            ->where('club_user_id', Auth::id())
            ->pluck('id');

        $appUserIds = DB::table('app_user_club')
            ->whereIn('club_id', $clubIds)
            ->pluck('app_user_id');

        $appUsers = AppUser::query()
            ->whereIn('id', $appUserIds)
            ->latest()
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
    public function store(StoreAppUserRequest $request): JsonResponse
    {
        $appUser = DB::transaction(function () use ($request): AppUser {

            $clubIds = Club::query()
                ->where('club_user_id', Auth::id())
                ->pluck('id');

            $appUser = AppUser::query()
                ->where('email', $request->validated('email'))
                ->where('phone_number', $request->validated('phone_number'))
                ->first();

            if ($appUser) {

                $appUser->update($request->validated());

                $appUser->clubs()->syncWithoutDetaching($clubIds);

            } else {

                $appUser = AppUser::query()->create([
                    ...$request->validated(),
                    'password' => Hash::make(Str::password(32)),
                ]);

                $appUser->clubs()->attach($clubIds);

                DB::afterCommit(function () use ($appUser): void {
                    Password::broker('app_users')->sendResetLink(['email' => $appUser->email]);
                });

            }

            return $appUser;
        });

        return new AppUserResource($appUser)
            ->response()
            ->setStatusCode(201);
    }

    public function show(AppUser $appUser): AppUserResource
    {
        Gate::authorize('view', [$appUser]);

        return new AppUserResource($appUser);
    }

    /**
     * @throws Throwable
     */
    public function update(UpdateAppUserRequest $request, AppUser $appUser): AppUserResource
    {
        Gate::authorize('update', [$appUser]);

        $appUser->update($request->validated());

        return new AppUserResource($appUser->refresh());
    }

    /**
     * @throws Throwable
     */
    public function destroy(AppUser $appUser): Response
    {
        Gate::authorize('delete', [$appUser]);

        DB::transaction(function () use ($appUser): void {

            $clubIds = Club::query()
                ->where('club_user_id', Auth::id())
                ->pluck('id');

            $appUser->clubs()->detach($clubIds);
        });

        return new Response(status: 204);
    }
}
