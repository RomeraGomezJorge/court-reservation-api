<?php

declare(strict_types=1);

namespace Tests\Http\Middleware;

use App\Http\Middleware\EnsureUserIsClubUser;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('allows authenticated club users', function (): void {
    actingAsClubUser();

    $response = (new EnsureUserIsClubUser())->handle(
        Request::create('/api/club/test'),
        static fn (Request $request): Response => response('allowed'),
    );

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('allowed');
});

it('rejects admin users as club users', function (): void {
    actingAsUser();

    $response = (new EnsureUserIsClubUser())->handle(
        Request::create('/api/club/test'),
        static fn (Request $request): Response => response('allowed'),
    );

    expect($response->getStatusCode())->toBe(401);
    expect($response->getContent())->toBe('{"message":"Unauthenticated."}');
});

