<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\PasswordResetController;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    Notification::fake();

    $this->user = User::factory()->createQuietly([
        'email' => 'admin@example.com',
    ]);
});

it('sends password reset link', function (): void {
    post(action([PasswordResetController::class, 'store']), [
        'email' => $this->user->email,
    ])->assertStatus(201);

    Notification::assertSentTo($this->user, ResetPassword::class);

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $this->user->email,
    ]);
});

it('fails to send password reset link with invalid data', function (array $invalidData, array $expectedMessages): void {
    post(action([PasswordResetController::class, 'store']), array_merge([
        'email' => $this->user->email,
    ], $invalidData))
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

    Notification::assertNothingSent();

    $this->assertDatabaseEmpty('password_reset_tokens');
})->with([
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => ['El campo correo electrónico es obligatorio.'],
    ],
    'email does not exist' => [
        'invalidData' => ['email' => 'unknown@example.com'],
        'expectedMessages' => ['El campo correo electrónico no existe.'],
    ],
]);

it('resets password', function (): void {
    $newPassword = 'NewValidP@ss1!';
    $token = Password::createToken($this->user);

    put(action([PasswordResetController::class, 'update']), [
        'token' => $token,
        'email' => $this->user->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])->assertNoContent();

    $this->user->refresh();

    expect(Hash::check($newPassword, $this->user->password))->toBeTrue();
});

it('fails to reset password with invalid data', function (array $invalidData, array $expectedMessages): void {
    $token = Password::createToken($this->user);

    $payload = array_merge([
        'token' => $token,
        'email' => $this->user->email,
        'password' => 'ValidPassword.123!',
        'password_confirmation' => 'ValidPassword.123!',
    ], $invalidData);

    put(action([PasswordResetController::class, 'update']), $payload)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

})->with([
    'empty token' => [
        'invalidData' => ['token' => ''],
        'expectedMessages' => ['El campo token es obligatorio.'],
    ],
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => ['El campo correo electrónico es obligatorio.'],
    ],
    'email does not exist' => [
        'invalidData' => ['email' => 'unknown@example.com'],
        'expectedMessages' => ['El campo correo electrónico no existe.'],
    ],
    'empty password' => [
        'invalidData' => ['password' => '', 'password_confirmation' => ''],
        'expectedMessages' => ['El campo contraseña es obligatorio.'],
    ],
    'password too short' => [
        'invalidData' => ['password' => 'Short1!', 'password_confirmation' => 'Short1!'],
        'expectedMessages' => ['El campo contraseña debe contener al menos 12 caracteres.'],
    ],
    'password confirmation does not match' => [
        'invalidData' => [
            'password' => 'ValidPassword.123!',
            'password_confirmation' => 'DifferentPassword.123!',
        ],
        'expectedMessages' => ['La confirmación de contraseña no coincide.'],
    ],
    'password too long' => [
        'invalidData' => [
            'password' => str_repeat('Aa1!', 70),
            'password_confirmation' => str_repeat('Aa1!', 70),
        ],
        'expectedMessages' => [
            'El campo contraseña no debe ser mayor que 72 caracteres.',
        ],
    ],
]);

it('fails to reset password when token is invalid or expired', function (): void {
    $invalidToken = 'invalid-expired-token-1234567890';

    put(action([PasswordResetController::class, 'update']), [
        'token' => $invalidToken,
        'email' => $this->user->email,
        'password' => 'ValidPassword.123!',
        'password_confirmation' => 'ValidPassword.123!',
    ])
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => ['El token de restablecimiento de contraseña es inválido.'],
        ]);

    $this->user->refresh();

    expect(Hash::check('ValidPassword.123!', $this->user->password))->toBeFalse();
});
