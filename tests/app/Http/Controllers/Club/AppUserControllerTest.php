<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\Gender;
use App\Http\Controllers\Club\AppUserController;
use App\Models\AppUser;
use App\Models\Club;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;
use function Pest\Laravel\put;

function validAppUserPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Juan',
        'last_name' => 'Perez',
        'phone_number' => '3415550000',
        'email' => 'juan.perez@example.com',
        'birthday' => '1995-05-20',
        'gender' => Gender::Male->value,
    ], $overrides);
}

function appUserResourcePayload(AppUser $appUser): array
{
    return [
        'id' => $appUser->id,
        'name' => $appUser->name,
        'last_name' => $appUser->last_name,
        'phone_number' => $appUser->phone_number,
        'birthday' => Date::parse($appUser->birthday)->toDateString(),
        'gender' => $appUser->gender->value,
        'email' => $appUser->email,
    ];
}

function appUserIndexResponsePayload(string $path, array $appUsers): array
{
    $pageUrl = $path.'?page=1';

    return [
        'data' => array_map(
            appUserResourcePayload(...),
            $appUsers,
        ),
        'links' => [
            'first' => $pageUrl,
            'last' => $pageUrl,
            'prev' => null,
            'next' => null,
        ],
        'meta' => [
            'current_page' => 1,
            'from' => 1,
            'last_page' => 1,
            'links' => [
                [
                    'url' => null,
                    'label' => '&laquo; Anterior',
                    'active' => false,
                    'page' => null,
                ],
                [
                    'url' => $pageUrl,
                    'label' => '1',
                    'active' => true,
                    'page' => 1,
                ],
                [
                    'url' => null,
                    'label' => 'Siguiente &raquo;',
                    'active' => false,
                    'page' => null,
                ],
            ],
            'path' => $path,
            'per_page' => 15,
            'to' => count($appUsers),
            'total' => count($appUsers),
        ],
    ];
}

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('returns the app users related to the club without filters', function (): void {

    [$club, $otherClub] = Club::factory()
        ->count(2)
        ->createQuietly([
            'club_user_id' => $this->clubUser->id,
        ]);

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->createQuietly()
        ->all();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    $indexUrl = action([AppUserController::class, 'index'], ['club' => $club]);

    get($indexUrl)
        ->assertExactJson(appUserIndexResponsePayload($indexUrl, [$firstAppUser, $secondAppUser]));
});

it('returns the app users filtered by name', function (): void {
    [$club, $otherClub] = Club::factory()
        ->count(2)
        ->createQuietly([
            'club_user_id' => $this->clubUser->id,
        ]);

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            ['name' => 'Carlos'],
            ['name' => 'Carla'],
            ['name' => 'Diego'],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    $indexUrl = action([AppUserController::class, 'index'], ['club' => $club]);

    get($indexUrl.'?name=Car')
        ->assertExactJson(appUserIndexResponsePayload($indexUrl, [$firstAppUser, $secondAppUser]));
});

it('returns the app users filtered by email', function (): void {
    [$club, $otherClub] = Club::factory()
        ->count(2)
        ->createQuietly([
            'club_user_id' => $this->clubUser->id,
        ]);

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            ['name' => 'Carlos@example.com'],
            ['name' => 'Carla@example.com'],
            ['name' => 'Diego@example.com'],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    $indexUrl = action([AppUserController::class, 'index'], ['club' => $club]);

    get($indexUrl.'?name=Car')
        ->assertExactJson(appUserIndexResponsePayload($indexUrl, [$firstAppUser, $secondAppUser]));
});

it('returns the app users filtered by last name', function (): void {
    [$club, $otherClub] = Club::factory()
        ->count(2)
        ->createQuietly([
            'club_user_id' => $this->clubUser->id,
        ]);
    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            ['last_name' => 'Lopez'],
            ['last_name' => 'Lozano'],
            ['last_name' => 'Diaz'],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    $indexUrl = action([AppUserController::class, 'index'], ['club' => $club]);

    get($indexUrl.'?last_name=Lo')
        ->assertExactJson(appUserIndexResponsePayload($indexUrl, [$firstAppUser, $secondAppUser]));
});

it('returns the app users filtered by phone number', function (): void {
    [$club, $otherClub] = Club::factory()
        ->count(2)
        ->createQuietly([
            'club_user_id' => $this->clubUser->id,
        ]);

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            ['phone_number' => '3415551001'],
            ['phone_number' => '3415551002'],
            ['phone_number' => '9999999999'],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    $indexUrl = action([AppUserController::class, 'index'], ['club' => $club]);

    get($indexUrl.'?phone_number=341555100')
        ->assertExactJson(appUserIndexResponsePayload($indexUrl, [$firstAppUser, $secondAppUser]));
});

it('fails to list app users when the club does not belong to the authenticated user', function (): void {
    $club = Club::factory()->createQuietly();

    get(action([AppUserController::class, 'index'], ['club' => $club]))
        ->assertNotFound()
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to list app users with invalid filters', function (array $filters, array $expectedMessages): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    get(action([AppUserController::class, 'index'], ['club' => $club]).'?'.http_build_query($filters))
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'long name' => [
        'filters' => ['name' => str_repeat('a', 101)],
        'expectedMessages' => ['El campo nombre no debe ser mayor que 100 caracteres.'],
    ],
    'long last name' => [
        'filters' => ['last_name' => str_repeat('a', 101)],
        'expectedMessages' => ['El campo apellido no debe ser mayor que 100 caracteres.'],
    ],
    'long email' => [
        'filters' => ['email' => str_repeat('a', 101).'@example.com'],
        'expectedMessages' => ['El campo correo electrónico no debe ser mayor que 100 caracteres.'],
    ],
    'long phone number' => [
        'filters' => ['phone_number' => str_repeat('a', 51)],
        'expectedMessages' => ['El campo teléfono no debe ser mayor que 50 caracteres.'],
    ],
    'invalid email format' => [
        'filters' => ['email' => 'invalid-email'],
        'expectedMessages' => ['El campo correo electrónico no es un correo válido.'],
    ],
]);

it('stores an app user and sends a password setup email when email exists', function (): void {
    Notification::fake();

    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $payload = validAppUserPayload([
        'name' => 'Ana',
        'last_name' => 'Martinez',
        'phone_number' => '3415550101',
        'email' => 'ana.martinez@example.com',
        'gender' => Gender::Female->value,
    ]);

    $response = post(action([AppUserController::class, 'store'], ['club' => $club]), $payload);

    $appUser = AppUser::query()->where('phone_number', '3415550101')->firstOrFail();

    $response
        ->assertStatus(201)
        ->assertExactJson([
            'id' => $appUser->id,
            'name' => 'Ana',
            'last_name' => 'Martinez',
            'phone_number' => '3415550101',
            'birthday' => '1995-05-20',
            'gender' => Gender::Female->value,
            'email' => 'ana.martinez@example.com',
        ]);

    expect($appUser->password)->not->toBeEmpty();
    expect($club->appUsers()->whereKey($appUser->id)->exists())->toBeTrue();

    Notification::assertSentTo($appUser, ResetPassword::class);
});

it('fails to store an app user when required fields are missing', function (array $invalidData, array $expectedMessages): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $payload = validAppUserPayload();

    foreach (array_keys($invalidData) as $field) {
        unset($payload[$field]);
    }

    post(action([AppUserController::class, 'store'], ['club' => $club]), $payload)
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'missing birthday' => [
        'invalidData' => ['birthday' => true],
        'expectedMessages' => ['El campo fecha de nacimiento es obligatorio.'],
    ],
    'missing gender' => [
        'invalidData' => ['gender' => true],
        'expectedMessages' => ['El campo género es obligatorio.'],
    ],
]);

it('fails to store an app user with invalid data', function (array $invalidData, array $expectedMessages): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    if (($invalidData['email'] ?? null) === 'duplicado@example.com') {
        AppUser::factory()->createQuietly(['email' => 'duplicado@example.com']);
    }

    if (($invalidData['phone_number'] ?? null) === 'duplicado-telefono') {
        AppUser::factory()->createQuietly(['phone_number' => 'duplicado-telefono']);
    }

    post(action([AppUserController::class, 'store'], ['club' => $club]), validAppUserPayload($invalidData))
        ->assertStatus(422)
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
        'invalidData' => ['name' => str_repeat('a', 101)],
        'expectedMessages' => ['El campo nombre no debe ser mayor que 100 caracteres.'],
    ],
    'empty last name' => [
        'invalidData' => ['last_name' => ''],
        'expectedMessages' => ['El campo apellido es obligatorio.'],
    ],
    'name not string' => [
        'invalidData' => ['name' => 000],
        'expectedMessages' => ['El campo nombre debe ser una cadena de caracteres.'],
    ],
    'long last name' => [
        'invalidData' => ['last_name' => str_repeat('a', 101)],
        'expectedMessages' => ['El campo apellido no debe ser mayor que 100 caracteres.'],
    ],
    'last name not string' => [
        'invalidData' => ['last_name' => 000],
        'expectedMessages' => ['El campo apellido debe ser una cadena de caracteres.'],
    ],
    'long email' => [
        'invalidData' => ['email' => str_repeat('a', 101).'@example.com'],
        'expectedMessages' => ['El campo correo electrónico no debe ser mayor que 100 caracteres.'],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'duplicado@example.com'],
        'expectedMessages' => ['El campo correo electrónico ya ha sido registrado.'],
    ],
    'invalid email format' => [
        'invalidData' => ['email' => 'invalid-email'],
        'expectedMessages' => ['El campo correo electrónico no es un correo válido.'],
    ],
    'empty phone number' => [
        'invalidData' => ['phone_number' => ''],
        'expectedMessages' => ['El campo teléfono es obligatorio.'],
    ],
    'long phone number' => [
        'invalidData' => ['phone_number' => str_repeat('a', 51)],
        'expectedMessages' => ['El campo teléfono no debe ser mayor que 50 caracteres.'],
    ],
    'duplicate phone number' => [
        'invalidData' => ['phone_number' => 'duplicado-telefono'],
        'expectedMessages' => ['El campo teléfono ya ha sido registrado.'],
    ],
    'phone number not string' => [
        'invalidData' => ['phone_number' => 000],
        'expectedMessages' => ['El campo teléfono debe ser una cadena de caracteres.'],
    ],
    'future birthday' => [
        'invalidData' => ['birthday' => '2999-01-01'],
        'expectedMessages' => ['El campo fecha de nacimiento debe ser una fecha anterior o igual a today.'],
    ],
    'invalid gender' => [
        'invalidData' => ['gender' => 'invalid-gender'],
        'expectedMessages' => ['El campo género no está en la lista de valores permitidos.'],
    ],
]);

it('shows an app user belonging to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly([
        'name' => 'Laura',
        'last_name' => 'Fernandez',
        'phone_number' => '3415550200',
        'birthday' => '1993-04-04',
        'gender' => Gender::Other,
    ]);

    $club->appUsers()->attach($appUser->id);

    get(action([AppUserController::class, 'show'], ['club' => $club, 'app_user' => $appUser]))
        ->assertStatus(200)
        ->assertExactJson([
            'id' => $appUser->id,
            'name' => 'Laura',
            'last_name' => 'Fernandez',
            'phone_number' => '3415550200',
            'birthday' => '1993-04-04',
            'gender' => Gender::Other->value,
            'email' => $appUser->email,
        ]);
});

it('fails to show an app user that does not belong to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    get(action([AppUserController::class, 'show'], ['club' => $club, 'app_user' => $appUser]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('updates an app user belonging to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly([
        'name' => 'Pedro',
        'last_name' => 'Sanchez',
        'phone_number' => '3415550300',
        'birthday' => '1994-05-05',
        'gender' => Gender::Male,
    ]);

    $club->appUsers()->attach($appUser->id);

    $response = put(action([AppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), validAppUserPayload([
        'name' => 'Pedro Actualizado',
        'last_name' => 'Sanchez Actualizado',
        'phone_number' => '3415550301',
        'email' => 'pedro.actualizado@example.com',
        'birthday' => '1992-06-06',
        'gender' => Gender::Other->value,
    ]));

    $appUser->refresh();

    $response
        ->assertStatus(200)
        ->assertExactJson([
            'id' => $appUser->id,
            'name' => 'Pedro Actualizado',
            'last_name' => 'Sanchez Actualizado',
            'phone_number' => '3415550301',
            'birthday' => '1992-06-06',
            'gender' => Gender::Other->value,
            'email' => 'pedro.actualizado@example.com',
        ]);

    expect($appUser->name)->toBe('Pedro Actualizado');
});

it('updates an app user keeping the same unique fields', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly([
        'name' => 'Pedro',
        'last_name' => 'Sanchez',
        'phone_number' => '3415550300',
        'email' => 'pedro@example.com',
        'birthday' => '1994-05-05',
        'gender' => Gender::Male,
    ]);

    $club->appUsers()->attach($appUser->id);

    put(action([AppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), [
        'name' => 'Pedro',
        'last_name' => 'Sanchez',
        'phone_number' => '3415550300',
        'email' => 'pedro@example.com',
        'birthday' => '1994-05-05',
        'gender' => Gender::Male->value,
    ])
        ->assertStatus(200)
        ->assertExactJson(appUserResourcePayload($appUser->refresh()));
});

it('fails to update an app user that does not belong to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    put(action([AppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), validAppUserPayload())
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('fails to update an app user when required fields are missing', function (array $invalidData, array $expectedMessages): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();
    $club->appUsers()->attach($appUser->id);

    $payload = validAppUserPayload();

    foreach (array_keys($invalidData) as $field) {
        unset($payload[$field]);
    }

    put(action([AppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), $payload)
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'missing birthday' => [
        'invalidData' => ['birthday' => true],
        'expectedMessages' => ['El campo fecha de nacimiento es obligatorio.'],
    ],
    'missing gender' => [
        'invalidData' => ['gender' => true],
        'expectedMessages' => ['El campo género es obligatorio.'],
    ],
]);

it('fails to update an app user with invalid data', function (array $invalidData, array $expectedMessages): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    if (($invalidData['email'] ?? null) === 'duplicado@example.com') {
        AppUser::factory()->createQuietly(['email' => 'duplicado@example.com']);
    }

    if (($invalidData['phone_number'] ?? null) === 'duplicado-telefono') {
        AppUser::factory()->createQuietly(['phone_number' => 'duplicado-telefono']);
    }

    $club->appUsers()->attach($appUser->id);

    put(action([AppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), validAppUserPayload($invalidData))
        ->assertStatus(422)
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
        'invalidData' => ['name' => str_repeat('a', 101)],
        'expectedMessages' => ['El campo nombre no debe ser mayor que 100 caracteres.'],
    ],
    'name not string' => [
        'invalidData' => ['name' => 000],
        'expectedMessages' => ['El campo nombre debe ser una cadena de caracteres.'],
    ],
    'empty last name' => [
        'invalidData' => ['last_name' => ''],
        'expectedMessages' => ['El campo apellido es obligatorio.'],
    ],
    'long last name' => [
        'invalidData' => ['last_name' => str_repeat('a', 101)],
        'expectedMessages' => ['El campo apellido no debe ser mayor que 100 caracteres.'],
    ],
    'last name not string' => [
        'invalidData' => ['last_name' => [000]],
        'expectedMessages' => ['El campo apellido debe ser una cadena de caracteres.'],
    ],
    'long email' => [
        'invalidData' => ['email' => str_repeat('a', 101).'@example.com'],
        'expectedMessages' => ['El campo correo electrónico no debe ser mayor que 100 caracteres.'],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'duplicado@example.com'],
        'expectedMessages' => ['El campo correo electrónico ya ha sido registrado.'],
    ],
    'invalid email format' => [
        'invalidData' => ['email' => 'invalid-email'],
        'expectedMessages' => ['El campo correo electrónico no es un correo válido.'],
    ],

    'empty phone number' => [
        'invalidData' => ['phone_number' => ''],
        'expectedMessages' => ['El campo teléfono es obligatorio.'],
    ],
    'long phone number' => [
        'invalidData' => ['phone_number' => str_repeat('a', 51)],
        'expectedMessages' => ['El campo teléfono no debe ser mayor que 50 caracteres.'],
    ],
    'duplicate phone number' => [
        'invalidData' => ['phone_number' => 'duplicado-telefono'],
        'expectedMessages' => ['El campo teléfono ya ha sido registrado.'],
    ],
    'phone number not string' => [
        'invalidData' => ['phone_number' => [111]],
        'expectedMessages' => ['El campo teléfono debe ser una cadena de caracteres.'],
    ],
    'future birthday' => [
        'invalidData' => ['birthday' => '2999-01-01'],
        'expectedMessages' => ['El campo fecha de nacimiento debe ser una fecha anterior o igual a today.'],
    ],
    'invalid gender' => [
        'invalidData' => ['gender' => 'invalid-gender'],
        'expectedMessages' => ['El campo género no está en la lista de valores permitidos.'],
    ],
]);

it('detaches an app user from the club without deleting it', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly([
        'phone_number' => '3415550400',
        'birthday' => '1990-07-07',
    ]);

    $club->appUsers()->attach($appUser->id);

    delete(action([AppUserController::class, 'destroy'], ['club' => $club, 'app_user' => $appUser]))
        ->assertNoContent();

    expect($club->appUsers()->whereKey($appUser->id)->exists())->toBeFalse();
    expect(AppUser::query()->whereKey($appUser->id)->exists())->toBeTrue();
});

it('fails to delete an app user that does not belong to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    delete(action([AppUserController::class, 'destroy'], ['club' => $club, 'app_user' => $appUser]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});
