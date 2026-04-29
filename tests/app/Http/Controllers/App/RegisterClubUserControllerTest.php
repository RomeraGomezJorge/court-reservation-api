<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\AppUser;

use App\Enums\Gender;
use App\Http\Controllers\App\RegisterAppUserController;
use App\Models\AppUser;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function (): void {
    Notification::fake();

    $this->validPayload = [
        'name' => fake()->name,
        'last_name' => fake()->lastName,
        'phone_number' => fake()->phoneNumber,
        'birthday' => fake()->date('Y-m-d', '-18 years'),
        'gender' => Gender::Male->value,
        'email' => 'app-user-register@example.com',
        'password' => 'ValidPassword.123!',
        'password_confirmation' => 'ValidPassword.123!',
    ];
});

it('registers an app user and sends verification email', function (): void {
    post(action([RegisterAppUserController::class, 'store']), $this->validPayload)
        ->assertStatus(201);

    /** @var AppUser $appUser */
    $appUser = AppUser::query()->where('email', $this->validPayload['email'])->firstOrFail();

    expect(Hash::check($this->validPayload['password'], $appUser->password))->toBeTrue();
    expect($appUser->email_verified_at)->toBeNull();

    Notification::assertSentTo($appUser, VerifyEmail::class);
});

it('fails to register an app user with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['email'] ?? null) === 'app-duplicado@example.com') {
        AppUser::factory()->createQuietly(['email' => 'app-duplicado@example.com']);
    }

    if (($invalidData['phone_number'] ?? null) === 'duplicado-telefono') {
        AppUser::factory()->createQuietly(['phone_number' => 'duplicado-telefono']);
    }

    post(action([RegisterAppUserController::class, 'store']), array_merge($this->validPayload, $invalidData))
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

    Notification::assertNothingSent();
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
        'invalidData' => ['email' => 'app-duplicado@example.com'],
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
    'password too short' => [
        'invalidData' => [
            'password' => 'Short1!',
            'password_confirmation' => 'Short1!',
        ],
        'expectedMessages' => ['El campo contraseña debe contener al menos 12 caracteres.'],
    ],
    'password without mixed case' => [
        'invalidData' => [
            'password' => 'lowercase1234!',
            'password_confirmation' => 'lowercase1234!',
        ],
        'expectedMessages' => ['La contraseña debe contener al menos una letra mayúscula y una minúscula.'],
    ],
    'password without numbers' => [
        'invalidData' => [
            'password' => 'ValidPassword!!!',
            'password_confirmation' => 'ValidPassword!!!',
        ],
        'expectedMessages' => ['La contraseña debe contener al menos un número.'],
    ],
    'password without symbols' => [
        'invalidData' => [
            'password' => 'ValidPassword1234',
            'password_confirmation' => 'ValidPassword1234',
        ],
        'expectedMessages' => ['La contraseña debe contener al menos un símbolo.'],
    ],
    'password confirmation mismatch' => [
        'invalidData' => [
            'password_confirmation' => 'AnotherPassword.123!',
        ],
        'expectedMessages' => ['La confirmación de contraseña no coincide.'],
    ],
]);


it('verifies email and redirects to success page', function (): void {
    $appUser = AppUser::factory()->unverified()->createQuietly();

    get(action([RegisterAppUserController::class, 'verifyEmail'], [
        'id' => $appUser->id,
        'hash' => sha1($appUser->getEmailForVerification()),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/app/auth/email-verification/success');

    expect($appUser->fresh()?->hasVerifiedEmail())->toBeTrue();
});

it('fails email verification when app user does not exist', function (): void {
    get(action([RegisterAppUserController::class, 'verifyEmail'], [
        'id' => '999999',
        'hash' => sha1('missing@example.com'),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/app/auth/email-verification/fail');
});

it('fails email verification when hash is invalid', function (): void {
    $appUser = AppUser::factory()->unverified()->createQuietly();

    get(action([RegisterAppUserController::class, 'verifyEmail'], [
        'id' => $appUser->id,
        'hash' => sha1('otro-correo@example.com'),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/app/auth/email-verification/fail');

    expect($appUser->fresh()?->hasVerifiedEmail())->toBeFalse();
});

it('fails email verification when email is already verified', function (): void {
    $appUser = AppUser::factory()->createQuietly();

    get(action([RegisterAppUserController::class, 'verifyEmail'], [
        'id' => $appUser->id,
        'hash' => sha1($appUser->getEmailForVerification()),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/app/auth/email-verification/fail');
});
