<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Club;
use App\Models\ClubUser;
use Illuminate\Auth\Access\Response;

final class ClubPolicy
{
    public function view(ClubUser $clubUser, Club $club): Response
    {
        return $this->authorizeClubOwnership($clubUser, $club);
    }

    public function update(ClubUser $clubUser, Club $club): Response
    {
        return $this->authorizeClubOwnership($clubUser, $club);
    }

    public function delete(ClubUser $clubUser, Club $club): Response
    {
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
