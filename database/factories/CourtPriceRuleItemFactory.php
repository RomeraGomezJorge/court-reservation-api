<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\PlayTime;
use App\Models\CourtPriceRule;
use App\Models\CourtPriceRuleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtPriceRuleItem>
 */
final class CourtPriceRuleItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'court_price_rule_id' => CourtPriceRule::factory(),
            'play_time_minutes' => fake()->randomElement(PlayTime::cases()),
            'price' => fake()->randomFloat(2, 25, 150),
            'price_starts_at' => fake()->time('H:i:s'),
        ];
    }

    public function forRule(CourtPriceRule $priceRule): self
    {
        return $this->state(fn (): array => [
            'court_price_rule_id' => $priceRule->id,
        ]);
    }

    public function forPlayTimeMinutes(int $minutes): self
    {
        return $this->state(fn (): array => [
            'play_time_minutes' => $minutes,
        ]);
    }

    public function startingAt(string $time): self
    {
        return $this->state(fn (): array => [
            'price_starts_at' => $time,
        ]);
    }
}
