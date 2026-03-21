<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\RegisterClubUserController;
use App\Models\ClubUser;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\get;
use function Pest\Laravel\post;

beforeEach(function (): void {
    Notification::fake();

    $this->validPayload = [
        'email' => 'club-register@example.com',
        'password' => 'ValidPassword.123!',
        'password_confirmation' => 'ValidPassword.123!',
    ];
});

it('registers a club user and sends verification email', function (): void {
    post(action([RegisterClubUserController::class, 'store']), $this->validPayload)
        ->assertStatus(201);

    /** @var ClubUser $clubUser */
    $clubUser = ClubUser::query()->where('email', $this->validPayload['email'])->firstOrFail();

    expect(Hash::check($this->validPayload['password'], $clubUser->password))->toBeTrue();
    expect($clubUser->email_verified_at)->toBeNull();

    Notification::assertSentTo($clubUser, VerifyEmail::class);
});

it('fails to register a club user with invalid data', function (array $invalidData, array $expectedMessages): void {
    if (($invalidData['email'] ?? null) === 'club-duplicado@example.com') {
        ClubUser::factory()->create(['email' => 'club-duplicado@example.com']);
    }

    post(action([RegisterClubUserController::class, 'store']), array_merge($this->validPayload, $invalidData))
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

    Notification::assertNothingSent();
})->with([
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => ['El campo correo electrónico es obligatorio.'],
    ],
    'invalid email format' => [
        'invalidData' => ['email' => 'correo-invalido'],
        'expectedMessages' => ['El campo correo electrónico no es un correo válido.'],
    ],
    'duplicate email' => [
        'invalidData' => ['email' => 'club-duplicado@example.com'],
        'expectedMessages' => ['El campo correo electrónico ya ha sido registrado.'],
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
    $clubUser = ClubUser::factory()->unverified()->create();

    get(action([RegisterClubUserController::class, 'verifyEmail'], [
        'id' => $clubUser->id,
        'hash' => sha1($clubUser->getEmailForVerification()),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/club/auth/email-verification/success');

    expect($clubUser->fresh()?->hasVerifiedEmail())->toBeTrue();
});

it('fails email verification when club user does not exist', function (): void {
    get(action([RegisterClubUserController::class, 'verifyEmail'], [
        'id' => '999999',
        'hash' => sha1('missing@example.com'),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/club/auth/email-verification/fail');
});

it('fails email verification when hash is invalid', function (): void {
    $clubUser = ClubUser::factory()->unverified()->create();

    get(action([RegisterClubUserController::class, 'verifyEmail'], [
        'id' => $clubUser->id,
        'hash' => sha1('otro-correo@example.com'),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/club/auth/email-verification/fail');

    expect($clubUser->fresh()?->hasVerifiedEmail())->toBeFalse();
});

it('fails email verification when email is already verified', function (): void {
    $clubUser = ClubUser::factory()->create();

    get(action([RegisterClubUserController::class, 'verifyEmail'], [
        'id' => $clubUser->id,
        'hash' => sha1($clubUser->getEmailForVerification()),
    ]))
        ->assertRedirect(config('app.spa_url').'/#/club/auth/email-verification/fail');
});
