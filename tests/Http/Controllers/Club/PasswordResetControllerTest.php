<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\PasswordResetController;
use App\Models\ClubUser;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;

use function Pest\Laravel\post;
use function Pest\Laravel\put;

beforeEach(function (): void {
    Notification::fake();

    $this->clubUser = ClubUser::factory()->create([
        'email' => 'club@example.com',
    ]);
});

it('sends password reset link for club user', function (): void {
    post(action([PasswordResetController::class, 'store']), [
        'email' => $this->clubUser->email,
    ])->assertStatus(201);

    Notification::assertSentTo($this->clubUser, ResetPassword::class);

    $this->assertDatabaseHas('password_reset_tokens', [
        'email' => $this->clubUser->email,
    ]);
});

it('fails to send password reset link with invalid data', function (array $invalidData, array $expectedMessages): void {
    post(action([PasswordResetController::class, 'store']), array_merge([
        'email' => $this->clubUser->email,
    ], $invalidData))
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);

    Notification::assertNothingSent();
    $this->assertDatabaseEmpty('password_reset_tokens');
})->with([
//    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => [__('validation.required', ['attribute' => __('validation.attributes.email')])],
//    ],
//    'email does not exist' => [
//        'invalidData' => ['email' => 'unknown@example.com'],
//        'expectedMessages' => [__('validation.exists', ['attribute' => __('validation.attributes.email')])],
//    ],
]);


it('resets a club user password', function (): void {
    $newPassword = 'NewValidP@ss1!';
    $token = Password::broker('club_users')->createToken($this->clubUser);

    put(action([PasswordResetController::class, 'update']), [
        'token' => $token,
        'email' => $this->clubUser->email,
        'password' => $newPassword,
        'password_confirmation' => $newPassword,
    ])->assertNoContent();

    $this->clubUser->refresh();

    expect(Hash::check($newPassword, $this->clubUser->password))->toBeTrue();

});

it('fails to reset password with invalid data', function (array $invalidData, array $expectedMessages): void {
    $token = Password::broker('club_users')->createToken($this->clubUser);

    put(action([PasswordResetController::class, 'update']), array_merge([
        'token' => $token,
        'email' => $this->clubUser->email,
        'password' => 'ValidPassword.123!',
        'password_confirmation' => 'ValidPassword.123!',
    ], $invalidData))
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'empty token' => [
        'invalidData' => ['token' => ''],
        'expectedMessages' => [__('validation.required', ['attribute' => 'token'])],
    ],
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => [__('validation.required', ['attribute' => __('validation.attributes.email')])],
    ],
    'email does not exist' => [
        'invalidData' => ['email' => 'unknown@example.com'],
        'expectedMessages' => [__('validation.exists', ['attribute' => __('validation.attributes.email')])],
    ],
    'empty password and confirmation' => [
        'invalidData' => ['password' => '', 'password_confirmation' => ''],
        'expectedMessages' => [
            __('validation.required', ['attribute' => __('validation.attributes.password')]),
            __('validation.required', ['attribute' => __('validation.attributes.password_confirmation')]),
        ],
    ],
    'password confirmation does not match' => [
        'invalidData' => [
            'password' => 'ValidPassword.123!',
            'password_confirmation' => 'DifferentPassword.123!',
        ],
        'expectedMessages' => [
            __('validation.same', [
                'attribute' => __('validation.attributes.password_confirmation'),
                'other' => __('validation.attributes.password'),
            ]),
        ],
    ],
]);

