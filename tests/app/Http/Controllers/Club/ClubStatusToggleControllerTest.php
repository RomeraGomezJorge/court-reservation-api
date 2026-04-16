<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\ClubStatusToggleController;
use App\Models\Club;

use function Pest\Laravel\patch;

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('toggles club status from active to inactive', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'is_active' => true,
    ]);

    patch(action([ClubStatusToggleController::class], $club))
        ->assertNoContent();

    $club->refresh();
    expect($club->is_active)->toBeFalse();
});

it('toggles club status from inactive to active', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'is_active' => false,
    ]);

    patch(action([ClubStatusToggleController::class], $club))
        ->assertNoContent();

    $club->refresh();
    expect($club->is_active)->toBeTrue();
});

it('fails to toggle a club status that does not exist', function (): void {
    patch(action([ClubStatusToggleController::class], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to toggle a club status that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->create();

    patch(action([ClubStatusToggleController::class], $club))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});
