<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ClubUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureUserIsClubUser
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::user() instanceof ClubUser) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        return $next($request);
    }
}

