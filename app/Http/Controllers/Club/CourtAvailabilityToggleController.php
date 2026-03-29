<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use App\Models\Court;
use App\Services\OwnershipVerifierService;
use Symfony\Component\HttpFoundation\Response;

final class CourtAvailabilityToggleController
{
    public function __invoke(
        Club $club,
        Court $court,
        OwnershipVerifierService $ownershipVerifier,
    ): Response {
        if ($court->club_id !== $club->id) {
            abort(404, __('validation.resource_not_found', ['resource' => 'Court']));
        }

        $ownershipVerifier->handle($court->club);

        $court->update([
            'is_available' => ! $court->is_available,
        ]);

        return new Response(status: 204);
    }
}
