<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\FeatureController;
use App\Models\Feature;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = actingAsUser();
});

it('stores a feature', function (): void {
    $featureData = [
        'name' => 'Iluminacion',
    ];

    post(action([FeatureController::class, 'store']), $featureData)
        ->assertStatus(201);

    $this->assertDatabaseHas('features', [
        'name' => 'Iluminacion',
        'is_active' => true,
    ]);
});

it('fails to store a feature with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['name'] ?? null) === 'Feature duplicada') {
        Feature::query()->createQuietly(['name' => 'Feature duplicada', 'is_active' => true]);
    }

    $featureData = [
        'name' => 'Feature valida',
    ];

    post(action([FeatureController::class, 'store']), array_merge($featureData, $invalidData))
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
        'invalidData' => ['name' => 'Feature duplicada'],
        'expectedMessages' => ['El campo nombre ya ha sido registrado.'],
    ],
]);

it('updates a feature', function (): void {
    $feature = Feature::query()->createQuietly([
        'name' => 'Feature base',
        'is_active' => true,
    ]);

    put(action([FeatureController::class, 'update'], $feature->id), [
        'name' => 'Feature actualizada',
    ])->assertNoContent();

    $this->assertDatabaseHas('features', [
        'id' => $feature->id,
        'name' => 'Feature actualizada',
    ]);
});

it('fails to update a feature that does not exist', function (): void {
    put(action([FeatureController::class, 'update'], 999), [
        'name' => 'Feature cualquiera',
    ])
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to update a feature with invalid data', function (array $invalidData, array $expectedMessages): void {
    $feature = Feature::query()->createQuietly([
        'name' => 'Feature base',
        'is_active' => true,
    ]);

    if (($invalidData['name'] ?? null) === 'Feature duplicada') {
        Feature::query()->createQuietly(['name' => 'Feature duplicada', 'is_active' => true]);
    }

    put(action([FeatureController::class, 'update'], $feature->id), array_merge([
        'name' => 'Feature valida',
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
        'invalidData' => ['name' => 'Feature duplicada'],
        'expectedMessages' => ['El campo nombre ya ha sido registrado.'],
    ],
]);

it('deletes a feature', function (): void {
    $feature = Feature::query()->createQuietly([
        'name' => 'Feature a eliminar',
        'is_active' => true,
    ]);

    delete(action([FeatureController::class, 'destroy'], $feature->id))
        ->assertNoContent();

    $this->assertDatabaseMissing('features', ['id' => $feature->id]);
});

it('fails to delete a feature that does not exist', function (): void {
    delete(action([FeatureController::class, 'destroy'], 999))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('shows a feature', function (): void {
    $feature = Feature::query()->createQuietly([
        'name' => 'Feature show',
        'is_active' => true,
    ]);

    get(action([FeatureController::class, 'show'], $feature->id))
        ->assertOk()
        ->assertExactJson([
            'id' => $feature->id,
            'name' => 'Feature show',
            'is_active' => true,
        ]);
});

it('fails to show a feature that does not exist', function (): void {
    get(action([FeatureController::class, 'show'], 999))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('returns a collection of features', function (): void {
    $firstFeature = Feature::query()->createQuietly(['name' => 'Feature 1', 'is_active' => true]);
    $secondFeature = Feature::query()->createQuietly(['name' => 'Feature 2', 'is_active' => false]);

    get(action([FeatureController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $firstFeature->id,
                'name' => 'Feature 1',
                'is_active' => true,
            ],
            [
                'id' => $secondFeature->id,
                'name' => 'Feature 2',
                'is_active' => false,
            ],
        ]);
});
