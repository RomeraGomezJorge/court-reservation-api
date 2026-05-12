<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AppUser;
use App\Models\Club;
use App\Models\ClubUser;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\DB;

final class AppUserPolicy
{
    public function view(ClubUser $clubUser, AppUser $appUser): Response
    {
        return $this->authorizeClubAppUser($clubUser, $appUser);
    }

    public function delete(ClubUser $clubUser, AppUser $appUser): Response
    {
        return $this->authorizeClubAppUser($clubUser, $appUser);
    }

    private function authorizeClubAppUser(ClubUser $clubUser, AppUser $appUser): Response
    {
        $clubIds = Club::query()
            ->where('club_user_id', $clubUser->id)
            ->pluck('id');

        $appUserBelongsToClub = DB::table('app_user_club')
            ->whereIn('club_id', $clubIds)
            ->where('app_user_id', $appUser->id)
            ->exists();

        if (! $appUserBelongsToClub) {
            return Response::denyAsNotFound(__('validation.resource_not_found'));
        }

        return Response::allow();
    }
}
