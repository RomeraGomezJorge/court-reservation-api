<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\CourtPriceRuleDay;
use App\Models\Court;
use App\Models\CourtPriceRule;
use Illuminate\Database\Seeder;

final class CourtPriceRuleSeeder extends Seeder
{
    public function run(): void
    {
        $court = Court::query()->where('name', 'Cancha Padel Central')->firstOrFail();

        CourtPriceRule::query()->updateOrCreate(
            [
                'court_id' => $court->id,
                'day' => CourtPriceRuleDay::Base->value,
            ],
        );

        CourtPriceRule::query()->updateOrCreate(
            [
                'court_id' => $court->id,
                'day' => CourtPriceRuleDay::Monday->value,
            ],
        );
    }
}
