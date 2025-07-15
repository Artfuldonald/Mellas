<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(2, true), // The seeder will override this with a generated name
            'sku' => $this->faker->unique()->bothify('VAR-####-????'),
            'price' => $this->faker->randomFloat(2, 10, 200),
            'quantity' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}