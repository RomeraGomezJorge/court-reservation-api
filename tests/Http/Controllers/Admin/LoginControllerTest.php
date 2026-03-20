<?php

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\LoginController;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\post;

beforeEach(function (): void {
    Notification::fake();
    $this->plaintextPassword = 'P@ssw0rd12345';

    /** @var User $user */
    $this->user = User::factory()
        ->create([
            'email' => 'test@example.com',
            'password' => $this->plaintextPassword,
        ]);
});

it('logs in successfully', function (): void {

    $data = [
        'email' => 'test@example.com',
        'password' => $this->plaintextPassword,
    ];

    $response = post(action([LoginController::class, 'login']), $data);

    $response->assertOk();
});

it('fails login with non exist user email', function (): void {

    $data = [
        'email' => 'invalid-user-emailt@example.com',
        'password' => 'P@ssw0rd12345',
    ];

    $response = post(action([LoginController::class, 'login']), $data);

    $response->assertStatus(422);
    $response->assertJson([
        'messages' => [__('auth.failed')],
        'code' => 422,
    ]);
});

it('fails login with incorrect password', function (): void {
    $data = [
        'email' => $this->user->email,
        'password' => 'Wr0ngP@ssw0rd12345',
    ];

    $response = post(action([LoginController::class, 'login']), $data);

    $response->assertStatus(422);
    $response->assertJson([
        'messages' => ['Estas credenciales no coinciden con nuestros registros.'],
        'code' => 422,
    ]);
});

it('fails login with unverify email', function (): void {

    $this->user->update(['email_verified_at' => null]);

    $data = [
        'email' => $this->user->email,
        'password' => $this->plaintextPassword,
    ];

    $response = post(action([LoginController::class, 'login']), $data);

    $response->assertStatus(422);
    $response->assertJson([
        'messages' => [__('auth.email_not_verified')],
        'code' => 422,
    ]);
});
