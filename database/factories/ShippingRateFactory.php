<?php
namespace Database\Factories;
use App\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingRateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shipping_zone_id' => ShippingZone::factory(),
            'name' => $this->faker->randomElement(['Standard', 'Express', 'Next Day']),
            'cost' => $this->faker->randomFloat(2, 5, 50),
            'is_active' => true,
            'description' => $this->faker->sentence(),
        ];
    }
}