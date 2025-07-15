<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Support\Str;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        $name = $this->faker->unique()->words(3, true);
        $price = $this->faker->randomFloat(2, 20, 500);

        return [
            // --- Basic Info ---
            'name' => Str::title($name),
            'slug' => Str::slug($name),
            'description' => '<h3>' . $this->faker->sentence(5) . '</h3><p>' . implode('</p><p>', $this->faker->paragraphs(4)) . '</p>',
            'short_description' => $this->faker->sentence(20),
            'brand_id' => Brand::inRandomOrder()->first()->id ?? Brand::factory(),

            // --- Specifications (JSON field) ---
            'specifications' => [
                ['key' => 'Material', 'value' => $this->faker->randomElement(['Cotton', 'Polyester', 'Leather', 'Aluminum'])],
                ['key' => 'Origin', 'value' => $this->faker->country()],
                ['key' => 'Warranty', 'value' => $this->faker->randomElement(['1 Year', '2 Years', 'No Warranty'])],
                ['key' => 'Model Number', 'value' => 'MDL-' . $this->faker->randomNumber(6)],
            ],
            
            // --- Pricing ---
            'price' => $price,
            'compare_at_price' => $this->faker->optional(0.4)->randomFloat(2, $price + 10, $price + 100), // 40% chance of having a discount
            'cost_price' => $this->faker->optional(0.7)->randomFloat(2, 10, $price - 5),

            // --- Status Flags ---
            'is_active' => true,
            'is_featured' => $this->faker->boolean(25), // 25% chance of being featured

            // --- SEO ---
            'meta_title' => Str::title($name) . ' | Mella\'s Connect',
            'meta_description' => Str::limit($this->faker->sentence(25), 155),

            // --- Shipping ---
            'weight' => $this->faker->randomFloat(2, 0.1, 5.0),
            'weight_unit' => 'kg',
            'dimensions' => $this->faker->numberBetween(10, 50) . 'x' . $this->faker->numberBetween(10, 50) . 'x' . $this->faker->numberBetween(2, 20) . ' cm',

            // Note: 'sku' and 'quantity' are handled by the simple() state factory
        ];
    }

    /**
     * Configure the model factory.
     * Use an afterCreating hook to attach images, making it reusable for all states.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {         
            ProductImage::factory(rand(3, 6))->create([ // Create a few more images for better gallery testing
                'product_id' => $product->id,
            ]);
        });
    }
   
    /**
     * Define a state for a simple product without variants.
     */
    public function simple(): Factory
    {
        return $this->state(function (array $attributes) {
            return [
                'sku' => $this->faker->unique()->bothify('PROD-######'),
                'quantity' => $this->faker->numberBetween(10, 200),
            ];
        });
    }
}