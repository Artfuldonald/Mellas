<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaxRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => 'VAT',
            'rate' => $this->faker->randomFloat(4, 0.05, 0.20), // 5% to 20%
            'priority' => 1,
            'is_active' => true,
        ];
    }
}