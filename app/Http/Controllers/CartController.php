<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CartService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // This method is for your API route and is correct
    public function getCount()
    {
        return response()->json(['count' => $this->cartService->getCartCount()]);
    }

    // This method is for the full cart page and is correct
    public function index()
    {
        $cartState = $this->cartService->getCartState();
        if (request()->wantsJson()) {
            return response()->json($cartState);
        }
        return view('cart.index', [
            'cartItems' => $cartState['items'],
            'subtotal'  => $cartState['totals']['subtotal'],
            'tax'       => $cartState['totals']['tax'],
            'shipping'  => $cartState['totals']['shipping'],
            'total'     => $cartState['totals']['grandTotal'],
        ]);
    }

    /**
     * Add or update an item in the cart.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        try {
            $product = Product::select(['id', 'name', 'price', 'quantity'])
                ->with(['variants:id,product_id,name,price,quantity'])
                ->findOrFail($validated['product_id']);
                
            $variant = null;
            $price = $product->price;
            $stock = $product->quantity;
            $variantDataForCart = null; 

            if ($request->filled('variant_id')) {
                $variant = $product->variants->find($validated['variant_id']);
                if (!$variant) { return response()->json(['success' => false, 'message' => 'Variant not found.'], 404); }
                $price = $variant->price;
                $stock = $variant->quantity;
                $variantDataForCart = ['display_name' => $variant->name];
            }

            if ($validated['quantity'] > $stock) {
                return response()->json(['success' => false, 'message' => 'Not enough items in stock.'], 422);
            }
                    
            $itemIdentifiers = ['product_id' => $product->id, 'variant_id' => $variant->id ?? null];
            $userOrSession = Auth::check() ? ['user_id' => Auth::id()] : ['session_id' => session()->getId()];

            Cart::updateOrCreate(
                array_merge($userOrSession, $itemIdentifiers),
                ['quantity' => $validated['quantity'], 'price_at_add' => $price, 'variant_data' => $variantDataForCart]
            );

            return response()->json([
                'success'    => true,
                'message'    => $product->name . ' added to cart!',
                'cart_count' => $this->cartService->getCartCount(),
            ]);

        } catch (\Exception $e) {
            Log::error("Error adding product to cart: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not add item to cart.'], 500);
        }
    }
        
    /**
     * Update an item's quantity in the cart.
     */
    public function updateItem(Request $request)
    {
        $validated = $request->validate([
            'cart_id'  => 'required|integer|exists:carts,id',
            'quantity' => 'required|integer|min:1|max:99', // Set a reasonable max
        ]);

        try {
            $cartItem = Cart::with(['product:id,quantity', 'variant:id,quantity'])->findOrFail($validated['cart_id']);
            $this->authorize('update', $cartItem);

            $stockToCheck = $cartItem->variant ? $cartItem->variant->quantity : $cartItem->product->quantity;
            if ($validated['quantity'] > $stockToCheck) {
                return response()->json(['success' => false, 'message' => 'Insufficient stock available.'], 422);
            }

            $cartItem->update(['quantity' => $validated['quantity']]);
            
            // Return a simple success response with the new count
            return response()->json([
                'success'     => true,
                'message'     => 'Cart quantity updated!',
                'cart_count'  => $this->cartService->getCartCount(),
                // Also return fresh totals for the cart page's Alpine component
                'cart_totals' => $this->cartService->getCartState()['totals'],
            ]);

        } catch (\Exception $e) {
            Log::error("Error updating cart item {$validated['cart_id']}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not update cart item.'], 500);
        }
    }

    /**
     * Remove an item from the cart.
     */
    public function removeItem(Request $request)
    {
        $validated = $request->validate(['cart_id' => 'required|integer|exists:carts,id']);

        try {
            $cartItem = Cart::findOrFail($validated['cart_id']);
            $this->authorize('delete', $cartItem);
            $cartItem->delete();

            return response()->json([
                'success'     => true,
                'message'     => 'Item removed from cart.',
                'cart_count'  => $this->cartService->getCartCount(),
                'cart_totals' => $this->cartService->getCartState()['totals'],
            ]);

        } catch (\Exception $e) {
            Log::error("Error removing cart item {$validated['cart_id']}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not remove item from cart.'], 500);
        }
    }

    /**
     * Remove a simple product (one without variants) from the cart.
     * This is typically used by the product card component.
     */
    public function removeSimpleProduct(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        try {
            // Build the base query to find the item for the current user/session
            $query = Auth::check()
                ? Cart::where('user_id', auth()->id())
                : Cart::where('session_id', session()->getId());

            // Find the specific cart item for this product, ensuring it's not a variant
            $cartItem = $query->where('product_id', $validated['product_id'])
                            ->whereNull('variant_id') // Crucial: only target simple products
                            ->first();

            // If an item was found, delete it
            if ($cartItem) {
                // We can use the existing authorization from the CartPolicy
                $this->authorize('delete', $cartItem);
                $cartItem->delete();
            }

            // Return a success response, even if the item wasn't found (it's a harmless request)
            return response()->json([
                'success'     => true,
                'message'     => 'Item removed from cart.',
                'cart_count'  => $this->cartService->getCartCount(),
                'cart_totals' => $this->cartService->getCartState()['totals'],
            ]);

        } catch (\Exception $e) {
            Log::error("Error removing simple product from cart: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not remove item from cart.'], 500);
        }
    }
        
    /**
     * Clear all items from the user's cart.
     */
    public function clear()
    {
        try {
            $query = Auth::check()
                ? Cart::where('user_id', auth()->id())
                : Cart::where('session_id', session()->getId());
            $query->delete();
            
            return response()->json([
                'success'     => true,
                'message'     => 'Your cart has been cleared.',
                'cart_count'  => 0,
                'cart_totals' => $this->cartService->getCartState()['totals'], // Will be all zeros
            ]);
        } catch (\Exception $e) {
            Log::error("Error clearing cart: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Could not clear the cart.'], 500);
        }
    }
}