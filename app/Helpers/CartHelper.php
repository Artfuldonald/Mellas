<?php

namespace App\Helpers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class CartHelper
{
    /**
     * Get the current cart items for the user or session.
     * This can be a singleton to avoid multiple DB calls per request.
     */
    protected static $cartItems = null;

    public static function getCartContents()
    {
        if (self::$cartItems === null) {
            $query = Auth::check()
                ? Cart::where('user_id', auth()->id())
                : Cart::where('session_id', session()->getId());
            
            self::$cartItems = $query->get();
        }
        return self::$cartItems;
    }

    /**
     * Check if a simple product is already in the cart.
     */
    public static function isProductInCart(Product $product): bool
    {
        // We only care about simple products here (no variant_id)
        return self::getCartContents()
            ->where('product_id', $product->id)
            ->whereNull('variant_id')
            ->isNotEmpty();
    }
}

if (!function_exists('is_product_in_cart')) {
    function is_product_in_cart(\App\Models\Product $product): bool
    {
        return \App\Helpers\CartHelper::isProductInCart($product);
    }
}