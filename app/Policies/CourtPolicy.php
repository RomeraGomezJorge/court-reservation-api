<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Club;
use App\Models\ClubUser;
use App\Models\Court;
use Illuminate\Auth\Access\Response;

final class CourtPolicy
{
    public function view(ClubUser $clubUser, Court $court, Club $club): Response
    {
        return $this->authorizeClubCourt($clubUser, $court, $club);
    }

    public function create(ClubUser $clubUser, Club $club): Response
    {
        return $this->authorizeClubOwnership($clubUser, $club);
    }

    public function update(ClubUser $clubUser, Court $court, Club $club): Response
    {
        return $this->authorizeClubCourt($clubUser, $court, $club);
    }

    public function delete(ClubUser $clubUser, Court $court, Club $club): Response
    {
        return $this->authorizeClubCourt($clubUser, $court, $club);
    }

    private function authorizeClubCourt(ClubUser $clubUser, Court $court, Club $club): Response
    {
        if ($court->club_id !== $club->id) {
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
