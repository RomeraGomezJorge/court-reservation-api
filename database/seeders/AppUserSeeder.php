<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\AppUser;
use Illuminate\Database\Seeder;

final class AppUserSeeder extends Seeder
{
    public function run(): void
    {
        AppUser::factory(10)->create();
    }
}

