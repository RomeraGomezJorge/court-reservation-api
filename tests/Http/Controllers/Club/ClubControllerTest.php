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

$maxLengthString = str_repeat('a', 256);
$maxLengthUrl = 'https://'.str_repeat('a', 244).'.com';

dataset('invalid club payload data', [
    'empty organization_name' => [
        'invalidData' => ['organization_name' => ''],
        'expectedMessages' => ['El campo nombre de la organización es obligatorio.'],
    ],
    'duplicate organization_name' => [
        'invalidData' => ['organization_name' => 'Club Duplicado'],
        'expectedMessages' => ['El campo nombre de la organización ya ha sido registrado.'],
    ],
    'organization_name max length' => [
        'invalidData' => ['organization_name' => $maxLengthString],
        'expectedMessages' => ['El campo nombre de la organización no debe ser mayor que 255 caracteres.'],
    ],
    'facebook_url invalid format' => [
        'invalidData' => ['facebook_url' => 'url-invalida'],
        'expectedMessages' => ['El campo facebook url debe ser una URL válida.'],
    ],
    'facebook_url max length' => [
        'invalidData' => ['facebook_url' => $maxLengthUrl],
        'expectedMessages' => ['El campo facebook url no debe ser mayor que 255 caracteres.'],
    ],
    'instagram_url invalid format' => [
        'invalidData' => ['instagram_url' => 'url-invalida'],
        'expectedMessages' => ['El campo instagram url debe ser una URL válida.'],
    ],
    'instagram_url max length' => [
        'invalidData' => ['instagram_url' => $maxLengthUrl],
        'expectedMessages' => ['El campo instagram url no debe ser mayor que 255 caracteres.'],
    ],
    'latitude not numeric' => [
        'invalidData' => ['latitude' => 'latitud-invalida'],
        'expectedMessages' => ['El campo latitud debe ser numérico.'],
    ],
    'latitude out of range' => [
        'invalidData' => ['latitude' => 91],
        'expectedMessages' => ['El campo latitud tiene que estar entre -90 - 90.'],
    ],
    'longitude not numeric' => [
        'invalidData' => ['longitude' => 'longitud-invalida'],
        'expectedMessages' => ['El campo longitud debe ser numérico.'],
    ],
    'longitude out of range' => [
        'invalidData' => ['longitude' => 181],
        'expectedMessages' => ['El campo longitud tiene que estar entre -180 - 180.'],
    ],
    'operating_hours_additional_info not string' => [
        'invalidData' => ['operating_hours_additional_info' => ['no-valido']],
        'expectedMessages' => ['El campo información adicional de horarios debe ser una cadena de caracteres.'],
    ],
    'operating_hours_additional_info max length' => [
        'invalidData' => ['operating_hours_additional_info' => $maxLengthString],
        'expectedMessages' => ['El campo información adicional de horarios no debe ser mayor que 255 caracteres.'],
    ],
    'phone_number not string' => [
        'invalidData' => ['phone_number' => ['no-valido']],
        'expectedMessages' => ['El campo teléfono debe ser una cadena de caracteres.'],
    ],
    'phone_number max length' => [
        'invalidData' => ['phone_number' => $maxLengthString],
        'expectedMessages' => ['El campo teléfono no debe ser mayor que 255 caracteres.'],
    ],
    'reservation_policies_and_payment_terms not string' => [
        'invalidData' => ['reservation_policies_and_payment_terms' => ['no-valido']],
        'expectedMessages' => ['El campo políticas de reserva y términos de pago debe ser una cadena de caracteres.'],
    ],
    'reservation_policies_and_payment_terms max length' => [
        'invalidData' => ['reservation_policies_and_payment_terms' => $maxLengthString],
        'expectedMessages' => ['El campo políticas de reserva y términos de pago no debe ser mayor que 255 caracteres.'],
    ],
    'twitter_url invalid format' => [
        'invalidData' => ['twitter_url' => 'url-invalida'],
        'expectedMessages' => ['El campo twitter url debe ser una URL válida.'],
    ],
    'twitter_url max length' => [
        'invalidData' => ['twitter_url' => $maxLengthUrl],
        'expectedMessages' => ['El campo twitter url no debe ser mayor que 255 caracteres.'],
    ],
    'whatsapp_number not string' => [
        'invalidData' => ['whatsapp_number' => ['no-valido']],
        'expectedMessages' => ['El campo whatsapp debe ser una cadena de caracteres.'],
    ],
    'whatsapp_number max length' => [
        'invalidData' => ['whatsapp_number' => $maxLengthString],
        'expectedMessages' => ['El campo whatsapp no debe ser mayor que 255 caracteres.'],
    ],
]);

it('returns a collection of clubs for the authenticated club user', function (): void {
    [$ownedClub,$otherOwnedClub] = Club::factory()
        ->count(2)
        ->sequence(
            ['organization_name' => 'Club Propio 1'],
            ['organization_name' => 'Club Propio 2'],
        )
        ->create([
            'club_user_id' => $this->clubUser->id,
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
                'organization_name' => 'Club Propio 1',
                'is_active' => true,
            ],
            [
                'id' => $otherOwnedClub->id,
                'organization_name' => 'Club Propio 2',
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
})->with('invalid club payload data');

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
})->with('invalid club payload data');

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
