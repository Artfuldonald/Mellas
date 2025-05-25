<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrderItem>
 */
class OrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

     protected $model = OrderItem::class;
     
    public function definition(): array
    {
        // IMPORTANT: Assumes you have at least one ProductVariant in your DB
        // If not, create one manually or seed products/variants first.
        $variant = ProductVariant::with('product')->inRandomOrder()->first();
        // Fallback to a simple product if no variants exist (less ideal for testing variants)
        $product = $variant ? $variant->product : Product::query()->whereDoesntHave('variants')->inRandomOrder()->first();

        if (!$variant && !$product) {
             // Handle case where no products/variants exist at all
             // You might throw an exception or create a dummy product here
             // For now, we'll use placeholder data
             return [
                'order_id' => \App\Models\Order::factory(), // Needs an order_id, usually set via relationship
                'product_id' => null,
                'product_variant_id' => null,
                'product_name' => 'Placeholder Product',
                'variant_name' => null,
                'sku' => 'PLACEHOLDER-SKU',
                'price' => 10.00,
                'quantity' => 1,
                'line_total' => 10.00,
             ];
        }

        $itemPrice = $variant ? $variant->price : $product->price;
        $quantity = $this->faker->numberBetween(1, 3);

        return [
            // 'order_id' will be set when using the factory via relationship (e.g., Order::factory()->hasItems(3))
            'product_id' => $variant ? null : $product->id,
            'product_variant_id' => $variant ? $variant->id : null,
            'product_name' => $product->name, // Copy name at time of order
            'variant_name' => $variant ? $variant->name : null, // Copy variant name
            'sku' => $variant ? $variant->sku : $product->sku, // Copy SKU
            'price' => $itemPrice, // Copy price
            'quantity' => $quantity,
            'line_total' => $itemPrice * $quantity,
        ];
    }
}