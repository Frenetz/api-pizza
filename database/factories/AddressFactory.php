<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city' => $this->faker->city,
            'street' => $this->faker->streetName,
            'house_number' => $this->faker->buildingNumber,
            'apartment_number' => $this->faker->numberBetween(1, 100),
            'entrance' => $this->faker->randomElement(['A', 'B', 'C', 'D']),
            'floor' => $this->faker->numberBetween(1, 20),
            'intercom' => $this->faker->randomNumber(4),
            'gate' => $this->faker->boolean,
            'comment' => $this->faker->text,
            'user_id' => User::factory()->create()->id,
        ];


    }
}
