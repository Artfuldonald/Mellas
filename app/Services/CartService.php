<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartService
{
    public function getCartItems()
    {
        if (Auth::check()) {
            return Cart::with(['product.images', 'variant'])
                      ->where('user_id', Auth::id())
                      ->get();
        } else {
            return Cart::with(['product.images', 'variant'])
                      ->where('session_id', Session::getId())
                      ->get();
        }
    }

    public function addToCart($productId, $quantity = 1, $variantId = null)
    {
        $product = Product::findOrFail($productId);
        
        // Determine price and stock
        $price = $product->price;
        $stock = $product->quantity;
        
        if ($variantId) {
            $variant = ProductVariant::findOrFail($variantId);
            $price = $variant->price;
            $stock = $variant->quantity;
        }

        // Check stock
        if ($quantity > $stock) {
            throw new \Exception('Insufficient stock available');
        }

        // Find existing cart item
        $cartQuery = Cart::where('product_id', $productId)
                        ->where('variant_id', $variantId);
                        
        if (Auth::check()) {
            $cartQuery->where('user_id', Auth::id());
        } else {
            $cartQuery->where('session_id', Session::getId());
        }

        $existingItem = $cartQuery->first();

        if ($existingItem) {
            $newQuantity = $existingItem->quantity + $quantity;
            if ($newQuantity > $stock) {
                throw new \Exception('Cannot add more items. Stock limit reached.');
            }
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            Cart::create([
                'user_id' => Auth::id(),
                'session_id' => Auth::check() ? null : Session::getId(),
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity,
                'price_at_add' => $price,
            ]);
        }
    }

    public function migrateSessionCartToUser($userId)
    {
        $sessionId = Session::getId();
        
        // Update session cart items to user
        Cart::where('session_id', $sessionId)
            ->whereNull('user_id')
            ->update([
                'user_id' => $userId,
                'session_id' => null
            ]);
    }

    public function removeFromCart($productId, $variantId = null)
    {
        $cartQuery = Cart::where('product_id', $productId)
                        ->where('variant_id', $variantId);
                        
        if (Auth::check()) {
            $cartQuery->where('user_id', Auth::id());
        } else {
            $cartQuery->where('session_id', Session::getId());
        }

        return $cartQuery->delete();
    }

    public function getCartCount()
    {
        if (Auth::check()) {
            return Cart::where('user_id', Auth::id())->count();
        } else {
            return Cart::where('session_id', Session::getId())->count();
        }
    }

    public function clearCart()
    {
        if (Auth::check()) {
            Cart::where('user_id', Auth::id())->delete();
        } else {
            Cart::where('session_id', Session::getId())->delete();
        }
    }
}
