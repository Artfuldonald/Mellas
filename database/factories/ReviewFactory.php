<?php
namespace Database\Factories;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'reviewer_name' => $this->faker->name(),
            'reviewer_email' => $this->faker->safeEmail(),
            'rating' => $this->faker->numberBetween(3, 5),
            'title' => $this->faker->sentence(4),
            'comment' => $this->faker->paragraph(2),
            'is_approved' => $this->faker->boolean(80), // 80% chance of being approved
        ];
    }
}