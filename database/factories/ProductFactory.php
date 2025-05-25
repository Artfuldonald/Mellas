<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
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

     protected $model = Product::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true); // Generate a unique 3-word name
        $price = $this->faker->randomFloat(2, 10, 200); // Price between 10 and 200
        
        return [
            'name' => Str::title($name), // Make SURE this key is exactly 'name'
            'slug' => Str::slug($name),
            'description' => $this->faker->paragraph(),
            'price' => $price,
            'compare_at_price' => $this->faker->optional(0.3)->randomFloat(2, $price + 5, $price + 50), // Optional, higher than price
            'cost_price' => $this->faker->optional(0.5)->randomFloat(2, 5, $price - 5), // Optional, lower than price
            'is_active' => true,
            'is_featured' => $this->faker->boolean(15), // 15% chance of being featured

            // Nullable fields (can be omitted or explicitly set to null/faker value)
            'sku' => null, // Set to null for products with variants by default
            'quantity' => 0, // Set to 0 for products with variants by default
            'meta_title' => $this->faker->optional(0.4)->sentence(6),
            'meta_description' => $this->faker->optional(0.4)->sentence(15),
            'weight' => $this->faker->optional(0.7)->randomFloat(2, 0.1, 5),
            'weight_unit' => 'kg', // Default from migration, but good to be explicit
            'dimensions' => null, // Or generate fake JSON if needed: json_encode(['L'=>10,'W'=>5,'H'=>2])
        ];
    }
}