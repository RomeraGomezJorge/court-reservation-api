<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\Gender;
use App\Http\Controllers\Club\AppUserController;
use App\Models\AppUser;
use App\Models\Club;
use App\Models\ClubUser;
use Carbon\Carbon;
use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\post;

function validPayload(array $overrides = []): array
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

beforeEach(function (): void {
    Notification::fake();

    $this->clubUser = ClubUser::factory()->createQuietly();
    $this->clubs = Club::factory()
        ->count(2)
        ->sequence(
            ['club_user_id' => $this->clubUser->id],
            ['club_user_id' => $this->clubUser->id],
        )
        ->createQuietly();

    actingAs($this->clubUser);
});

it('fails to store an app user with invalid payload', function (
    array $payloadOverrides,
    array $expectedMessages,
): void {
    $response = post(
        action([AppUserController::class, 'store']),
        validPayload($payloadOverrides),
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
            'email' => str_repeat('a', 89).'@e.com',
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
            'birthday' => Carbon::now()->addDay()->format('Y-m-d'),
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
            'El campo club_ids.0 no existe.',
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
    post(action([AppUserController::class, 'store']), [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => 'john@example.com',
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [$this->clubs[0]->id, $this->clubs[0]->id],
    ])->assertExactJson([
        'code' => 422,
        'messages' => [
            'El campo club_ids.0 contiene un valor duplicado.',
            'El campo club_ids.1 contiene un valor duplicado.',
        ],
    ]);

    Notification::assertNothingSent();
});

it('fails to store an app user when already attached to the club', function (): void {
    $existingAppUser = AppUser::factory()->createQuietly();
    $existingAppUser->clubs()->attach($this->clubs[0]->id);

    post(action([AppUserController::class, 'store']), [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => $existingAppUser->email,
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [$this->clubs[0]->id],
    ])
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

    post(action([AppUserController::class, 'store']), [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => $existingAppUser->email,
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [$this->clubs[0]->id],
    ])
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

    post(action([AppUserController::class, 'store']), [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => 'john@example.com',
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [$otherClub->id],
    ])
        ->assertExactJson([
            'code' => 422,
            'messages' => ['Los siguientes clubes no pertenecen al usuario autenticado: '.$otherClub->id.'.'],
        ]);

    Notification::assertNothingSent();

});

it('stores new app user with club_ids owned by authenticated club user', function (): void {
    $payload = [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => 'john@example.com',
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [$this->clubs[0]->id, $this->clubs[1]->id],
    ];

    post(action([AppUserController::class, 'store']), [
        ...$payload,
    ])
        ->assertStatus(201);

    unset($payload['club_ids']);
    $this->assertDatabaseHas('app_users', [
        ...$payload,
    ]);

    $this->assertDatabaseHas('app_user_club', [
        'club_id' => $this->clubs[0]->id,
        'club_id' => $this->clubs[1]->id,
    ]);

    Notification::assertSentTo(
        notifiable: AppUser::query()->where('email', $payload['email'])->firstOrFail(),
        notification: ResetPasswordNotification::class,
    );

});

it('attaches existing app user to clubs correctly', function (): void {
    $existingAppUser = AppUser::factory()->createQuietly();

    post(action([AppUserController::class, 'store']), [
        'name' => 'John',
        'last_name' => 'Doe',
        'phone_number' => '1234567890',
        'email' => $existingAppUser->email,
        'birthday' => '1990-01-01',
        'gender' => Gender::Male->value,
        'club_ids' => [$this->clubs[0]->id],
    ])
        ->assertStatus(201);

    $this->assertDatabaseHas('app_user_club', [
        'app_user_id' => $existingAppUser->id,
        'club_id' => $this->clubs[0]->id,
    ]);

    // Check dont create a new app user
    $this->assertDatabaseCount('app_user_club', 1);

    Notification::assertNothingSent();
});
