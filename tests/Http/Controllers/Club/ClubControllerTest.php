<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\ClubController;
use App\Models\Club;
use App\Models\ClubUser;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('returns a collection of clubs for the authenticated club user', function (): void {
    $ownedClub = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Propio',
        'is_active' => true,
    ]);

    Club::factory()->create([
        'club_user_id' => ClubUser::factory()->create()->id,
        'organization_name' => 'Club Ajeno',
    ]);

    get(action([ClubController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $ownedClub->id,
                'organization_name' => 'Club Propio',
                'is_active' => true,
            ],
        ]);
});

it('stores a club', function (): void {
    $payload = [
        'address_city' => 'Rosario',
        'address_country' => 'Argentina',
        'address_postal_code' => '2000',
        'address_state' => 'Santa Fe',
        'address_street' => 'Av. Siempre Viva 123',
        'description' => 'Club con canchas de futbol',
        'organization_name' => 'Club Nuevo',
    ];
    post(action([ClubController::class, 'store']), $payload)->assertStatus(201);

    $this->assertDatabaseHas('clubs', [
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Nuevo',
        'is_active' => true,
    ]);
});

it('fails to store a club with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['organization_name'] ?? null) === 'Club Duplicado') {
        Club::factory()->create(['organization_name' => 'Club Duplicado']);
    }

    $clubData = [
        'address_city' => 'Rosario',
        'address_country' => 'Argentina',
        'address_postal_code' => '2000',
        'address_state' => 'Santa Fe',
        'address_street' => 'Av. Siempre Viva 123',
        'description' => 'Club con canchas de futbol',
        'organization_name' => 'Club Valido',
    ];

    post(action([ClubController::class, 'store']), array_merge($clubData, $invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'empty organization_name' => [
        'invalidData' => ['organization_name' => ''],
        'expectedMessages' => ['El campo organization name es obligatorio.'],
    ],
    'duplicate organization_name' => [
        'invalidData' => ['organization_name' => 'Club Duplicado'],
        'expectedMessages' => ['El campo organization name ya ha sido registrado.'],
    ],
]);

it('shows a club', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Show',
        'is_active' => true,
    ]);

    get(action([ClubController::class, 'show'], $club))
        ->assertOk()
        ->assertExactJson([
            'id' => $club->id,
            'club_user_id' => $club->club_user_id,
            'address_city' => $club->address_city,
            'address_country' => $club->address_country,
            'address_postal_code' => $club->address_postal_code,
            'address_state' => $club->address_state,
            'address_street' => $club->address_street,
            'description' => $club->description,
            'facebook_url' => $club->facebook_url,
            'instagram_url' => $club->instagram_url,
            'latitude' => $club->latitude,
            'longitude' => $club->longitude,
            'operating_hours_additional_info' => $club->operating_hours_additional_info,
            'organization_name' => $club->organization_name,
            'phone_number' => $club->phone_number,
            'reservation_policies_and_payment_terms' => $club->reservation_policies_and_payment_terms,
            'twitter_url' => $club->twitter_url,
            'whatsapp_number' => $club->whatsapp_number,
            'is_active' => $club->is_active,
        ]);
});

it('fails to show a club that does not exist', function (): void {
    get(action([ClubController::class, 'show'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Club] 999'],
        ]);
});

it('fails to show a club that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->create();

    get(action([ClubController::class, 'show'], $club))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso Club  no se ha encontrado.'],
        ]);
});

it('updates a club', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Base',
    ]);

    put(action([ClubController::class, 'update'], $club), [
        'address_city' => 'Cordoba',
        'address_country' => 'Argentina',
        'address_postal_code' => '5000',
        'address_state' => 'Cordoba',
        'address_street' => 'Calle Falsa 742',
        'description' => 'Club actualizado',
        'organization_name' => 'Club Actualizado',
    ])->assertNoContent();

    $this->assertDatabaseHas('clubs', [
        'id' => $club->id,
        'organization_name' => 'Club Actualizado',
        'address_city' => 'Cordoba',
    ]);
});

it('fails to update a club that does not exist', function (): void {
    put(action([ClubController::class, 'update'], 999), [
        'address_city' => 'Cordoba',
        'address_country' => 'Argentina',
        'address_postal_code' => '5000',
        'address_state' => 'Cordoba',
        'address_street' => 'Calle Falsa 742',
        'description' => 'Club actualizado',
        'organization_name' => 'Club Actualizado',
    ])
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Club] 999'],
        ]);
});

it('fails to update a club with invalid data', function (array $invalidData, array $expectedMessages): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Base',
    ]);

    if (($invalidData['organization_name'] ?? null) === 'Club Duplicado') {
        Club::factory()->create(['organization_name' => 'Club Duplicado']);
    }

    put(action([ClubController::class, 'update'], $club), array_merge([
        'address_city' => 'Rosario',
        'address_country' => 'Argentina',
        'address_postal_code' => '2000',
        'address_state' => 'Santa Fe',
        'address_street' => 'Av. Siempre Viva 123',
        'description' => 'Club con canchas de futbol',
        'organization_name' => 'Club Valido',
    ], $invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'empty organization_name' => [
        'invalidData' => ['organization_name' => ''],
        'expectedMessages' => ['El campo organization name es obligatorio.'],
    ],
    'duplicate organization_name' => [
        'invalidData' => ['organization_name' => 'Club Duplicado'],
        'expectedMessages' => ['El campo organization name ya ha sido registrado.'],
    ],
]);

it('fails to update a club that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->create();

    put(action([ClubController::class, 'update'], $club), [
        'address_city' => 'Cordoba',
        'address_country' => 'Argentina',
        'address_postal_code' => '5000',
        'address_state' => 'Cordoba',
        'address_street' => 'Calle Falsa 742',
        'description' => 'Club actualizado',
        'organization_name' => 'Club Actualizado',
    ])
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso Club  no se ha encontrado.'],
        ]);
});

it('deletes a club', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    delete(action([ClubController::class, 'destroy'], $club))
        ->assertNoContent();

    $this->assertDatabaseMissing('clubs', ['id' => $club->id]);
});

it('fails to delete a club that does not exist', function (): void {
    delete(action([ClubController::class, 'destroy'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Club] 999'],
        ]);
});

it('fails to delete a club that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->create();

    delete(action([ClubController::class, 'destroy'], $club))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso Club  no se ha encontrado.'],
        ]);
});

