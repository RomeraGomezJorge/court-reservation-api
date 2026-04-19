<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\SportTypeController;
use App\Models\SportType;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = actingAsUser();
});

it('stores a sport type', function (): void {
    $sportTypeData = [
        'name' => 'Paddle',
    ];

    post(action([SportTypeController::class, 'store']), $sportTypeData)
        ->assertStatus(201);

    $this->assertDatabaseHas('sport_types', [
        'name' => 'Paddle',
        'is_active' => true,
    ]);
});

it('fails to store a sport type with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['name'] ?? null) === 'Sport Type Duplicate') {
        SportType::query()->create(['name' => 'Sport Type Duplicate', 'is_active' => true]);
    }

    $serviceData = [
        'name' => 'Sport Type Valid',
    ];

    post(action([SportTypeController::class, 'store']), array_merge($serviceData, $invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

})->with([
    'empty name' => [
        'invalidData' => ['name' => ''],
        'expectedMessages' => ['El campo nombre es obligatorio.'],
    ],
    'long name' => [
        'invalidData' => ['name' => str_repeat('a', 256)],
        'expectedMessages' => ['El campo nombre no debe ser mayor que 255 caracteres.'],
    ],
    'duplicate name' => [
        'invalidData' => ['name' => 'Sport Type Duplicate'],
        'expectedMessages' => ['El campo nombre ya ha sido registrado.'],
    ],
]);

it('updates a sport type', function (): void {
    $service = SportType::query()->create([
        'name' => 'Sport Type base',
        'is_active' => true,
    ]);

    put(action([SportTypeController::class, 'update'], $service->id), [
        'name' => 'Sport Type Updated',
    ])->assertNoContent();

    $this->assertDatabaseHas('sport_types', [
        'id' => $service->id,
        'name' => 'Sport Type Updated',
    ]);
});

it('fails to update a sport type that does not exist', function (): void {
    put(action([SportTypeController::class, 'update'], 999), [
        'name' => 'Sport Type',
    ])
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to update a sport type with invalid data', function (array $invalidData, array $expectedMessages): void {
    $service = SportType::query()->create([
        'name' => 'Sport Type base',
        'is_active' => true,
    ]);

    if (($invalidData['name'] ?? null) === 'Sport Type Duplicated') {
        SportType::query()->create(['name' => 'Sport Type Duplicated', 'is_active' => true]);
    }

    put(action([SportTypeController::class, 'update'], $service->id), array_merge([
        'name' => 'Servicio valido',
    ], $invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

})->with([
    'empty name' => [
        'invalidData' => ['name' => ''],
        'expectedMessages' => ['El campo nombre es obligatorio.'],
    ],
    'long name' => [
        'invalidData' => ['name' => str_repeat('a', 256)],
        'expectedMessages' => ['El campo nombre no debe ser mayor que 255 caracteres.'],
    ],
    'duplicate name' => [
        'invalidData' => ['name' => 'Sport Type Duplicated'],
        'expectedMessages' => ['El campo nombre ya ha sido registrado.'],
    ],
]);

it('deletes a sport type', function (): void {
    $service = SportType::query()->create([
        'name' => 'Sport Type to delete',
        'is_active' => true,
    ]);

    delete(action([SportTypeController::class, 'destroy'], $service->id))
        ->assertNoContent();

    $this->assertDatabaseMissing('sport_types', ['id' => $service->id]);
});

it('fails to delete a sport type that does not exist', function (): void {
    delete(action([SportTypeController::class, 'destroy'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('shows a sport type', function (): void {
    $service = SportType::query()->create([
        'name' => 'Sport type show',
        'is_active' => true,
    ]);

    get(action([SportTypeController::class, 'show'], $service->id))
        ->assertOk()
        ->assertExactJson([
            'id' => $service->id,
            'name' => 'Sport type show',
            'is_active' => true,
        ]);
});

it('fails to show a sport type that does not exist', function (): void {
    get(action([SportTypeController::class, 'show'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('returns a collection of sport types', function (): void {
    [$firstSportType, $secondSportType] = SportType::factory()
        ->count(2)
        ->sequence(
            ['name' => 'Paddle', 'is_active' => true],
            ['name' => 'Tennis', 'is_active' => false],
        )->createQuietly();

    get(action([SportTypeController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $firstSportType->id,
                'name' => 'Paddle',
                'is_active' => true,
            ],
            [
                'id' => $secondSportType->id,
                'name' => 'Tennis',
                'is_active' => false,
            ],
        ]);
});
