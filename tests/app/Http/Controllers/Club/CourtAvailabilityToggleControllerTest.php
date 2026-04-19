<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\CourtAvailabilityToggleController;
use App\Models\Club;
use App\Models\Court;
use App\Models\SportType;

use function Pest\Laravel\patch;

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('toggles court availability', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->create([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'is_available' => true,
    ]);

    patch(action(CourtAvailabilityToggleController::class, ['club' => $club, 'court' => $court]))
        ->assertNoContent();

    $court->refresh();
    expect($court->is_available)->toBeFalse();

    patch(action(CourtAvailabilityToggleController::class, ['club' => $club, 'court' => $court]))
        ->assertNoContent();

    $court->refresh();
    expect($court->is_available)->toBeTrue();
});

it('fails to toggle court availability when the court is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->createQuietly();
    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->create([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'is_available' => true,
    ]);

    patch(action(CourtAvailabilityToggleController::class, ['club' => $otherClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to toggle court availability when the court does not belong to the club in route', function (): void {
    $ownedClub = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherOwnedClub = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->create([
        'club_id' => $otherOwnedClub->id,
        'sport_type_id' => $sportType->id,
        'is_available' => true,
    ]);

    patch(action(CourtAvailabilityToggleController::class, ['club' => $ownedClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $court->refresh();
    expect($court->is_available)->toBeTrue();
});
