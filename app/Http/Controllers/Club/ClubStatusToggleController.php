<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use App\Services\OwnershipVerifierService;
use Symfony\Component\HttpFoundation\Response;

final class ClubStatusToggleController
{
    public function __invoke(Club $club, OwnershipVerifierService $ownershipVerifier): Response
    {

        $ownershipVerifier->handle($club);

        $club->update([
            'is_active' => ! $club->is_active,
        ]);

        return new Response(status: 204);
    }
}
