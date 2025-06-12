<?php

use App\Models\Product;
use Illuminate\Support\Str;

if (!function_exists('is_product_in_cart')) {
    /**
     * Checks if a product (or any of its variants) is in the session cart.
     *
     * @param Product|null $product
     * @return bool
     */
    function is_product_in_cart(?Product $product): bool
    {
        if (!$product) {
            return false;
        }

        $cart = session('cart', []);

        // Check for the simple product itself (e.g., key '4')
        if (isset($cart[$product->id])) {
            return true;
        }

        // If it's a product with variants, check if any cart item key starts with "productId-"
        // We can check this even if variants aren't loaded, using the count.
        if ($product->variants_count > 0) {
            foreach (array_keys($cart) as $cartItemId) {
                if (is_string($cartItemId) && Str::startsWith($cartItemId, $product->id . '-')) {
                    return true;
                }
            }
        }

        return false;
    }
}