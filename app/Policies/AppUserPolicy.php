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
    public function create(ClubUser $clubUser, Club $club): Response
    {
        return $this->authorizeClubOwnership($clubUser, $club);
    }

    public function view(ClubUser $clubUser, AppUser $appUser, Club $club): Response
    {
        return $this->authorizeClubAppUser($clubUser, $appUser, $club);
    }

    public function update(ClubUser $clubUser, AppUser $appUser, Club $club): Response
    {
        return $this->authorizeClubAppUser($clubUser, $appUser, $club);
    }

    public function delete(ClubUser $clubUser, AppUser $appUser, Club $club): Response
    {
        return $this->authorizeClubAppUser($clubUser, $appUser, $club);
    }

    private function authorizeClubAppUser(ClubUser $clubUser, AppUser $appUser, Club $club): Response
    {
        $appUserBelongsToClub = DB::table('app_user_club')
            ->where('club_id', $club->id)
            ->where('app_user_id', $appUser->id)
            ->exists();

        if (! $appUserBelongsToClub) {
            return Response::denyAsNotFound(__('validation.resource_not_found'));
        }

        return $this->authorizeClubOwnership($clubUser, $club);
    }

    private function authorizeClubOwnership(ClubUser $clubUser, Club $club): Response
    {
        if (! $clubUser->owns($club)) {
            return Response::denyAsNotFound(__('validation.resource_not_found'));
        }

        return Response::allow();
    }

}
