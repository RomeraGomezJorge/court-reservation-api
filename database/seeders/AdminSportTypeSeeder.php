<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\SportType;
use Illuminate\Database\Seeder;

final class AdminSportTypeSeeder extends Seeder
{
    public function run(): void
    {
        $sportTypes = [
            'Padel',
            'Tenis',
            'Futbol',
        ];

        foreach ($sportTypes as $sportTypeName) {
            SportType::query()->updateOrCreate(
                ['name' => $sportTypeName],
                ['is_active' => true],
            );
        }
    }
}
