<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\WorkingDays;
use App\Models\Court;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;
use Illuminate\Database\Seeder;

final class CourtPriceRuleItemSeeder extends Seeder
{
    public function run(): void
    {
        $court = Court::query()->where('name', 'Cancha Padel Central')->firstOrFail();
        $genericRule = $this->findRule($court->id, null);
        $mondayRule = $this->findRule($court->id, WorkingDays::Monday->value);

        $this->syncRuleItems($genericRule->id, $this->genericItems());
        $this->syncRuleItems($mondayRule->id, $this->mondayItems());
    }

    /**
     * @return array<int, array{play_time_minutes: int, price_starts_at: string, price: int}>
     */
    private function genericItems(): array
    {
        return [
            ['play_time_minutes' => 60, 'price_starts_at' => '09:00:00', 'price' => 3000],
            ['play_time_minutes' => 60, 'price_starts_at' => '12:00:00', 'price' => 4000],
            ['play_time_minutes' => 60, 'price_starts_at' => '18:00:00', 'price' => 5000],
            ['play_time_minutes' => 90, 'price_starts_at' => '09:00:00', 'price' => 4500],
            ['play_time_minutes' => 90, 'price_starts_at' => '12:00:00', 'price' => 5200],
            ['play_time_minutes' => 90, 'price_starts_at' => '18:00:00', 'price' => 6500],
        ];
    }

    /**
     * @return array<int, array{play_time_minutes: int, price_starts_at: string, price: int}>
     */
    private function mondayItems(): array
    {
        return [
            ['play_time_minutes' => 60, 'price_starts_at' => '00:00:00', 'price' => 2800],
            ['play_time_minutes' => 90, 'price_starts_at' => '00:00:00', 'price' => 4200],
        ];
    }

    private function findRule(int|string $courtId, ?string $day): CourtPriceRule
    {
        return CourtPriceRule::query()->where('court_id', $courtId)->where('day', $day)->firstOrFail();
    }

    /**
     * @param  array<int, array{play_time_minutes: int, price_starts_at: string, price: int}>  $items
     */
    private function syncRuleItems(int|string $priceRuleId, array $items): void
    {
        foreach ($items as $item) {
            CourtPriceRuleItem::query()->updateOrCreate(
                [
                    'court_price_rule_id' => $priceRuleId,
                    'play_time_minutes' => $item['play_time_minutes'],
                    'price_starts_at' => $item['price_starts_at'],
                ],
                [
                    'price' => $item['price'],
                ],
            );
        }
    }
}
