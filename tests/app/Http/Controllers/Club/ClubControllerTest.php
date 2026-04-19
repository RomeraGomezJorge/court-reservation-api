<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\ClubServicesType;
use App\Enums\WorkingDays;
use App\Http\Controllers\Club\ClubController;
use App\Models\Club;
use App\Models\ClubService;
use App\Models\ClubUser;
use App\Models\ClubWorkingDay;
use App\Models\Court;
use App\Models\SportType;
use Illuminate\Support\Facades\Date;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

function validWorkingDays(): array
{
    return [
        ['day' => 'monday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'tuesday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'wednesday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'thursday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'friday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'saturday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'sunday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ['day' => 'holiday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
    ];
}

function validClubPayload(array $overrides = []): array
{
    return array_merge([
        'address_city' => 'Rosario',
        'address_country' => 'Argentina',
        'address_postal_code' => '2000',
        'address_state' => 'Santa Fe',
        'address_street' => 'Av. Siempre Viva 123',
        'description' => 'Club con canchas de futbol',
        'organization_name' => 'Club Valido',
        'services' => ['wifi', 'parking'],
        'working_days' => validWorkingDays(),
    ], $overrides);
}

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
    'working_days invalid enum day' => [
        'invalidData' => ['working_days' => [['day' => 'funday', 'opening_hour' => '09:00', 'closing_hour' => '01:00']]],
        'expectedMessages' => ['El campo día no está en la lista de valores permitidos.'],
    ],
    'working_days invalid opening hour format' => [
        'invalidData' => ['working_days' => [['day' => 'monday', 'opening_hour' => 'aa:bb', 'closing_hour' => 'aa:bb']]],
        'expectedMessages' => [
            'El campo hora de apertura debe coincidir con el formato H:i.',
            'El formato de hora de lunes es inválido.',
            'El campo hora de cierre debe coincidir con el formato H:i.',

        ],
    ],
    'working_days schedule too wide' => [
        'invalidData' => ['working_days' => [['day' => 'monday', 'opening_hour' => '09:00', 'closing_hour' => '04:00']]],
        'expectedMessages' => ['El horario para lunes es demasiado amplio.'],
    ],
    'working_days missing opening hour' => [
        'invalidData' => ['working_days' => [['day' => 'monday', 'closing_hour' => '01:00']]],
        'expectedMessages' => ['El campo hora de apertura es obligatorio.'],
    ],
    'working_days duplicate day' => [
        'invalidData' => [
            'working_days' => [
                ['day' => 'monday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
                ['day' => 'monday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
            ],
        ],
        'expectedMessages' => [
            'El campo día contiene un valor duplicado.',
            'El campo día contiene un valor duplicado.',
        ],
    ],
    'services invalid enum value' => [
        'invalidData' => ['services' => ['invalid-service']],
        'expectedMessages' => ['El campo servicio no está en la lista de valores permitidos.'],
    ],
    'services duplicate value' => [
        'invalidData' => ['services' => ['wifi', 'wifi']],
        'expectedMessages' => [
            'El campo servicio contiene un valor duplicado.',
            'El campo servicio contiene un valor duplicado.',
        ],
    ],
]);

it('returns a collection of clubs for the authenticated club user', function (): void {
    [$ownedClub, $otherOwnedClub] = Club::factory()
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
        'club_user_id' => ClubUser::factory()->createQuietly()->id,
        'organization_name' => 'Club Ajeno',
    ]);

    $sportType = SportType::factory()->createQuietly();

    $firstCourt = Court::factory()->create([
        'club_id' => $ownedClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Principal',
        'is_available' => true,
    ]);

    $secondCourt = Court::factory()->create([
        'club_id' => $ownedClub->id,
        'sport_type_id' => $sportType->id,
        'name' => 'Cancha Auxiliar',
        'is_available' => false,
    ]);

    get(action([ClubController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $ownedClub->id,
                'organization_name' => 'Club Propio 1',
                'is_active' => true,
                'courts' => [

                    [
                        'id' => $secondCourt->id,
                        'name' => 'Cancha Auxiliar',
                        'is_available' => false,
                    ],
                    [
                        'id' => $firstCourt->id,
                        'name' => 'Cancha Principal',
                        'is_available' => true,
                    ],
                ],
            ],
            [
                'id' => $otherOwnedClub->id,
                'organization_name' => 'Club Propio 2',
                'is_active' => true,
                'courts' => [],
            ],
        ]);
});

it('stores a club', function (): void {
    $payload = validClubPayload([
        'organization_name' => 'Club Nuevo',
    ]);

    post(action([ClubController::class, 'store']), $payload)
        ->assertStatus(201);

    $club = Club::query()->where('organization_name', 'Club Nuevo')->firstOrFail();

    $this->assertDatabaseHas('clubs', [
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Nuevo',
        'is_active' => true,
    ]);

    $this->assertDatabaseHas('club_working_days', [
        'club_id' => $club->id,
        'day' => 'holiday',
        'opening_hour' => '09:00:00',
        'closing_hour' => '01:00:00',
    ]);

    $this->assertDatabaseHas('club_services', [
        'club_id' => $club->id,
        'type' => 'wifi',
    ]);

    $this->assertDatabaseHas('club_services', [
        'club_id' => $club->id,
        'type' => 'parking',
    ]);
});

it('fails to store a club with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['organization_name'] ?? null) === 'Club Duplicado') {
        Club::factory()->create(['organization_name' => 'Club Duplicado']);
    }

    post(action([ClubController::class, 'store']), validClubPayload($invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with('invalid club payload data');

it('fails to store a club when date parser returns non carbon instances', function (): void {
    Date::shouldReceive('createFromFormat')
        ->twice()
        ->andReturn(false);

    post(action([ClubController::class, 'store']), validClubPayload([
        'organization_name' => 'Club Fecha No Carbon',
        'working_days' => [
            ['day' => 'monday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ],
    ]))
        ->assertUnprocessable()
        ->assertExactJson([
            'code' => 422,
            'messages' => ['El formato de hora de lunes es inválido.'],
        ]);
});

it('shows a club', function (): void {

    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Show',
        'is_active' => true,
    ]);

    [$clubService, $otherOwnedClub] = ClubService::factory()
        ->count(2)
        ->sequence(
            ['type' => ClubServicesType::Wifi->value],
            ['type' => ClubServicesType::FirstAid->value],
        )->create([
            'club_id' => $club->id,
        ]);

    [$workingDay, $otherWorkingDay] = ClubWorkingDay::factory()
        ->count(2)
        ->sequence(
            ['day' => WorkingDays::Monday, 'opening_hour' => '09:00:00', 'closing_hour' => '01:00:00'],
            ['day' => WorkingDays::Tuesday, 'opening_hour' => '09:00:00', 'closing_hour' => '01:00:00'],
        )->create([
            'club_id' => $club->id,
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
            'working_days' => [

                [
                    'day' => $workingDay->day->value,
                    'opening_hour' => '09:00',
                    'closing_hour' => '01:00',
                ],
                [
                    'day' => $otherWorkingDay->day->value,
                    'opening_hour' => '09:00',
                    'closing_hour' => '01:00',
                ],

            ],
            'services' => [
                [
                    'id' => $otherOwnedClub->id,
                    'type' => $otherOwnedClub->type->value,
                    'label' => $otherOwnedClub->type->label(),
                    'icon' => $otherOwnedClub->type->getIcon(),
                ],
                [
                    'id' => $clubService->id,
                    'type' => $clubService->type->value,
                    'label' => $clubService->type->label(),
                    'icon' => $clubService->type->getIcon(),
                ],
            ],
        ]);
});

it('fails to show a club that does not exist', function (): void {
    get(action([ClubController::class, 'show'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to show a club that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->createQuietly();

    get(action([ClubController::class, 'show'], $club))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('updates a club', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Base',
    ]);

    $club->workingDays()->createMany([
        ['day' => 'monday', 'opening_hour' => '08:00', 'closing_hour' => '01:00'],
    ]);

    $club->services()->createMany([
        ['type' => ClubServicesType::Wifi->value],
    ]);

    put(action([ClubController::class, 'update'], $club), validClubPayload([
        'address_city' => 'Cordoba',
        'address_state' => 'Cordoba',
        'address_street' => 'Calle Falsa 742',
        'organization_name' => 'Club Actualizado',
        'services' => [ClubServicesType::Restaurant->value, ClubServicesType::Tournaments->value],
    ]))->assertNoContent();

    $this->assertDatabaseHas('clubs', [
        'id' => $club->id,
        'organization_name' => 'Club Actualizado',
        'address_city' => 'Cordoba',
    ]);

    $this->assertDatabaseHas('club_working_days', [
        'club_id' => $club->id,
        'day' => 'holiday',
        'opening_hour' => '09:00:00',
        'closing_hour' => '01:00:00',
    ]);

    $this->assertDatabaseMissing('club_services', [
        'club_id' => $club->id,
        'type' => ClubServicesType::Wifi->value,
    ]);

    $this->assertDatabaseHas('club_services', [
        'club_id' => $club->id,
        'type' => ClubServicesType::Restaurant->value,
    ]);

    $this->assertDatabaseHas('club_services', [
        'club_id' => $club->id,
        'type' => ClubServicesType::Tournaments->value,
    ]);
});

it('updates a club without working days payload', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Base',
    ]);

    $club->workingDays()->createMany([
        ['day' => 'monday', 'opening_hour' => '08:00', 'closing_hour' => '01:00'],
    ]);

    $club->services()->createMany([
        ['type' => ClubServicesType::Wifi->value],
    ]);

    $payload = validClubPayload([
        'address_city' => 'Mendoza',
        'organization_name' => 'Club Sin Cambiar Horarios',
    ]);

    unset($payload['working_days']);

    put(action([ClubController::class, 'update'], $club), $payload)
        ->assertNoContent();

    $this->assertDatabaseHas('clubs', [
        'id' => $club->id,
        'organization_name' => 'Club Sin Cambiar Horarios',
        'address_city' => 'Mendoza',
    ]);

    $this->assertDatabaseHas('club_working_days', [
        'club_id' => $club->id,
        'day' => 'monday',
        'opening_hour' => '08:00:00',
        'closing_hour' => '01:00:00',
    ]);

    $this->assertDatabaseHas('club_services', [
        'club_id' => $club->id,
        'type' => ClubServicesType::Wifi->value,
    ]);
});

it('updates a club clearing services', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
    ]);

    $club->services()->createMany([
        ['type' => ClubServicesType::Wifi->value],
        ['type' => ClubServicesType::Parking->value],
    ]);

    put(action([ClubController::class, 'update'], $club), validClubPayload([
        'organization_name' => 'Club Limpia Servicios',
        'services' => [],
    ]))->assertNoContent();

    $this->assertDatabaseMissing('club_services', [
        'club_id' => $club->id,
        'type' => ClubServicesType::Wifi->value,
    ]);

    $this->assertDatabaseMissing('club_services', [
        'club_id' => $club->id,
        'type' => ClubServicesType::Parking->value,
    ]);
});

it('fails to update a club that does not exist', function (): void {
    put(action([ClubController::class, 'update'], 999), validClubPayload([
        'address_city' => 'Cordoba',
        'address_state' => 'Cordoba',
        'address_street' => 'Calle Falsa 742',
        'organization_name' => 'Club Actualizado',
    ]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
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

    put(action([ClubController::class, 'update'], $club), validClubPayload($invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with('invalid club payload data');

it('fails to update a club when date parser returns non carbon instances', function (): void {
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => 'Club Base',
    ]);

    Date::shouldReceive('createFromFormat')
        ->twice()
        ->andReturn(false);

    put(action([ClubController::class, 'update'], $club), validClubPayload([
        'organization_name' => 'Club Fecha No Carbon Actualiza',
        'working_days' => [
            ['day' => 'monday', 'opening_hour' => '09:00', 'closing_hour' => '01:00'],
        ],
    ]))
        ->assertUnprocessable()
        ->assertExactJson([
            'code' => 422,
            'messages' => ['El formato de hora de lunes es inválido.'],
        ]);
});

it('fails to update a club that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->createQuietly();

    put(action([ClubController::class, 'update'], $club), validClubPayload([
        'address_city' => 'Cordoba',
        'address_state' => 'Cordoba',
        'address_street' => 'Calle Falsa 742',
        'organization_name' => 'Club Actualizado',
    ]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('deletes a club', function (): void {
    $organizationName = 'Club Eliminado';
    $club = Club::factory()->create([
        'club_user_id' => $this->clubUser->id,
        'organization_name' => $organizationName,
    ]);

    delete(action([ClubController::class, 'destroy'], $club))
        ->assertNoContent();

    $club->refresh();
    $this->assertSoftDeleted($club);
    $this->assertNotEquals($organizationName, $club->organization_name);
    $this->assertEquals($club->organization_name, "Club Eliminado (deleted #{$club->id})");
});

it('fails to delete a club that does not exist', function (): void {
    delete(action([ClubController::class, 'destroy'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to delete a club that is not owned by the authenticated club user', function (): void {
    $club = Club::factory()->createQuietly();

    delete(action([ClubController::class, 'destroy'], $club))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});
