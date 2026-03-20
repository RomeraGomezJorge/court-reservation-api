<?php

namespace Tests\Http\Controllers\Admin;


use App\Http\Controllers\Admin\UserController;

use App\Models\User;

use Hash;

use function Pest\Laravel\post;

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

it('it fails to store a user with invalid data', function (array $invalidData, array $expectedErrors): void {
    $userData = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'password' => 'Password.123!',
        'password_confirmation' => 'Password.123!',

    ];
    $userData = array_merge($userData, $invalidData);

        post(action([UserController::class, 'store']), $userData)
        ->assertExactJson($expectedErrors);
})->with([
    'empty name' => [
        'invalidData' => ['name' => ''],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'El campo nombre es obligatorio.',
            ],
        ],
    ],
    'short name' => [
        'invalidData' => ['name' => 'a'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'nombre debe contener al menos 2 caracteres.',
            ],
        ],
    ],
    'long name' => [
        'invalidData' => ['name' => str_repeat('a', 256)],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'nombre no debe ser mayor que 255 caracteres.',
            ],
        ],
    ],
    'invalid email format' => [
        'invalidData' => ['email' => 'invalid-email'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'correo electrónico no es un correo válido.',
            ],
        ],
    ],
    'long email' => [
        'invalidData' => ['email' => str_repeat('a', 256).'@example.com'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'correo electrónico no debe ser mayor que 255 caracteres.',
            ],
        ],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'test@test.com'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'El correo electrónico ya ha sido registrado.',
            ],
        ],
    ],
    'empty password' => [
        'invalidData' => ['password' => '', 'password_confirmation' => ''],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'El campo contraseña es obligatorio.',
            ],
        ],
    ],
    'password too short' => [
        'invalidData' => ['password' => 'Short1!', 'password_confirmation' => 'Short1!'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'contraseña debe contener al menos 12 caracteres.',
            ],
        ],
    ],
    'password no numbers' => [
        'invalidData' => ['password' => 'Password.!@#', 'password_confirmation' => 'Password.!@#'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'La contraseña debe contener al menos un número.',
            ],
        ],
    ],
    'password no symbols' => [
        'invalidData' => ['password' => 'Password123456', 'password_confirmation' => 'Password123456'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'La contraseña debe contener al menos un símbolo.',
            ],
        ],
    ],
    'password no uppercase' => [
        'invalidData' => ['password' => 'password.123!', 'password_confirmation' => 'password.123!'],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'La contraseña debe contener letras mayúsculas y minúsculas.',
            ],
        ],
    ],
]);

it('updates a user', function (): void {
    $user = User::factory()->create();

    $userData = [
        'id' => $user->id,
        'name' => 'Updated Name',
        'email' => 'updated@example.com',
        'roles' => [Roles::ADMIN],
    ];

    actingAs($this->user)->put(action([UserController::class, 'update'], $user->id), $userData)
        ->assertOk();

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated Name',

        'email' => 'updated@example.com',
    ]);

    $user->refresh();
    expect($user->hasRole(Roles::ADMIN))->toBeTrue();
});

it('fails to update a user that does not exist', function (): void {
    $user = User::factory()->create();

    $userData = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'roles' => [Roles::ADMIN],
    ];

    actingAs($this->user)
        ->put(action([UserController::class, 'update'], 999), $userData)
        ->assertStatus(404)
        ->assertJson([
            'messages' => [
                'No query results for model [Src\\Domain\\User\\Models\\User] 999',
            ],
            'code' => 404,
        ]);
});

it('fails to update a use with invalid data', function (array $invalidData, array $expectedErrors): void {
    $user = User::factory()->create();

    $userData = [
        'name' => 'John Doe',
        'email' => 'johndoe@example.com',
        'roles' => [Roles::ADMIN],
    ];
    $userData = array_merge($userData, $invalidData);

    actingAs($this->user)
        ->put(action([UserController::class, 'update'], $user), $userData)
        ->assertExactJson($expectedErrors);
})->with([
    'empty name' => [
        'invalidData' => ['name' => ''],
        'expectedErrors' => [
            'code' => 422,
            'messages' => [
                'El campo nombre debe ser una cadena de caracteres.',
                'El campo nombre es obligatorio.',
            ],
        ],
    ],
//    'short name' => [
//        'invalidData' => ['name' => 'a'],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'nombre debe contener al menos 2 caracteres.',
//            ],
//        ],
//    ],
//    'invalid email format' => [
//        'invalidData' => ['email' => 'correo-invalido'],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'correo electrónico no es un correo válido.',
//            ],
//        ],
//    ],
//    'long email' => [
//        'invalidData' => ['email' => str_repeat('a', 256).'@example.com'],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'correo electrónico no debe ser mayor que 255 caracteres.',
//            ],
//        ],
//    ],
//    'duplicate email' => [
//        'invalidData' => ['email' => 'test@test.com'],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'El correo electrónico ya ha sido registrado.',
//            ],
//        ],
//    ],
//    'empty roles' => [
//        'invalidData' => ['roles' => []],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'El campo roles es obligatorio.',
//            ],
//        ],
//    ],
//    'invalid role' => [
//        'invalidData' => ['roles' => ['ROL_INVALIDO']],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'roles es inválido.',
//            ],
//        ],
//    ],
//    'name too long' => [
//        'invalidData' => ['name' => str_repeat('a', 256)],
//        'expectedErrors' => [
//            'code' => 422,
//            'messages' => [
//                'nombre no debe ser mayor que 255 caracteres.',
//            ],
//        ],
//    ],

]);

it('deletes a user', function (): void {
    $user = User::factory()->create();

    actingAs($this->user)->delete(action([UserController::class, 'destroy'], $user))->assertOk();

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

it('shows a user', function (): void {
    $user = User::factory()->create();

    actingAs($this->user)
        ->get(action([UserController::class, 'show'], $user->id))
        ->assertOk()
        ->assertExactJson([
            'email' => $user->email,
            'name' => $user->name,
            'roles' => [],
            'id' => $user->id,
        ]);
});

it('returns a collection of users', function (): void {
    $user = User::factory()->create();

    actingAs($this->user)
        ->get(action([UserController::class, 'index']))
        ->assertOk()
        ->assertJsonFragment([
            'email' => $this->user->email,
            'name' => $this->user->name,
            'roles' => [Roles::ADMIN],
            'id' => $this->user->id,
        ])
        ->assertJsonFragment([
            'email' => $user->email,
            'name' => $user->name,
            'roles' => [],
            'id' => $user->id,
        ]);
});

it('sets the authenticated user password - direct call', function (): void {
    $newPassword = 'NewValidP@ss1!';
    $passwordData = SetPasswordData::from([
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ]);

    actingAs($this->user);

    $controller = $this->app->make(UserController::class);

    $controller->setMyPassword($passwordData);

    $this->user->refresh();
    expect(Hash::check($newPassword, $this->user->password))->toBeTrue();
});

it('gets the authenticated user details', function (): void {
    actingAs($this->user)
        ->get(action([UserController::class, 'getMe']))
        ->assertOk()
        ->assertExactJson([
            'id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
            'roles' => $this->user->roles->pluck('name')->toArray(),
        ]);
});
