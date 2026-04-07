<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin9@admin.com'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('Admin123456!'),
                'email_verified_at' => now(),
            ],
        );

        User::factory()->count(2)->create();
    }
}
