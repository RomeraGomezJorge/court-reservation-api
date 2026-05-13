<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AppUser;
use App\Models\ClubUser;
use Illuminate\Auth\Access\Response;

final class AppUserPolicy
{
    public function create(ClubUser $loggedClubUser, array $clubIds): Response
    {
        $clubsBelongToLoggedClubUserCount = $loggedClubUser->clubs()
            ->whereIn('id', $clubIds)
            ->count();

        info('comparation', [$clubIds, $clubsBelongToLoggedClubUserCount, count($clubIds)]);

        return $clubsBelongToLoggedClubUserCount !== count($clubIds)
            ? Response::denyAsNotFound(__('validation.resource_not_found'))
            : Response::allow();
    }

    public function view(ClubUser $loggedClubUser, AppUser $appUser): Response
    {
        return $this->authorizeClubAppUser($loggedClubUser, $appUser);
    }

    public function delete(ClubUser $loggedClubUser, AppUser $appUser): Response
    {
        return $this->authorizeClubAppUser($loggedClubUser, $appUser);
    }

    private function authorizeClubAppUser(ClubUser $loggedClubUser, AppUser $appUser): Response
    {
        $appUserBelongsToClub = $appUser->clubs()
            ->where('club_user_id', $loggedClubUser->id)
            ->exists();

        return (! $appUserBelongsToClub)
            ? Response::denyAsNotFound(__('validation.resource_not_found'))
            : Response::allow();
    }
}
