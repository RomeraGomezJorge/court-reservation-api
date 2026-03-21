<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClubWorkingDays;
use App\Models\Club;
use App\Models\ClubWorkingDay;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubWorkingDay>
 */
final class ClubWorkingDayFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'day' => fake()->randomElement(ClubWorkingDays::cases()),
            'opening_hour' => fake()->time('H:i'),
            'closing_hour' => fake()->time('H:i'),
        ];
    }
}

