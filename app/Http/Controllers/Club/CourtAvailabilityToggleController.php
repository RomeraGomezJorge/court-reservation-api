<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use App\Models\Court;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

final class CourtAvailabilityToggleController
{
    public function __invoke(
        Club $club,
        Court $court,
    ): Response {
        Gate::authorize('update', [$court, $club]);

        $court->update([
            'is_available' => ! $court->is_available,
        ]);

        return new Response(status: 204);
    }
}
