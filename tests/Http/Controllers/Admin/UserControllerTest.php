<?php

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\UserController;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    $this->user = actingAsUser();
});

it('stores a user', function (): void {
    $userData = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'P@ssword123456',
        'password_confirmation' => 'P@ssword123456',
    ];

    post(action([UserController::class, 'store']), $userData)
        ->assertStatus(201);

    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
    ]);

    $user = User::query()->where('email', 'johndoe@example.com')->first();
    expect(Hash::check('P@ssword123456', $user->password))->toBeTrue();
});

it('fails to store a user with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['email'] ?? null) === 'duplicate@example.com') {
        User::factory()->create(['email' => 'duplicate@example.com']);
    }

    $userData = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'Password.123!',
        'password_confirmation' => 'Password.123!',
    ];

    post(action([UserController::class, 'store']), array_merge($userData, $invalidData))
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
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => ['El campo correo electrónico es obligatorio.'],
    ],
    'long email' => [
        'invalidData' => ['email' => str_repeat('a', 256).'@example.com'],
        'expectedMessages' => ['El campo correo electrónico no debe ser mayor que 255 caracteres.'],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'duplicate@example.com'],
        'expectedMessages' => ['El campo correo electrónico ya ha sido registrado.'],
    ],
    'empty password' => [
        'invalidData' => ['password' => '', 'password_confirmation' => ''],
        'expectedMessages' => ['El campo contraseña es obligatorio.', 'El campo confirmación de contraseña es obligatorio.'],
    ],
    'password too short' => [
        'invalidData' => ['password' => 'Short1!', 'password_confirmation' => 'Short1!'],
        'expectedMessages' => ['El campo contraseña debe contener al menos 12 caracteres.', 'El campo confirmación de contraseña debe contener al menos 12 caracteres.'],
    ],
    'password no numbers' => [
        'invalidData' => ['password' => 'Password.!@#', 'password_confirmation' => 'Password.!@#'],
        'expectedMessages' => ['La contraseña debe contener al menos un número.', 'La confirmación de contraseña debe contener al menos un número.'],
    ],
    'password no symbols' => [
        'invalidData' => ['password' => 'Password123456', 'password_confirmation' => 'Password123456'],
        'expectedMessages' => ['La contraseña debe contener al menos un símbolo.', 'La confirmación de contraseña debe contener al menos un símbolo.'],
    ],
    'password no uppercase' => [
        'invalidData' => ['password' => 'password.123!', 'password_confirmation' => 'password.123!'],
        'expectedMessages' => ['La contraseña debe contener al menos una letra mayúscula y una minúscula.', 'La confirmación de contraseña debe contener al menos una letra mayúscula y una minúscula.'],
    ],
    'password confirmation does not match' => [
        'invalidData' => [
            'password' => 'Password.123!',
            'password_confirmation' => 'Different.123!',
        ],
        'expectedMessages' => ['Los campos confirmación de contraseña y contraseña deben coincidir.'],
    ],
    'password too long' => [
        'invalidData' => [
            'password' => str_repeat('Aa1!', 70),
            'password_confirmation' => str_repeat('Aa1!', 70),
        ],
        'expectedMessages' => [
            'El campo contraseña no debe ser mayor que 255 caracteres.',
            'El campo confirmación de contraseña no debe ser mayor que 255 caracteres.',
        ],
    ],
]);

it('updates a user', function (): void {
    $user = User::factory()->create();

    put(action([UserController::class, 'update'], $user->id), [
        'email' => 'updated@example.com',
    ])->assertNoContent();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'email' => 'updated@example.com',
    ]);
});

it('fails to update a user that does not exist', function (): void {
    put(action([UserController::class, 'update'], 999), [
        'email' => 'johndoe@example.com',
    ])
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['No query results for model [App\\Models\\User] 999'],
        ]);
});

it('fails to update a user with invalid data', function (array $invalidData, array $expectedMessages): void {
    $user = User::factory()->create();

    if (($invalidData['email'] ?? null) === 'duplicate@example.com') {
        User::factory()->create(['email' => 'duplicate@example.com']);
    }

    $response = put(action([UserController::class, 'update'], $user), array_merge([
        'email' => 'valid@example.com',
    ], $invalidData));

    $response->assertStatus(422)
        ->assertJsonPath('code', 422);

    foreach ($expectedMessages as $message) {
        expect(collect($response->json('messages'))
            ->contains(fn(string $responseMessage): bool => str_contains($responseMessage, $message)))
            ->toBeTrue();
    }
})->with([
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => ['El campo correo electrónico es obligatorio.'],
    ],
    'long email' => [
        'invalidData' => ['email' => str_repeat('a', 256).'@example.com'],
        'expectedMessages' => ['El campo correo electrónico no debe ser mayor que 255 caracteres.'],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'duplicate@example.com'],
        'expectedMessages' => ['ya ha sido registrado.'],
    ],
]);

it('deletes a user', function (): void {
    $user = User::factory()->create();

    delete(action([UserController::class, 'destroy'], $user))
        ->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('shows a user', function (): void {
    $user = User::factory()->create();

    get(action([UserController::class, 'show'], $user->id))
        ->assertOk()
        ->assertExactJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['super_admin'],
        ]);
});

it('returns a collection of users', function (): void {
    $otherUser = User::factory()->create();

    get(action([UserController::class, 'index']))
        ->assertOk()
        ->assertExactJson([
            [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
                'roles' => ['super_admin'],
            ],
            [
                'id' => $otherUser->id,
                'name' => $otherUser->name,
                'email' => $otherUser->email,
                'roles' => ['super_admin'],
            ],
        ]);

});

it('changes a user password', function (): void {
    $targetUser = User::factory()->create();

    $newPassword = 'NewValidP@ss1!';

    put(action([UserController::class, 'changePassword'], $targetUser), [
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])->assertNoContent();

    $targetUser->refresh();
    expect(Hash::check($newPassword, $targetUser->password))->toBeTrue();
});

it('fails to delete the last user', function (): void {
    User::query()->where('id', '!=', $this->user->id)->delete();

    delete(action([UserController::class, 'destroy'], $this->user))
        ->assertStatus(400)
        ->assertJson([
            'messages' => ['No se puede eliminar el último usuario'],
            'code' => 400,
        ]);
});
