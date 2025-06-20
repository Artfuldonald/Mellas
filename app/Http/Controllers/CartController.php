<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    public function index()
    {
        $cartItems = $this->getCartItems();
        $subtotal = $cartItems->sum('total');
        $tax = $subtotal * 0.08; // 8% tax
        $shipping = $subtotal > 50 ? 0 : 9.99;
        $total = $subtotal + $tax + $shipping;

        return view('cart.index', compact('cartItems', 'subtotal', 'tax', 'shipping', 'total'));
    }

    public function add(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variant_data' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);

        // Check if product is active
        if (!$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Product is not available'], 400);
        }

        // Handle variant products
        $variantId = null;
        $stockToCheck = $product->quantity;
        $priceToUse = $product->price;

        if ($request->variant_data && isset($request->variant_data['variant_id'])) {
            $variant = $product->variants()->where('id', $request->variant_data['variant_id'])->first();
            if ($variant && $variant->is_active) {
                $variantId = $variant->id;
                $stockToCheck = $variant->quantity;
                $priceToUse = $variant->price;
            } else {
                return response()->json(['success' => false, 'message' => 'Selected variant is not available'], 400);
            }
        }

        // Check stock
        if ($stockToCheck < $request->quantity) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock available'], 400);
        }

        $cartData = [
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'quantity' => $request->quantity,
            'variant_data' => $request->variant_data,
            'price_at_add' => $priceToUse,
        ];

        if (Auth::check()) {
            $cartData['user_id'] = Auth::id();
        } else {
            $cartData['session_id'] = session()->getId();
        }

        // Check if item already exists in cart
        $existingItemQuery = Cart::where('product_id', $product->id);
        
        if ($variantId) {
            $existingItemQuery->where('variant_id', $variantId);
        }
        
        if (Auth::check()) {
            $existingItemQuery->where('user_id', Auth::id());
        } else {
            $existingItemQuery->where('session_id', session()->getId());
        }

        $existingItem = $existingItemQuery->first();

        if ($existingItem) {
            // Check if adding quantity would exceed stock
            $newQuantity = $existingItem->quantity + $request->quantity;
            if ($newQuantity > $stockToCheck) {
                return response()->json(['success' => false, 'message' => 'Cannot add more items. Stock limit reached.'], 400);
            }
            $existingItem->update(['quantity' => $newQuantity]);
        } else {
            Cart::create($cartData);
        }

        return response()->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cart_count' => $this->getCartCount(),
        ]);
    }

    public function removeItem(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
        ]);

        $query = Cart::where('product_id', $request->product_id);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $cartItem = $query->first();

        if ($cartItem) {
            $cartItem->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product removed from cart',
                'cart_distinct_items_count' => $this->getCartCount(),
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in cart',
        ], 404);
    }

    public function update(Request $request, Cart $cartItem)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $this->authorizeCartItem($cartItem);

        // Check stock for the update
        $stockToCheck = $cartItem->variant_id 
            ? $cartItem->variant->quantity 
            : $cartItem->product->quantity;

        if ($request->quantity > $stockToCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock available'
            ], 400);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully',
            'total' => $cartItem->total,
        ]);
    }

    
    public function clear()
    {
        $query = Cart::query();

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $query->delete();

        return redirect()->route('cart.index')->with('success', 'Cart cleared.');
    }


    private function getCartItems()
    {
        $query = Cart::with(['product','product.images', 'variant']);

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        return $query->get();
    }

    private function getCartCount()
    {
        $query = Cart::query();

        if (Auth::check()) {
            $query->where('user_id', Auth::id());
        } else {
            $query->where('session_id', session()->getId());
        }

        return $query->count(); // Count distinct items, not total quantity
    }

    private function authorizeCartItem(Cart $cartItem)
    {
        if (Auth::check()) {
            if ($cartItem->user_id !== Auth::id()) {
                abort(403);
            }
        } else {
            if ($cartItem->session_id !== session()->getId()) {
                abort(403);
            }
        }
    }
}