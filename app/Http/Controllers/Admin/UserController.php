<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Requests\Admin\User\ChangeUserPasswordRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Http\Requests\Admin\User\UpdateUserRequest;
use App\Http\Resources\Admin\UserResource;
use App\Models\User;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

final class UserController
{
    public function index(): AnonymousResourceCollection
    {
        return UserResource::collection(User::query()->get());
    }

    public function store(StoreUserRequest $request): Response
    {
        User::query()->create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return new Response(status: 201);
    }

    public function update(UpdateUserRequest $request, User $user): Response
    {
        $user->update([
            'email' => $request->email,
        ]);

        return new Response(status: 204);
    }

    public function changePassword(ChangeUserPasswordRequest $request, User $user): Response
    {
        $user->update([
            'password' => bcrypt((string) $request->password),
        ]);

        return new Response(status: 204);
    }

    public function show(User $user): UserResource
    {
        return new UserResource($user);
    }

    public function destroy(User $user): void
    {
        $user->delete();
    }
}
