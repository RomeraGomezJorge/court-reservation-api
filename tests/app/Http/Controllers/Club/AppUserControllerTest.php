<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\Gender;
use App\Http\Controllers\Club\AppUserController;
use App\Models\AppUser;
use App\Models\Club;
use App\Models\ClubUser;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\delete;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

function validAppUserPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => 'john@example.com',
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [test()->clubs[0]->id],
    ], $overrides);
}

dataset('invalid app user payload', [

]);

beforeEach(function (): void {
    Notification::fake();

    $this->loggedClubUser = ClubUser::factory()->createQuietly();
    $this->clubs = Club::factory()
        ->count(2)
        ->sequence(
            ['club_user_id' => $this->loggedClubUser->id],
            ['club_user_id' => $this->loggedClubUser->id],
        )
        ->createQuietly();

    actingAs($this->loggedClubUser);
});

it('returns the app users related to the club without filters ordered by created_at desc', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);
    $otherClub = Club::factory()->createQuietly();

    [$newestAppUser, $middleAppUser, $oldestAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            [
                'name' => 'Newest App User',
                'created_at' => '2025-01-03 12:00:00',
            ],
            [
                'name' => 'Middle App User',
                'created_at' => '2025-01-02 12:00:00',
            ],
            [
                'name' => 'Oldest App User',
                'created_at' => '2025-01-01 12:00:00',
            ],
        )
        ->createQuietly();

    $club->appUsers()->attach([$newestAppUser->id, $oldestAppUser->id]);
    $otherClub->appUsers()->attach($middleAppUser->id);

    get(
        action([AppUserController::class, 'index'])
    )->assertJsonPath('data', [
        [
            'id' => $newestAppUser->id,
            'name' => $newestAppUser->name,
            'last_name' => $newestAppUser->last_name,
            'phone_number' => $newestAppUser->phone_number,
            'email' => $newestAppUser->email,
        ],
        [
            'id' => $oldestAppUser->id,
            'name' => $oldestAppUser->name,
            'last_name' => $oldestAppUser->last_name,
            'phone_number' => $oldestAppUser->phone_number,
            'email' => $oldestAppUser->email,
        ],
    ]);
});

it('returns the app users filtered by name', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);
    $otherClub = Club::factory()->createQuietly();

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            [
                'name' => 'Carlos',
                'created_at' => '2025-01-03 12:00:00',
            ],
            [
                'name' => 'Mario',
                'created_at' => '2025-01-02 12:00:00',
            ],
            [
                'name' => 'Carlitos',
                'created_at' => '2025-01-01 12:00:00',
            ],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    get(
        action([AppUserController::class, 'index'], ['name' => 'Car'])
    )->assertJsonPath('data', [
        [
            'id' => $firstAppUser->id,
            'name' => $firstAppUser->name,
            'last_name' => $firstAppUser->last_name,
            'phone_number' => $firstAppUser->phone_number,
            'email' => $firstAppUser->email,
        ],
    ]);
});

it('returns the app users filtered by email', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);
    $otherClub = Club::factory()->createQuietly();

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            [
                'email' => 'Carlos@example.com',
                'created_at' => '2025-01-03 12:00:00',
            ],
            [
                'email' => 'Mario@example.com',
                'created_at' => '2025-01-02 12:00:00',
            ],
            [
                'email' => 'Carlitos@example.com',
                'created_at' => '2025-01-01 12:00:00',
            ],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    get(
        action([AppUserController::class, 'index'], ['email' => 'Car'])
    )->assertJsonPath('data', [
        [
            'id' => $firstAppUser->id,
            'name' => $firstAppUser->name,
            'last_name' => $firstAppUser->last_name,
            'phone_number' => $firstAppUser->phone_number,
            'email' => $firstAppUser->email,
        ],
    ]);
});

it('returns the app users filtered by last name', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);
    $otherClub = Club::factory()->createQuietly();

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            [
                'last_name' => 'Lopez',
                'created_at' => '2025-01-03 12:00:00',
            ],
            [
                'last_name' => 'Morales',
                'created_at' => '2025-01-02 12:00:00',
            ],
            [
                'last_name' => 'Lozano',
                'created_at' => '2025-01-01 12:00:00',
            ],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    get(
        action([AppUserController::class, 'index'], ['last_name' => 'Lo'])
    )->assertJsonPath('data', [
        [
            'id' => $firstAppUser->id,
            'name' => $firstAppUser->name,
            'last_name' => $firstAppUser->last_name,
            'phone_number' => $firstAppUser->phone_number,
            'email' => $firstAppUser->email,
        ],
    ]);
});

it('returns the app users filtered by phone number', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);
    $otherClub = Club::factory()->createQuietly();

    [$firstAppUser, $secondAppUser, $thirdAppUser] = AppUser::factory()
        ->count(3)
        ->sequence(
            [
                'phone_number' => '3415551001',
                'created_at' => '2025-01-03 12:00:00',
            ],
            [
                'phone_number' => '9999999999',
                'created_at' => '2025-01-02 12:00:00',
            ],
            [
                'phone_number' => '3415551002',
                'created_at' => '2025-01-01 12:00:00',
            ],
        )
        ->createQuietly();

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($thirdAppUser->id);

    get(
        action([AppUserController::class, 'index'], ['phone_number' => '341555100'])
    )->assertJsonPath('data', [
        [
            'id' => $firstAppUser->id,
            'name' => $firstAppUser->name,
            'last_name' => $firstAppUser->last_name,
            'phone_number' => $firstAppUser->phone_number,
            'email' => $firstAppUser->email,
        ],
    ]);
});

it('fails to list app users with invalid filters', function (array $filters, array $expectedMessages): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);

    get(action([AppUserController::class, 'index'], $filters))
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
        'filters' => ['email' => str_repeat('a', 95).'@example.com'],
        'expectedMessages' => ['El campo correo electrónico no debe ser mayor que 100 caracteres.'],
    ],
    'long phone number' => [
        'filters' => ['phone_number' => str_repeat('a', 51)],
        'expectedMessages' => ['El campo teléfono no debe ser mayor que 50 caracteres.'],
    ],
    'invalid sort column' => [
        'filters' => ['sort_column' => 'created_at'],
        'expectedMessages' => ['El campo columna de ordenamiento no está en la lista de valores permitidos.'],
    ],
    'invalid sort direction' => [
        'filters' => ['sort_direction' => 'up'],
        'expectedMessages' => ['El campo dirección de ordenamiento no está en la lista de valores permitidos.'],
    ],
    'per page lower than minimum' => [
        'filters' => ['per_page' => 0],
        'expectedMessages' => ['El tamaño de filas por página debe ser de al menos 1.'],
    ],
    'per page greater than maximum' => [
        'filters' => ['per_page' => 101],
        'expectedMessages' => ['El campo filas por página no debe ser mayor que 100.'],
    ],
]);

it('fails to store an app user with invalid payload', function (
    array $payloadOverrides,
    array $expectedMessages,
): void {
    $response = post(
        action([AppUserController::class, 'store']),
        validAppUserPayload($payloadOverrides),
    );

    $response
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

    Notification::assertNothingSent();
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

    'empty last name' => [
        'payloadOverrides' => [
            'last_name' => '',
        ],
        'expectedMessages' => [
            'El campo apellido es obligatorio.',
        ],
    ],

    'last name exceeds max length' => [
        'payloadOverrides' => [
            'last_name' => str_repeat('a', 101),
        ],
        'expectedMessages' => [
            'El campo apellido no debe ser mayor que 100 caracteres.',
        ],
    ],

    'empty phone number' => [
        'payloadOverrides' => [
            'phone_number' => '',
        ],
        'expectedMessages' => [
            'El campo teléfono es obligatorio.',
        ],
    ],

    'phone number exceeds max length' => [
        'payloadOverrides' => [
            'phone_number' => str_repeat('1', 51),
        ],
        'expectedMessages' => [
            'El campo teléfono no debe ser mayor que 50 caracteres.',
        ],
    ],

    'empty email' => [
        'payloadOverrides' => [
            'email' => '',
        ],
        'expectedMessages' => [
            'El campo correo electrónico es obligatorio.',
        ],
    ],

    'invalid email format' => [
        'payloadOverrides' => [
            'email' => 'invalid-email',
        ],
        'expectedMessages' => [
            'El campo correo electrónico no es un correo válido.',
        ],
    ],

    'email exceeds max length' => [
        'payloadOverrides' => [
            'email' => str_repeat('a', 95).'@e.com',
        ],
        'expectedMessages' => [
            'El campo correo electrónico no debe ser mayor que 100 caracteres.',
        ],
    ],

    'empty birthday' => [
        'payloadOverrides' => [
            'birthday' => '',
        ],
        'expectedMessages' => [
            'El campo fecha de nacimiento es obligatorio.',
        ],
    ],

    'invalid birthday format' => [
        'payloadOverrides' => [
            'birthday' => 'not-a-date',
        ],
        'expectedMessages' => [
            'El campo fecha de nacimiento debe ser una fecha válida.',
        ],
    ],

    'future birthday' => [
        'payloadOverrides' => [
            'birthday' => Date::now()->addDay()->format('Y-m-d'),
        ],
        'expectedMessages' => [
            'El campo fecha de nacimiento debe ser una fecha anterior o igual a today.',
        ],
    ],

    'empty gender' => [
        'payloadOverrides' => [
            'gender' => '',
        ],
        'expectedMessages' => [
            'El campo género es obligatorio.',
        ],
    ],

    'invalid gender' => [
        'payloadOverrides' => [
            'gender' => 'invalid-gender',
        ],
        'expectedMessages' => [
            'El campo género no está en la lista de valores permitidos.',
        ],
    ],

    'empty club ids' => [
        'payloadOverrides' => [
            'club_ids' => [],
        ],
        'expectedMessages' => [
            'El campo clubes es obligatorio.',
        ],
    ],

    'club ids is not an array' => [
        'payloadOverrides' => [
            'club_ids' => 'not-an-array',
        ],
        'expectedMessages' => [
            'El campo clubes debe ser un conjunto.',
        ],
    ],

    'club ids is not exist' => [
        'payloadOverrides' => [
            'club_ids' => [9999],
        ],
        'expectedMessages' => [
            'El club en la posición 1 no existe.',
        ],
    ],

]);

it('fails to store an app user without club_ids field', function (): void {
    post(action([AppUserController::class, 'store']), [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => 'john@example.com',
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
    ])
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => ['El campo clubes es obligatorio.'],
        ]);

    Notification::assertNothingSent();
});

it('fails to store an app user with duplicate club_ids', function (): void {

    $payload = validAppUserPayload(
        overrides: [
            'club_ids' => [$this->clubs[0]->id, $this->clubs[0]->id],
        ]);

    post(action([AppUserController::class, 'store']), $payload)
        ->assertExactJson([
            'code' => 422,
            'messages' => [
                'El club seleccionado en la posición 1 está duplicado.',
                'El club seleccionado en la posición 2 está duplicado.',
            ],
        ]);

    Notification::assertNothingSent();
});

it('fails to store an app user when already attached to the club', function (): void {
    $existingAppUser = AppUser::factory()->createQuietly();
    $existingAppUser->clubs()->attach($this->clubs[0]->id);

    $payload = validAppUserPayload(
        overrides: ['email' => $existingAppUser->email],
    );

    post(action([AppUserController::class, 'store']), $payload)
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => ['El usuario ya está asociado a uno de tus clubes.'],
        ]);

    Notification::assertNothingSent();
});

it('stores an app user when not already attached to the authenticated user clubs', function (): void {
    $existingAppUser = AppUser::factory()->createQuietly();
    $otherClubUser = ClubUser::factory()->createQuietly();
    $otherClub = Club::factory()->createQuietly(['club_user_id' => $otherClubUser->id]);
    $existingAppUser->clubs()->attach($otherClub->id);

    $payload = validAppUserPayload(
        overrides: ['email' => $existingAppUser->email],
    );

    post(action([AppUserController::class, 'store']), $payload)
        ->assertStatus(201);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $existingAppUser->id,
        'club_id' => $this->clubs[0]->id,
    ]);

    Notification::assertNothingSent();
});

it('fails to store an app user with club_ids not owned by authenticated club user', function (): void {
    $otherClub = Club::factory()
        ->createQuietly(['club_user_id' => ClubUser::factory()]);

    $payload = validAppUserPayload(
        overrides: ['club_ids' => [$otherClub->id]],
    );

    post(action([AppUserController::class, 'store']), $payload)
        ->assertExactJson([
            'code' => 422,
            'messages' => ['Los siguientes clubes no pertenecen al usuario autenticado: '.$otherClub->id.'.'],
        ]);

    Notification::assertNothingSent();

});

it('stores new app user with club_ids owned by authenticated club user', function (): void {
    $payload = validAppUserPayload(
        overrides: [
            'club_ids' => [$this->clubs[0]->id, $this->clubs[1]->id],
        ],
    );

    post(action([AppUserController::class, 'store']), [
        ...$payload,
    ])
        ->assertStatus(201);

    unset($payload['club_ids']);
    $this->assertDatabaseHas('app_users', [
        ...$payload,
    ]);

    $createdAppUser = AppUser::query()
        ->where('email', $payload['email'])
        ->firstOrFail();

    $this->assertDatabaseHas('app_user_club', [
        'club_id' => $this->clubs[0]->id,
        'app_user_id' => $createdAppUser->id,
    ]);

    $this->assertDatabaseHas('app_user_club', [
        'club_id' => $this->clubs[1]->id,
        'app_user_id' => $createdAppUser->id,
    ]);

    Notification::assertSentTo(
        notifiable: AppUser::query()->where('email', $payload['email'])->firstOrFail(),
        notification: ResetPasswordNotification::class,
    );

});

it('attaches existing app user to clubs correctly', function (): void {
    $existingAppUser = AppUser::factory()->createQuietly();

    $payload = validAppUserPayload(
        overrides: ['email' => $existingAppUser->email],
    );

    post(action([AppUserController::class, 'store']), $payload)
        ->assertStatus(201);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $existingAppUser->id,
        'club_id' => $this->clubs[0]->id,
    ]);

    // Check dont create a new app user
    $this->assertDatabaseCount('app_user_club', 1);

    Notification::assertNothingSent();
});

it('attaches existing app user to multiple clubs correctly', function (): void {
    $existingAppUser = AppUser::factory()->createQuietly();

    $payload = validAppUserPayload(
        overrides: [
            'email' => $existingAppUser->email,
            'club_ids' => [$this->clubs[0]->id, $this->clubs[1]->id],
        ],
    );

    post(action([AppUserController::class, 'store']), $payload)
        ->assertStatus(201);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $existingAppUser->id,
        'club_id' => $this->clubs[0]->id,
    ]);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $existingAppUser->id,
        'club_id' => $this->clubs[1]->id,
    ]);

    // Check dont create a new app user
    $this->assertDatabaseCount('app_user_club', 2);

    Notification::assertNothingSent();
});

it('recovers from race condition when app user creation hits unique constraint violation', function (): void {
    // Pre-create the app user that will cause the race condition
    $existingAppUser = AppUser::factory()->createQuietly([
        'email' => 'race-condition@example.com',
    ]);

    $payload = validAppUserPayload(
        overrides: [
            'email' => 'race-condition@example.com',
            'club_ids' => [$this->clubs[0]->id],
        ],
    );

    // Simulate a concurrent request creating the same email by manipulating the query
    // The service should catch the unique constraint violation and recover gracefully
    post(action([AppUserController::class, 'store']), $payload)
        ->assertStatus(201);

    // Verify the existing user was reused and attached to the club
    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $existingAppUser->id,
        'club_id' => $this->clubs[0]->id,
    ]);

    // Verify no new app user was created
    $this->assertDatabaseCount('app_users', 1);
    $this->assertDatabaseCount('app_user_club', 1);

    // Verify no password reset notification was sent (only sent on creation)
    Notification::assertNothingSent();
});

it('properly handles postgres unique constraint violation during app user creation', function (): void {
    $email = 'postgres-race-'.time().'@example.com';
    $payload = validAppUserPayload(
        overrides: [
            'email' => $email,
            'club_ids' => [$this->clubs[0]->id],
        ],
    );

    // First request creates the app user successfully
    post(action([AppUserController::class, 'store']), $payload)
        ->assertStatus(201);

    $createdAppUser = AppUser::query()->where('email', $email)->firstOrFail();
    expect($createdAppUser)->toBeInstanceOf(AppUser::class);

    // Verify the user and attachment were created
    $this->assertDatabaseHas('app_users', [
        'email' => $email,
    ]);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $createdAppUser->id,
        'club_id' => $this->clubs[0]->id,
    ]);

    Notification::assertSentTo($createdAppUser, ResetPasswordNotification::class);
});

it('detaches an app user from the club without deleting it', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    $club->appUsers()->attach($appUser->id);

    delete(action([AppUserController::class, 'destroy'], ['app_user' => $appUser]))
        ->assertNoContent();

    $this->assertDatabaseMissing('app_user_club', [
        'app_user_id' => $appUser->id,
    ]);

    $this->assertDatabaseHas('app_users', [
        'id' => $appUser->id,
    ]);

});

it('fails to delete an app user that does not belong to the club', function (): void {
    Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);

    $otherClub = Club::factory()->createQuietly();
    $appUser = AppUser::factory()->createQuietly();
    $appUser->clubs()->attach($otherClub->id);

    delete(action([AppUserController::class, 'destroy'], ['app_user' => $appUser]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $appUser->id,
    ]);

    $this->assertDatabaseHas('app_users', [
        'id' => $appUser->id,
    ]);
});

it('shows an app user belonging to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly([
        'name' => 'Laura',
        'last_name' => 'Fernandez',
        'phone_number' => '3415550200',
        'birthday' => '1993-04-04',
        'gender' => Gender::Other,
    ]);

    $club->appUsers()->attach($appUser->id);

    get(action([AppUserController::class, 'show'], ['app_user' => $appUser]))
        ->assertStatus(200)
        ->assertExactJson([
            'id' => $appUser->id,
            'name' => 'Laura',
            'last_name' => 'Fernandez',
            'phone_number' => '3415550200',
            'birthday' => '1993-04-04',
            'gender' => Gender::Other->value,
            'email' => $appUser->email,
            'club_ids' => [$club->id],
        ]);
});

it('fails to show an app user that does not belong to the club', function (): void {
    Club::factory()->createQuietly([
        'club_user_id' => $this->loggedClubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();
    $otherClub = Club::factory()->createQuietly();
    $appUser->clubs()->attach($otherClub->id);

    get(action([AppUserController::class, 'show'], ['app_user' => $appUser]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('skips app user club attachment validation when authenticated user has no clubs', function (): void {

    $otherClubUser = ClubUser::factory()->createQuietly();
    $club = Club::factory()->createQuietly([
        'club_user_id' => $otherClubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    $payload = validAppUserPayload(
        overrides: [
            'email' => $appUser->email,
            'club_ids' => [$club->id],
        ],
    );

    $this->loggedClubUser->clubs()->delete();

    $response = post(action([AppUserController::class, 'store']), $payload);

    $response->assertExactJson([
        'code' => 422,
        'messages' => ['Los siguientes clubes no pertenecen al usuario autenticado: '.$club->id.'.'],
    ]);
});
