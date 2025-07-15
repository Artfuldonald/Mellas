<?php
namespace Database\Factories;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BrandFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->company();
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => $this->faker->sentence(),
            'logo_path' => null, // Typically uploaded, so null by default
            'is_active' => true,
        ];
    }
}