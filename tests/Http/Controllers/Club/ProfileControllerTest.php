<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\ProfileController;

use function Pest\Laravel\delete;
use function Pest\Laravel\get;

beforeEach(function (): void {
    $this->clubUser = actingAsClubUser();
});

it('shows authenticated club profile', function (): void {
    get(action([ProfileController::class, 'show']))
        ->assertOk()
        ->assertExactJson([
            'id' => $this->clubUser->id,
            'email' => $this->clubUser->email,
            'roles' => ['club'],
        ]);
});

it('deletes a club', function (): void {
    $email = $this->clubUser->email;

    delete(
        action([
            ProfileController::class,
            'destroy',
        ]),
    )->assertNoContent();

    $this->clubUser->refresh();
    $this->assertSoftDeleted($this->clubUser);
    $this->assertNotEquals($email, $this->clubUser->email);
});
