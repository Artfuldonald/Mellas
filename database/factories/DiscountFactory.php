<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed_amount']);
        $value = ($type === 'percentage')
            ? $this->faker->numberBetween(10, 50)
            : $this->faker->randomFloat(2, 5, 25);

        return [
            'code' => strtoupper($this->faker->unique()->word() . $this->faker->numberBetween(100, 999)),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'value' => $value,
            'min_spend' => $this->faker->optional(0.5)->randomFloat(2, 50, 100),
            'max_uses' => $this->faker->optional(0.7)->numberBetween(100, 1000),
            'max_uses_per_user' => $this->faker->optional(0.8)->numberBetween(1, 3),
            'starts_at' => now()->subDays(rand(1, 10)),
            'expires_at' => now()->addDays(rand(5, 30)),
            'is_active' => true,
        ];
    }
}