<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Http\Requests\Club\IndexAppUserRequest;
use App\Http\Requests\Club\StoreAppUserRequest;
use App\Http\Resources\Club\AppUserResource;
use App\Http\Resources\Club\ShowAppUserResource;
use App\Models\AppUser;
use App\Models\Club;
use App\Services\ClubUserCreateOrAttachAppUserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            ->orderBy(
                $request->validated('sort_column', 'created_at'),
                $request->validated('sort_direction', 'desc')
            )
            ->paginate(
                perPage: $request->validated('per_page', 10),
                page: $request->validated('page'),
            );

        return AppUserResource::collection($appUsers);
    }

    /**
     * @throws Throwable
     */
    public function store(StoreAppUserRequest $request, ClubUserCreateOrAttachAppUserService $appUserCreator): JsonResponse
    {
        $appUser = $appUserCreator->handle(
            attributes: $request->validatedAttributes(),
        );

        return new AppUserResource($appUser)
            ->response()
            ->setStatusCode(201);
    }

    public function show(AppUser $appUser): ShowAppUserResource
    {
        Gate::authorize('view', $appUser);

        return new ShowAppUserResource($appUser);
    }

    /**
     * @throws Throwable
     */
    public function destroy(AppUser $appUser): Response
    {
        Gate::authorize('delete', $appUser);

        DB::transaction(function () use ($appUser): void {

            $clubIds = Club::query()
                ->where('club_user_id', Auth::id())
                ->pluck('id');

            $appUser->clubs()->detach($clubIds);
        });

        return new Response(status: 204);
    }
}
