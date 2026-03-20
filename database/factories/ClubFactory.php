<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Club;
use App\Models\ClubUser;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Club>
 */
final class ClubFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'club_user_id' => ClubUser::factory(),
            'address_city' => fake()->city(),
            'address_country' => fake()->country(),
            'address_postal_code' => fake()->postcode(),
            'address_state' => fake()->state(),
            'address_street' => fake()->streetAddress(),
            'description' => fake()->paragraph(),
            'facebook_url' => fake()->optional()->url(),
            'instagram_url' => fake()->optional()->url(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'operating_hours_additional_info' => fake()->optional()->sentence(),
            'organization_name' => fake()->unique()->company(),
            'phone_number' => fake()->optional()->phoneNumber(),
            'reservation_policies_and_payment_terms' => fake()->paragraph(),
            'twitter_url' => fake()->optional()->url(),
            'whatsapp_number' => fake()->optional()->phoneNumber(),
            'is_active' => fake()->boolean(80), // 80% true
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => false,
        ]);
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes): array => [
            'is_active' => true,
        ]);
    }
}
