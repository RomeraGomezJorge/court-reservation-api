<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Enums\Gender;
use App\Http\Controllers\Club\ClubAppUserController;
use App\Models\AppUser;
use App\Models\Club;
use Illuminate\Support\Facades\Hash;

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

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('returns the app users related to the club with filters applied', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $otherClub = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $firstAppUser = AppUser::factory()->createQuietly([
        'name' => 'Carlos',
        'last_name' => 'Lopez',
        'phone_number' => '3415550001',
        'birthday' => '1990-01-01',
        'gender' => Gender::Male,
    ]);

    $secondAppUser = AppUser::factory()->createQuietly([
        'name' => 'Carla',
        'last_name' => 'Gomez',
        'phone_number' => '3415550002',
        'birthday' => '1991-02-02',
        'gender' => Gender::Female,
    ]);

    $otherAppUser = AppUser::factory()->createQuietly([
        'name' => 'Diego',
        'last_name' => 'Diaz',
        'phone_number' => '3415550003',
        'birthday' => '1992-03-03',
        'gender' => Gender::Other,
    ]);

    $club->appUsers()->attach([$firstAppUser->id, $secondAppUser->id]);
    $otherClub->appUsers()->attach($otherAppUser->id);

    get(action([ClubAppUserController::class, 'index'], ['club' => $club]).'?name=Car')
        ->assertStatus(200)
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.id', $secondAppUser->id)
        ->assertJsonPath('data.1.id', $firstAppUser->id);
});

it('stores an app user and attaches it to the club with a default password', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $payload = validAppUserPayload([
        'name' => 'Ana',
        'last_name' => 'Martinez',
        'phone_number' => '3415550100',
        'email' => null,
        'gender' => Gender::Female->value,
    ]);

    $response = post(action([ClubAppUserController::class, 'store'], ['club' => $club]), $payload);

    $appUser = AppUser::query()->where('phone_number', '3415550100')->firstOrFail();

    $response
        ->assertStatus(201)
        ->assertExactJson([
            'id' => $appUser->id,
            'name' => 'Ana',
            'last_name' => 'Martinez',
            'phone_number' => '3415550100',
            'birthday' => '1995-05-20',
            'gender' => Gender::Female->value,
            'email' => null,
        ]);

    expect(Hash::check('ChangeMe2026!', $appUser->password))->toBeTrue();
    expect($club->appUsers()->whereKey($appUser->id)->exists())->toBeTrue();
});

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

    post(action([ClubAppUserController::class, 'store'], ['club' => $club]), validAppUserPayload($invalidData))
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
    'duplicate phone number' => [
        'invalidData' => ['phone_number' => 'duplicado-telefono'],
        'expectedMessages' => ['El campo teléfono ya ha sido registrado.'],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'duplicado@example.com'],
        'expectedMessages' => ['El campo correo electrónico ya ha sido registrado.'],
    ],
    'future birthday' => [
        'invalidData' => ['birthday' => '2999-01-01'],
        'expectedMessages' => ['El campo birthday debe ser una fecha anterior o igual a today.'],
    ],
    'invalid gender' => [
        'invalidData' => ['gender' => 'invalid-gender'],
        'expectedMessages' => ['El campo gender no está en la lista de valores permitidos.'],
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

    get(action([ClubAppUserController::class, 'show'], ['club' => $club, 'app_user' => $appUser]))
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

    get(action([ClubAppUserController::class, 'show'], ['club' => $club, 'app_user' => $appUser]))
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

    $response = put(action([ClubAppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), validAppUserPayload([
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

it('fails to update an app user that does not belong to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    put(action([ClubAppUserController::class, 'update'], ['club' => $club, 'app_user' => $appUser]), validAppUserPayload())
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});

it('detaches an app user from the club without deleting it', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly([
        'phone_number' => '3415550400',
        'birthday' => '1990-07-07',
    ]);

    $club->appUsers()->attach($appUser->id);

    delete(action([ClubAppUserController::class, 'destroy'], ['club' => $club, 'app_user' => $appUser]))
        ->assertNoContent();

    expect($club->appUsers()->whereKey($appUser->id)->exists())->toBeFalse();
    expect(AppUser::query()->whereKey($appUser->id)->exists())->toBeTrue();
});

it('fails to delete an app user that does not belong to the club', function (): void {
    $club = Club::factory()->createQuietly([
        'club_user_id' => $this->clubUser->id,
    ]);

    $appUser = AppUser::factory()->createQuietly();

    delete(action([ClubAppUserController::class, 'destroy'], ['club' => $club, 'app_user' => $appUser]))
        ->assertStatus(404)
        ->assertExactJson([
            'code' => 404,
            'messages' => ['El recurso no se ha encontrado.'],
        ]);
});
