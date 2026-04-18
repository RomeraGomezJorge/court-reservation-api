<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use Illuminate\Support\Facades\Gate;
use App\Models\Club;
use Symfony\Component\HttpFoundation\Response;

final class ClubStatusToggleController
{
    public function __invoke(Club $club): Response
    {
        Gate::authorize('update', $club);

        $club->update([
            'is_active' => ! $club->is_active,
        ]);

        return new Response(status: 204);
    }
}
