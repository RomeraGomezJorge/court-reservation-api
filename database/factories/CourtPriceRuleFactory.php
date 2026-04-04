<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\WorkingDays;
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
            'day' => null,
        ];
    }

    public function forCourt(Court $court): self
    {
        return $this->state(fn (): array => [
            'court_id' => $court->id,
        ]);
    }

    public function forDay(WorkingDays $day): self
    {
        return $this->state(fn (): array => [
            'day' => $day->value,
        ]);
    }

    public function generic(): self
    {
        return $this->state(fn (): array => [
            'day' => null,
        ]);
    }
}
