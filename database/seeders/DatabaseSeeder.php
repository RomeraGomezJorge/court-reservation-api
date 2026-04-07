<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            AdminSportTypeSeeder::class,
            AdminFeatureSeeder::class,
            ClubWithCourtSeeder::class,
            ClubServiceSeeder::class,
            ClubWorkingDaySeeder::class,
            CourtPriceRuleSeeder::class,
            CourtPriceRuleItemSeeder::class,
        ]);
    }
}
