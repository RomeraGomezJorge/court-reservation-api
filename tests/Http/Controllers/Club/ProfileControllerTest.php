<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Club;

use App\Http\Controllers\Club\ProfileController;

use function Pest\Laravel\get;

it('shows authenticated club profile', function (): void {
    $clubUser = actingAsClubUser();

    get(action([ProfileController::class, 'show']))
        ->assertOk()
        ->assertExactJson([
            'id' => $clubUser->id,
            'email' => $clubUser->email,
            'roles' => ['club'],
        ]);
});
