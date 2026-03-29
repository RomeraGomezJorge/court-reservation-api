<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Club;
use App\Models\Court;
use App\Models\SportType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Court>
 */
final class CourtFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'sport_type_id' => SportType::factory(),
            'name' => fake()->unique()->bothify('Court-##??'),
            'description' => fake()->optional()->sentence(),
            'is_available' => fake()->boolean(80),
        ];
    }

    public function available(): self
    {
        return $this->state(fn (): array => [
            'is_available' => true,
        ]);
    }

    public function unavailable(): self
    {
        return $this->state(fn (): array => [
            'is_available' => false,
        ]);
    }
}
