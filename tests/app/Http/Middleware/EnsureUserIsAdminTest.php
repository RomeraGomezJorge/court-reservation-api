<?php

declare(strict_types=1);

namespace Tests\Http\Middleware;

use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

it('allows authenticated admin users', function (): void {
    actingAsUser();

    $response = (new EnsureUserIsAdmin())->handle(
        Request::create('/api/admin/test'),
        static fn (Request $request): Response => response('allowed'),
    );

    expect($response->getStatusCode())->toBe(200);
    expect($response->getContent())->toBe('allowed');
});

it('rejects club users as admin users', function (): void {
    actingAsClubUser();

    $response = (new EnsureUserIsAdmin())->handle(
        Request::create('/api/admin/test'),
        static fn (Request $request): Response => response('allowed'),
    );

    expect($response->getStatusCode())->toBe(401);
    expect($response->getContent())->toBe('{"message":"Unauthenticated."}');
});

