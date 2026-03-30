<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\ActiveFeatureController;
use App\Models\Feature;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('returns only active features', function (): void {
    $activeFeature = Feature::factory()->create([
        'name' => 'Iluminacion',
        'is_active' => true,
    ]);

    Feature::factory()->create([
        'name' => 'Vestuario',
        'is_active' => false,
    ]);

    get(action([ActiveFeatureController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $activeFeature->id,
                'name' => 'Iluminacion',
            ],
        ]);
});
