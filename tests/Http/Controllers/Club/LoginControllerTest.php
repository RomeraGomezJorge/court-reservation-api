<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\LoginController;
use App\Models\ClubUser;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\post;

beforeEach(function (): void {
    Notification::fake();

    $this->plaintextPassword = 'P@ssw0rd12345';

    $this->clubUser = ClubUser::factory()->create([
        'email' => 'club@example.com',
        'password' => $this->plaintextPassword,
    ]);
});

it('logs in a club user successfully', function (): void {
    post(action([LoginController::class, 'login']), [
        'email' => $this->clubUser->email,
        'password' => $this->plaintextPassword,
    ])->assertOk();
});

it('fails login with non existing club user email', function (): void {
    post(action([LoginController::class, 'login']), [
        'email' => 'unknown@example.com',
        'password' => $this->plaintextPassword,
    ])
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => [__('auth.failed')],
        ]);
});

it('fails login with incorrect password', function (): void {
    post(action([LoginController::class, 'login']), [
        'email' => $this->clubUser->email,
        'password' => 'WrongP@ssw0rd12345',
    ])
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => [__('auth.failed')],
        ]);
});

it('fails login when email is not verified', function (): void {
    $this->clubUser->forceFill(['email_verified_at' => null])->save();

    post(action([LoginController::class, 'login']), [
        'email' => $this->clubUser->email,
        'password' => $this->plaintextPassword,
    ])
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => [__('auth.email_not_verified')],
        ]);
});

it('fails login with invalid payload', function (array $invalidData, array $expectedMessages): void {
    post(action([LoginController::class, 'login']), array_merge([
        'email' => $this->clubUser->email,
        'password' => $this->plaintextPassword,
    ], $invalidData))
        ->assertStatus(422)
        ->assertExactJson([
            'code' => 422,
            'messages' => $expectedMessages,
        ]);
})->with([
    'empty email' => [
        'invalidData' => ['email' => ''],
        'expectedMessages' => [__('validation.required', ['attribute' => __('validation.attributes.email')])],
    ],
    'invalid email format' => [
        'invalidData' => ['email' => 'invalid-email'],
        'expectedMessages' => [__('validation.email', ['attribute' => __('validation.attributes.email')])],
    ],
    'empty password' => [
        'invalidData' => ['password' => ''],
        'expectedMessages' => [__('validation.required', ['attribute' => __('validation.attributes.password')])],
    ],
]);

