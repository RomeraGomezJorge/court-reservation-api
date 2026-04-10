<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\CourtPriceRuleDay;
use App\Models\Court;
use App\Models\CourtPriceRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourtPriceRule>
 */
final class CourtPriceRuleFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'court_id' => Court::factory(),
            'day' => CourtPriceRuleDay::Base->value,
        ];
    }

    public function forCourt(Court $court): self
    {
        return $this->state(fn (): array => [
            'court_id' => $court->id,
        ]);
    }

    public function forDay(CourtPriceRuleDay|string $day): self
    {
        $dayValue = match (true) {
            $day instanceof CourtPriceRuleDay => $day->value,
            default => $day,
        };

        return $this->state(fn (): array => [
            'day' => $dayValue,
        ]);
    }

    public function base(): self
    {
        return $this->state(fn (): array => [
            'day' => CourtPriceRuleDay::Base->value,
        ]);
    }
}
