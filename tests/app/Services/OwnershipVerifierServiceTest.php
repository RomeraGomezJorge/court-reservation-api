<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Models\Club;
use App\Services\OwnershipVerifierService;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

mutates(OwnershipVerifierService::class);

it('throws runtime exception when authenticated user is not a club user', function (): void {
    actingAsUser();

    $club = Club::factory()->create();

    $service = resolve(OwnershipVerifierService::class);

    $this->expectException(RuntimeException::class);
    $this->expectExceptionMessage('The authenticated user is not an instance of ClubUser');

    $service->handle($club);
});

it('aborts with not found when authenticated club user does not own model', function (): void {
    actingAsClubUser();

    $club = Club::factory()->create();

    $service = resolve(OwnershipVerifierService::class);

    try {
        $service->handle($club);
        $this->fail('Expected NotFoundHttpException was not thrown.');
    } catch (NotFoundHttpException $notFoundHttpException) {
        expect($notFoundHttpException->getStatusCode())->toBe(404);
        expect($notFoundHttpException->getMessage())->toBe('El recurso no se ha encontrado.');
    }
});
