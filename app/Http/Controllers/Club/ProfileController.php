<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Resources\Club\ClubUserResource;
use App\Models\ClubUser;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Throwable;

final class ProfileController
{
    public function show(): ClubUserResource
    {
        /** @var ClubUser $clubUser */
        $clubUser = Auth::user();

        return new ClubUserResource($clubUser);
    }

    /**
     * @throws Throwable
     */
    public function destroy(): Response
    {
        DB::transaction(function (): void {
            /** @var ClubUser $clubUser */
            $clubUser = Auth::user();

            $clubUser->update([
                'email' => Str::random(10).'@deleted_club_user.com',
            ]);

            $clubUser->delete();
        });

        return new Response(status: 204);
    }
}
