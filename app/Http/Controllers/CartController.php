<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    /**
     * A central helper to get the current cart state.
     * This avoids repeating code and ensures consistency.
     */
    private function getCartState(): array
    {
        // Use your original, working eager loading logic
        $query = Cart::with(['product', 'product.images', 'variant']);

        if (Auth::check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->where('session_id', session()->getId());
        }

        $cartItems = $query->latest('id')->get();

        // Perform calculations based on your business logic
        $subtotal = $cartItems->sum('total');
        $tax = $subtotal * 0.08; // 8% tax rule
        $shipping = ($subtotal > 50 || $subtotal == 0) ? 0 : 9.99; // Free shipping over $50 rule
        $total = $subtotal + $tax + $shipping;

        return [
            'items' => $cartItems,
            'totals' => [
                'subtotal'   => (float) $subtotal,
                'tax'        => (float) $tax,
                'shipping'   => (float) $shipping,
                'grandTotal' => (float) $total,
            ],
            'item_count' => $cartItems->count(),
        ];
    }

    /**
     * Display the cart page using your original variable structure.
     */
    public function index()
    {
        $cartState = $this->getCartState();
        
        $cartItems = $cartState['items'];
        $subtotal = $cartState['totals']['subtotal'];
        $tax = $cartState['totals']['tax'];
        $shipping = $cartState['totals']['shipping'];
        $total = $cartState['totals']['grandTotal'];  
       
        // Send flat variables to the view
        return view('cart.index', compact('cartItems', 'subtotal', 'tax', 'shipping', 'total'));
    }
    
    /**
     * Update the quantity of a specific item in the cart via AJAX.
     * This method is for the routes used by the new cart page.
     */
    public function updateItem(Request $request)
    {
        $validated = $request->validate([
            'cart_id'  => 'required|integer|exists:carts,id',
            'quantity' => 'required|integer|min:1|max:10',
        ]);

        $cartItem = Cart::findOrFail($validated['cart_id']);
        $this->authorize('update', $cartItem); // Using Laravel Policies is recommended

        $stockToCheck = $cartItem->variant ? $cartItem->variant->quantity : $cartItem->product->quantity;
        if ($validated['quantity'] > $stockToCheck) {
            return response()->json(['success' => false, 'message' => 'Insufficient stock available.'], 422);
        }

        $cartItem->update(['quantity' => $validated['quantity']]);

        // Return the fresh, fully recalculated cart state for the AJAX update
        return response()->json([
            'success'     => true,
            'message'     => 'Cart updated!',
            'cart_totals' => $this->getCartState()['totals'],
            'cart_count'  => $this->getCartState()['item_count'],
        ]);
    }

    /**
     * Remove a single item from the cart via AJAX.
     */
    public function removeItem(Request $request)
    {
        $validated = $request->validate([
            'cart_id' => 'required|integer|exists:carts,id',
        ]);

        $cartItem = Cart::findOrFail($validated['cart_id']);
        $this->authorize('delete', $cartItem);
        
        $cartItem->delete();

        return response()->json([
            'success'     => true,
            'message'     => 'Item removed from cart.',
            'cart_totals' => $this->getCartState()['totals'],
            'cart_count'  => $this->getCartState()['item_count'],
        ]);
    }
    
    /**
     * Clear all items from the cart via AJAX.
     */
    public function clear()
    {
        $query = Auth::check()
            ? Cart::where('user_id', auth()->id())
            : Cart::where('session_id', session()->getId());
        
        $query->delete();
        
        // This MUST return JSON for the AJAX call to work.
        return response()->json([
            'success'     => true,
            'message'     => 'Your cart has been cleared.',
            'cart_totals' => $this->getCartState()['totals'], // Will be all zeros
            'cart_count'  => 0,
        ]);
    }

    /**
     * Add a product to the cart (your original logic).
     */
    public function add(Request $request)
    {
        // This is your original 'add' method, it remains unchanged as it works for you.
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'variant_data' => 'nullable|array',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Product is not available'], 400);
        }

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

        $existingItemQuery = Cart::where('product_id', $product->id)
            ->when($variantId, fn($query) => $query->where('variant_id', $variantId))
            ->when(Auth::check(), fn($query) => $query->where('user_id', Auth::id()), fn($query) => $query->where('session_id', session()->getId()));

        $existingItem = $existingItemQuery->first();

        if ($existingItem) {
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
            'cart_count' => $this->getCartState()['item_count'],
        ]);
    }

    public function removeSimpleProduct(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $query = Auth::check()
            ? Cart::where('user_id', auth()->id())
            : Cart::where('session_id', session()->getId());

        $cartItem = $query->where('product_id', $validated['product_id'])
                        ->whereNull('variant_id')
                        ->first();

        if ($cartItem) {
            $cartItem->delete();
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Item removed from cart.',
            'cart_count'  => $this->getCartState()['item_count'],
        ]);
    }

        /**
     * Sets the quantity for a simple product in the cart.
     * This will be used by the product card and cart page steppers.
     */
    public function setQuantity(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1', // Stepper ensures it's at least 1
        ]);

        $product = Product::findOrFail($validated['product_id']);

        // Server-side stock check
        if ($validated['quantity'] > $product->quantity) {
            return response()->json(['success' => false, 'message' => 'Not enough items in stock.'], 422);
        }
        
        $userOrSession = Auth::check() 
            ? ['user_id' => Auth::id()] 
            : ['session_id' => session()->getId()];

        // This is the key: updateOrCreate will find the item and SET the quantity.
        // It does not add to the existing quantity.
        Cart::updateOrCreate(
            array_merge($userOrSession, [
                'product_id' => $product->id, 
                'variant_id' => null
            ]),
            [
                'quantity' => $validated['quantity'],
                'price_at_add' => $product->price // Good practice to update price in case it changed
            ]
        );

        return response()->json([
            'success'    => true,
            'message'    => 'Cart updated.',
            'cart_count' => $this->getCartState()['item_count'],
        ]);
    }
}