<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\ActiveSportTypeController;
use App\Models\SportType;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('returns only active sport types', function (): void {
    $activeSportType = SportType::factory()->create([
        'name' => 'Padel',
        'is_active' => true,
    ]);

    SportType::factory()->create([
        'name' => 'Tenis',
        'is_active' => false,
    ]);

    get(action([ActiveSportTypeController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $activeSportType->id,
                'name' => 'Padel',
            ],
        ]);
});
