<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\ProductVariant;
use App\Models\Product; 
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = ProductVariant::class;

    public function definition(): array
    {
        // Generate a somewhat unique SKU part
        $skuSuffix = strtoupper(Str::random(4)) . $this->faker->randomNumber(3);
        $variantName = $this->faker->colorName(); // Simple example name

        return [
           // 'product_id' will usually be set by the relationship when calling ->hasVariants()
            // 'product_id' => Product::factory(), // Only needed if creating variant directly

            'name' => $variantName, // You might generate this based on attributes later
            'sku' => 'VAR-' . $skuSuffix,
            'price' => $this->faker->randomFloat(2, 5, 100), // Price between 5 and 100
            'quantity' => $this->faker->numberBetween(0, 100),
            'is_active' => true,
        ];
    }
}