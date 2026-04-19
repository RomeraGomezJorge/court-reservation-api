<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\SportTypeStatusToggleController;
use App\Models\SportType;

use function Pest\Laravel\patch;

beforeEach(function (): void {
    $this->user = actingAsUser();
});

it('toggles sport type status', function (): void {
    $sportType = SportType::factory()->createQuietly([
        'is_active' => true,
    ]);

    patch(action([SportTypeStatusToggleController::class], $sportType))
        ->assertNoContent();

    $sportType->refresh();
    expect($sportType->is_active)->toBeFalse();

    patch(action([SportTypeStatusToggleController::class], $sportType))
        ->assertNoContent();

    $sportType->refresh();
    expect($sportType->is_active)->toBeTrue();
});

it('fails to toggle a sport type status that does not exist', function (): void {
    patch(action([SportTypeStatusToggleController::class], 999))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});
