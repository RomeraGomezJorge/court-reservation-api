<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\CourtController;
use App\Models\Club;
use App\Models\ClubUser;
use App\Models\Court;
use App\Models\Feature;
use App\Models\SportType;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

function validCourtPayload(array $overrides = []): array
{
    $sportType = SportType::factory()->createQuietly();
    $features = Feature::factory()->count(2)->createQuietly();

    return array_merge([
        'name' => 'Cancha Central',
        'description' => 'Cancha para partidos oficiales',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [$features[0]->id, $features[1]->id],
    ], $overrides);
}

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('stores a court with default availability in true', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $payload = validCourtPayload([
        'name' => 'Cancha Norte',
    ]);

    post(action([CourtController::class, 'store'], ['club' => $club]), $payload)
        ->assertStatus(201);

    $court = Court::query()->where('name', 'Cancha Norte')->firstOrFail();

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'club_id' => $club->id,
        'name' => 'Cancha Norte',
        'is_available' => true,
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $payload['feature_ids'][0],
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $payload['feature_ids'][1],
    ]);
});

it('stores a court with duplicated name in different clubs', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherClub = Club::factory()->createQuietly();

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Compartida',
    ]);

    post(action([CourtController::class, 'store'], ['club' => $club]), [
        'name' => $court->name,
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [],
    ])->assertCreated();
});

it('fails to store a court with invalid payload', function (
    array $payloadOverrides,
    array $expectedMessages,
): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $response = post(
        action([CourtController::class, 'store'], ['club' => $club]),
        validCourtPayload($payloadOverrides),
    );

    $response
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'empty name' => [
        'payloadOverrides' => [
            'name' => '',
        ],
        'expectedMessages' => [
            'El campo nombre es obligatorio.',
        ],
    ],

    'name exceeds max length' => [
        'payloadOverrides' => [
            'name' => str_repeat('a', 101),
        ],
        'expectedMessages' => [
            'El campo nombre no debe ser mayor que 100 caracteres.',
        ],
    ],

    'name must be string' => [
        'payloadOverrides' => [
            'name' => 123,
        ],
        'expectedMessages' => [
            'El campo nombre debe ser una cadena de caracteres.',
        ],
    ],

    'description exceeds max length' => [
        'payloadOverrides' => [
            'description' => str_repeat('a', 256),
        ],
        'expectedMessages' => [
            'El campo descripción no debe ser mayor que 255 caracteres.',
        ],
    ],

    'description must be string' => [
        'payloadOverrides' => [
            'description' => 123,
        ],
        'expectedMessages' => [
            'El campo descripción debe ser una cadena de caracteres.',
        ],
    ],

    'sport type is required' => [
        'payloadOverrides' => [
            'sport_type_id' => '',
        ],
        'expectedMessages' => [
            'El campo tipo de deporte es obligatorio.',
        ],
    ],

    'sport type must be integer' => [
        'payloadOverrides' => [
            'sport_type_id' => 'invalid',
        ],
        'expectedMessages' => [
            'El campo tipo de deporte debe ser un número entero.',
        ],
    ],

    'sport type does not exist' => [
        'payloadOverrides' => [
            'sport_type_id' => 9999,
        ],
        'expectedMessages' => [
            'El campo tipo de deporte no existe.',
        ],
    ],

    'feature ids must be array' => [
        'payloadOverrides' => [
            'feature_ids' => 'invalid',
        ],
        'expectedMessages' => [
            'El campo características debe ser un conjunto.',
        ],
    ],

    'feature id must be integer' => [
        'payloadOverrides' => [
            'feature_ids' => ['invalid'],
        ],
        'expectedMessages' => [
            'La característica en la posición 1 debe ser un número.',
        ],
    ],

    'feature id does not exist' => [
        'payloadOverrides' => [
            'feature_ids' => [9999],
        ],
        'expectedMessages' => [
            'La característica en la posición 1 no existe.',
        ],
    ],
]);

it('fails to store a court with duplicate feature_ids', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $feature = Feature::factory()->createQuietly();

    $payload = validCourtPayload(['feature_ids' => [$feature->id, $feature->id]]);

    post(action([CourtController::class, 'store'], ['club' => $club]), $payload)
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => [
                'La característica seleccionada en la posición 1 está duplicada.',
                'La característica seleccionada en la posición 2 está duplicada.',
            ],
        ]);
});

it('fails to store a court with duplicate name inside the same club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Repetida',
    ]);

    post(action([CourtController::class, 'store'], ['club' => $club]), [
        'name' => 'Cancha Repetida',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [],
    ])->assertExactJson([
        'code' => 422,
        'messages' => ['El campo nombre ya ha sido registrado.'],
    ]);
});

it('fails to store a court when the club is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->createQuietly();

    post(action([CourtController::class, 'store'], ['club' => $otherClub]), validCourtPayload())
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('shows a court', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly([
        'name' => 'Padel',
    ]);

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Show',
        'description' => 'Descripcion show',
        'is_available' => true,
    ]);

    [$featureOne, $featureTwo] = Feature::factory()
        ->count(2)
        ->sequence(
            ['name' => 'Iluminacion'],
            ['name' => 'Cesped sintetico'],
        )
        ->createQuietly();

    $court->features()->sync([$featureOne->id, $featureTwo->id]);

    get(action([CourtController::class, 'show'], ['club' => $club, 'court' => $court]))
        ->assertOk()
        ->assertExactJson([
            'id' => $court->id,
            'club_id' => $club->id,
            'sport_type_id' => $sportType->id,
            'name' => 'Cancha Show',
            'description' => 'Descripcion show',
            'is_available' => true,
            'sport_type' => [
                'id' => $sportType->id,
                'name' => 'Padel',
            ],
            'features' => [
                ['id' => $featureOne->id, 'name' => 'Iluminacion'],
                ['id' => $featureTwo->id, 'name' => 'Cesped sintetico'],
            ],
        ]);
});

it('fails to show a court when the court does not belong to the club in route', function (): void {
    $ownedClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherOwnedClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherOwnedClub->id,
        'sport_type_id' => $sportType->id,
    ]);

    get(action([CourtController::class, 'show'], ['club' => $ownedClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to show a court when the club is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->createQuietly();
    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
    ]);

    get(action([CourtController::class, 'show'], ['club' => $otherClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('updates a court and syncs features', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();
    $newSportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Base',
    ]);

    [$oldFeature] = Feature::factory()->count(1)->createQuietly();
    [$newFeatureOne, $newFeatureTwo] = Feature::factory()->count(2)->createQuietly();

    $court->features()->sync([$oldFeature->id]);

    put(action([CourtController::class, 'update'], ['club' => $club, 'court' => $court]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion actualizada',
        'sport_type_id' => $newSportType->id,
        'feature_ids' => [$newFeatureOne->id, $newFeatureTwo->id],
    ])->assertNoContent();

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'name' => 'Cancha Actualizada',
        'sport_type_id' => $newSportType->id,
    ]);

    $this->assertDatabaseMissing('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $oldFeature->id,
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $newFeatureOne->id,
    ]);

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $newFeatureTwo->id,
    ]);
});

it('fails to update a court with duplicate name inside the same club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Duplicada',
    ]);

    $courtToUpdate = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Otra Cancha',
    ]);

    put(action([CourtController::class, 'update'], [
        'club' => $club,
        'court' => $courtToUpdate,
    ]), [
        'name' => 'Cancha Duplicada',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [],
    ])->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => [
                'El campo nombre ya ha sido registrado.',
            ],
        ]);
});

it('updates a court with duplicated name from another club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherClub = Club::factory()->createQuietly();

    $sportType = SportType::factory()->createQuietly();

    Court::factory()->createQuietly([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Compartida',
    ]);

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Original',
    ]);

    put(action([CourtController::class, 'update'], [
        'club' => $club,
        'court' => $court,
    ]), [
        'name' => 'Cancha Compartida',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [],
    ])->assertNoContent();
});

it('fails to update a court with duplicate feature_ids', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $feature = Feature::factory()->createQuietly();

    put(action([CourtController::class, 'update'], [
        'club' => $club,
        'court' => $court,
    ]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [$feature->id, $feature->id],
    ])->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => [
                'La característica seleccionada en la posición 1 está duplicada.',
                'La característica seleccionada en la posición 2 está duplicada.',
            ],
        ]);
});

it('fails to update a court with invalid payload', function (
    array $payloadOverrides,
    array $expectedMessages,
): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Original',
    ]);

    $response = put(
        action([CourtController::class, 'update'], [
            'club' => $club,
            'court' => $court,
        ]),
        validCourtPayload($payloadOverrides),
    );

    $response
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'empty name' => [
        'payloadOverrides' => [
            'name' => '',
        ],
        'expectedMessages' => [
            'El campo nombre es obligatorio.',
        ],
    ],

    'name exceeds max length' => [
        'payloadOverrides' => [
            'name' => str_repeat('a', 101),
        ],
        'expectedMessages' => [
            'El campo nombre no debe ser mayor que 100 caracteres.',
        ],
    ],

    'name must be string' => [
        'payloadOverrides' => [
            'name' => 123,
        ],
        'expectedMessages' => [
            'El campo nombre debe ser una cadena de caracteres.',
        ],
    ],

    'description exceeds max length' => [
        'payloadOverrides' => [
            'description' => str_repeat('a', 256),
        ],
        'expectedMessages' => [
            'El campo descripción no debe ser mayor que 255 caracteres.',
        ],
    ],

    'description must be string' => [
        'payloadOverrides' => [
            'description' => 123,
        ],
        'expectedMessages' => [
            'El campo descripción debe ser una cadena de caracteres.',
        ],
    ],

    'sport type is required' => [
        'payloadOverrides' => [
            'sport_type_id' => '',
        ],
        'expectedMessages' => [
            'El campo tipo de deporte es obligatorio.',
        ],
    ],

    'sport type must be integer' => [
        'payloadOverrides' => [
            'sport_type_id' => 'invalid',
        ],
        'expectedMessages' => [
            'El campo tipo de deporte debe ser un número entero.',
        ],
    ],

    'sport type does not exist' => [
        'payloadOverrides' => [
            'sport_type_id' => 9999,
        ],
        'expectedMessages' => [
            'El campo tipo de deporte no existe.',
        ],
    ],

    'feature ids must be array' => [
        'payloadOverrides' => [
            'feature_ids' => 'invalid',
        ],
        'expectedMessages' => [
            'El campo características debe ser un conjunto.',
        ],
    ],

    'feature id must be integer' => [
        'payloadOverrides' => [
            'feature_ids' => ['invalid'],
        ],
        'expectedMessages' => [
            'La característica en la posición 1 debe ser un número.',
        ],
    ],

    'feature id does not exist' => [
        'payloadOverrides' => [
            'feature_ids' => [9999],
        ],
        'expectedMessages' => [
            'La característica en la posición 1 no existe.',
        ],
    ],
]);

it('fails to update a court when the court does not belong to the club in route', function (): void {
    $ownedClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherOwnedClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherOwnedClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Inalterable',
    ]);

    put(action([CourtController::class, 'update'], ['club' => $ownedClub, 'court' => $court]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion actualizada',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [],
    ])->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'name' => 'Cancha Inalterable',
    ]);
});

it('fails to update a court when the club is not owned by authenticated user', function (): void {
    $otherClub = Club::factory()->createQuietly();
    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Inalterable',
    ]);

    put(action([CourtController::class, 'update'], ['club' => $otherClub, 'court' => $court]), [
        'name' => 'Cancha Actualizada',
        'description' => 'Descripcion actualizada',
        'sport_type_id' => $sportType->id,
        'feature_ids' => [],
    ])->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertDatabaseHas('courts', [
        'id' => $court->id,
        'name' => 'Cancha Inalterable',
    ]);
});

it('updates a court without features payload keeping existing ones', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();
    $feature = Feature::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
    ]);

    $court->features()->sync([$feature->id]);

    put(action([CourtController::class, 'update'], ['club' => $club, 'court' => $court]), [
        'name' => 'Cancha sin cambiar features',
        'description' => 'Descripcion',
        'sport_type_id' => $sportType->id,
    ])->assertNoContent();

    $this->assertDatabaseHas('court_feature', [
        'court_id' => $court->id,
        'feature_id' => $feature->id,
    ]);
});

it('deletes a court with soft delete and renames it to keep unique constraints available', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $club->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Eliminada',
    ]);

    delete(action([CourtController::class, 'destroy'], ['club' => $club, 'court' => $court]))
        ->assertNoContent();

    $court->refresh();

    $this->assertSoftDeleted($court);
    expect($court->name)->toBe("Cancha Eliminada (deleted #{$court->id})");
});

it('fails to delete a court when the court does not belong to the club in route', function (): void {
    $ownedClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherOwnedClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherOwnedClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Sin Eliminar',
    ]);

    delete(action([CourtController::class, 'destroy'], ['club' => $ownedClub, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertNotSoftDeleted($court);
});

it('fails to delete a court when the club is not owned by authenticated user', function (): void {
    $club = Club::factory()->createQuietly(
        ['club_user_id' => $this->clubUser->id]
    );

    $otherClubUser = ClubUser::factory()->createQuietly();
    $otherClub = Club::factory()->createQuietly([
        'club_user_id' => $otherClubUser->id,
    ]);

    $sportType = SportType::factory()->createQuietly();

    $court = Court::factory()->createQuietly([
        'club_id' => $otherClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Sin Eliminar',
    ]);

    delete(action([CourtController::class, 'destroy'], ['club' => $club, 'court' => $court]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,

            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertNotSoftDeleted($court);
});
