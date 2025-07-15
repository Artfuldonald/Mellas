<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->country() . ' Zone',
            'is_active' => true,
        ];
    }
}