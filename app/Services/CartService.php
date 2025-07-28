<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\TaxRate;
use App\Models\Discount;
use App\Models\ShippingRate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class CartService
{
    /**
     * Get the full state of the cart, including items and all dynamic totals.
     */
    public function getCartState(): array
    {
        $cartItems = $this->getCartItems();
        $totals = $this->calculateTotals($cartItems);
        
        return [
            'items' => $cartItems,
            'totals' => $totals,
            'item_count' => $cartItems->count(), 
            'total_quantity' => $cartItems->sum('quantity'), 
        ];
    }

     /**
     * Get just the count of distinct items in the cart, very efficiently.
     * 
     */
    public function getCartCount(): int
    {
        $query = Auth::check()
            ? Cart::where('user_id', auth()->id())
            : Cart::where('session_id', session()->getId());

        // We use sum('quantity') to get the total number of all items.
        // If you want the number of *distinct lines* (e.g., 2 shirts, 1 pair of pants = 2), use  instead.
        return (int) $query->count(); 
    }
        
    public function getCartItems(): Collection
    {
         $query = Cart::with([
            'product:id,name,slug,quantity,compare_at_price',
            'product.media',
            'variant:id,product_id,name,price,quantity,compare_at_price',
            'variant.attributeValues.attribute:id,name' // Be specific about columns
        ]);

        $query->where(Auth::check() ? 'user_id' : 'session_id', Auth::check() ? auth()->id() : session()->getId());

        $cartItems = $query->latest('id')->get();

        $cartItems->each(function ($item) {
            $stock = 0;
            if ($item->variant) {
                $variantAttributes = $item->variant->attributeValues->map(fn($av) => $av->attribute->name . ': ' . $av->value)->implode(', ');
                $item->display_name = $item->product->name . ' (' . $variantAttributes . ')';
                $stock = $item->variant->quantity;
            } elseif ($item->product) {
                $item->display_name = $item->product->name;
                $stock = $item->product->quantity;
            }
                        
            if ($item->product) {
                $item->image_url = $item->product->getFirstMediaUrl('default', 'cart_thumbnail') ?? asset('images/placeholder.png');
            } else {
                $item->image_url = asset('images/placeholder.png');
            }

            // Assign other necessary properties
            $item->line_total = $item->price_at_add * $item->quantity;
            $item->is_in_stock = $stock > 0;
            $item->max_stock = $stock;
        });

        return $cartItems;
    }

    /**
     * Calculates all financial totals for the cart, including discounts, tax, and shipping.
     */
    public function calculateTotals(Collection $cartItems): array
    {
        // 1. Calculate Subtotal
        $subtotal = $cartItems->sum('line_total');

        // 2. Apply Discount (if one is in the session)
        $discountAmount = 0;
        $discountCode = session('cart.discount_code');
        $appliedDiscount = null;

        if ($discountCode) {
            $discount = Discount::where('code', $discountCode)->valid()->first();
            if ($discount && $subtotal >= $discount->min_spend) {
                $appliedDiscount = $discount;
                if ($discount->type === Discount::TYPE_PERCENTAGE) {
                    $discountAmount = ($subtotal * $discount->value) / 100;
                } else { // Fixed amount
                    $discountAmount = $discount->value;
                }
                $discountAmount = min($subtotal, $discountAmount); // Ensure discount doesn't exceed subtotal
            } else {
                session()->forget('cart.discount_code'); // Invalid discount, so remove it
            }
        }
                
        $subtotalAfterDiscount = $subtotal - $discountAmount;

        // 3. Calculate Tax on the discounted subtotal
        $activeTaxRatePercentage = TaxRate::where('is_active', true)->sum('rate');
        $taxAmount = $subtotalAfterDiscount * ($activeTaxRatePercentage / 100);

        // 4. Calculate Shipping
        $shippingRate = ShippingRate::where('is_active', true)->orderBy('cost', 'asc')->first();
        $shippingCost = $shippingRate ? $shippingRate->cost : 0.00;

        // 5. Calculate Grand Total
        $grandTotal = $subtotalAfterDiscount + $taxAmount + $shippingCost;

        return [
            'subtotal'   => (float) $subtotal,
            'discount'   => (float) $discountAmount,
            'subtotal_after_discount' => (float) $subtotalAfterDiscount,
            'tax'        => (float) $taxAmount,
            'shipping'   => (float) $shippingCost,
            'grandTotal' => (float) $grandTotal,
            'applied_discount' => $appliedDiscount // Pass the full discount object or null
        ];
    }
}