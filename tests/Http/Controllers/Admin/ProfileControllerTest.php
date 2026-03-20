<?php

declare(strict_types=1);

namespace Tests\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ProfileController;

use function Pest\Laravel\get;

it('shows authenticated admin profile', function (): void {
    $user = actingAsUser();

    get(action([ProfileController::class, 'show']))
        ->assertOk()
        ->assertExactJson([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => ['super_admin'],
        ]);
});
