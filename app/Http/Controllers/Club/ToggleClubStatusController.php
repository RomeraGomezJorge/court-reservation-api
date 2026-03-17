<?php

declare(strict_types=1);

namespace App\Http\Controllers\Club;

use App\Models\Club;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class ToggleClubStatusController
{
    public function __invoke(Club $club): Response
    {
        abort_if($club->club_user_id !== (string) Auth::id(), 404);

        $club->update([
            'is_active' => ! $club->is_active,
        ]);

        return new Response(status: 204);
    }
}

