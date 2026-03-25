<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ClubServicesType;
use App\Models\Club;
use App\Models\ClubService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClubService>
 */
final class ClubServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_id' => Club::factory(),
            'type' => fake()->randomElement(ClubServicesType::cases()),
        ];
    }
}
