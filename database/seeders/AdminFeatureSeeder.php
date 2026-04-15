<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Feature;
use Illuminate\Database\Seeder;

final class AdminFeatureSeeder extends Seeder
{
    public function run(): void
    {
        $features = [
            'Iluminacion',
            'Techada',
            'Cesped sintetico',
            'Marcador digital',
        ];

        foreach ($features as $featureName) {
            Feature::query()->updateOrCreate(
                ['name' => $featureName],
                ['is_active' => true],
            );
        }
    }
}
