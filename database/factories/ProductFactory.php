<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->text,
            'composition' => $this->faker->text,
            'calories' => $this->faker->numberBetween(100,1000),
            'price' => $this->faker->numberBetween(100,1000),
            'category_id' => ProductCategory::factory()->create()->id
        ];
    }
}