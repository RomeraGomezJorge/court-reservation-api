<?php

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ServiceController;
use App\Models\Service;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = actingAsUser();
});

it('stores a service', function (): void {
    $serviceData = [
        'name' => 'Cancha techada',
    ];

    post(action([ServiceController::class, 'store']), $serviceData)
        ->assertStatus(201);

    $this->assertDatabaseHas('services', [
        'name' => 'Cancha techada',
    ]);
});

it('fails to store a service with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['name'] ?? null) === 'Servicio duplicado') {
        Service::query()->create(['name' => 'Servicio duplicado']);
    }

    $serviceData = [
        'name' => 'Servicio valido',
    ];

    post(action([ServiceController::class, 'store']), array_merge($serviceData, $invalidData))
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
        'invalidData' => ['name' => 'Servicio duplicado'],
        'expectedMessages' => ['El campo nombre ya ha sido registrado.'],
    ],
]);

it('updates a service', function (): void {
    $service = Service::query()->create([
        'name' => 'Servicio base',
    ]);

    put(action([ServiceController::class, 'update'], $service->id), [
        'name' => 'Servicio actualizado',
    ])->assertNoContent();

    $this->assertDatabaseHas('services', [
        'id' => $service->id,
        'name' => 'Servicio actualizado',
    ]);
});

it('fails to update a service that does not exist', function (): void {
    put(action([ServiceController::class, 'update'], 999), [
        'name' => 'Servicio cualquiera',
    ])
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Service] 999'],
        ]);
});

it('fails to update a service with invalid data', function (array $invalidData, array $expectedMessages): void {
    $service = Service::query()->create([
        'name' => 'Servicio base',
    ]);

    if (($invalidData['name'] ?? null) === 'Servicio duplicado') {
        Service::query()->create(['name' => 'Servicio duplicado']);
    }

    put(action([ServiceController::class, 'update'], $service->id), array_merge([
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
        'invalidData' => ['name' => 'Servicio duplicado'],
        'expectedMessages' => ['El campo nombre ya ha sido registrado.'],
    ],
]);

it('deletes a service', function (): void {
    $service = Service::query()->create([
        'name' => 'Servicio a eliminar',
    ]);

    delete(action([ServiceController::class, 'destroy'], $service->id))
        ->assertNoContent();

    $this->assertDatabaseMissing('services', ['id' => $service->id]);
});

it('fails to delete a service that does not exist', function (): void {
    delete(action([ServiceController::class, 'destroy'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Service] 999'],
        ]);
});

it('shows a service', function (): void {
    $service = Service::query()->create([
        'name' => 'Servicio show',
    ]);

    get(action([ServiceController::class, 'show'], $service->id))
        ->assertOk()
        ->assertExactJson([
            'id' => $service->id,
            'name' => 'Servicio show',
        ]);
});

it('fails to show a service that does not exist', function (): void {
    get(action([ServiceController::class, 'show'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\Service] 999'],
        ]);
});

it('returns a collection of services', function (): void {
    $firstService = Service::query()->create(['name' => 'Servicio 1']);
    $secondService = Service::query()->create(['name' => 'Servicio 2']);

    get(action([ServiceController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $firstService->id,
                'name' => 'Servicio 1',
            ],
            [
                'id' => $secondService->id,
                'name' => 'Servicio 2',
            ],
        ]);
});

