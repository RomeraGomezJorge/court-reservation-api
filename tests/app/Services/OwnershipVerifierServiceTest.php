<?php

declare(strict_types=1);

namespace Tests\Services;

use App\Models\Club;
use App\Services\OwnershipVerifierService;
use Illuminate\Support\Facades\Lang;
use ReflectionMethod;
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
        expect($notFoundHttpException->getMessage())->toBe('El recurso Club no se ha encontrado.');
    }
});

it('returns translated model-specific message when key exists', function (): void {
    $service = resolve(OwnershipVerifierService::class);

    Lang::addLines([
        'club.not_found' => 'No se encontro el club por traduccion especifica.',
    ], 'es');

    $message = new ReflectionMethod(OwnershipVerifierService::class, 'getNotFoundMessage');

    expect($message->invoke($service, 'club'))->toBe('No se encontro el club por traduccion especifica.');
});

it('returns fallback message using formatted model name when key does not exist', function (): void {
    $service = resolve(OwnershipVerifierService::class);

    $message = new ReflectionMethod(OwnershipVerifierService::class, 'getNotFoundMessage');

    expect($message->invoke($service, 'court_price_rule'))
        ->toBe('El recurso Court price rule no se ha encontrado.');
});

it('replaces the resource placeholder in english fallback message', function (): void {
    $service = resolve(OwnershipVerifierService::class);

    $originalLocale = app()->getLocale();

    try {
        app()->setLocale('en');

        $message = new ReflectionMethod(OwnershipVerifierService::class, 'getNotFoundMessage');

        expect($message->invoke($service, 'court'))
            ->toBe('The specified Court was not found.');
    } finally {
        app()->setLocale($originalLocale);
    }
});
