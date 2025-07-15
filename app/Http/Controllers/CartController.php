<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Services\CartService;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    protected $cartService;

    // Inject the service via the constructor
    public function __construct(CartService $cartService)
    {
        $this->cartService = $cartService;
    }

    // This now just calls the service
    private function getCartState(): array
    {
        return $this->cartService->getCartState();
    }

    public function index()
    {
        $cartState = $this->getCartState();        
       
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
     * Add or set the quantity of a product/variant in the cart.
     * This single method can handle adding to cart and updating quantity via steppers.
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::with('variants')->findOrFail($validated['product_id']);
        $variant = null;
        $price = $product->price;
        $stock = $product->quantity;
        $variantDataForCart = null; 

        // If a variant is specified, use its details
        if ($request->filled('variant_id')) {

            $variant = $product->variants()->find($validated['variant_id']);
            
            if (!$variant) {
                return response()->json(['success' => false, 'message' => 'Variant not found.'], 404);
            }
            $price = $variant->price;
            $stock = $variant->quantity;
            $variantDataForCart = ['display_name' => $variant->name];
        }

        // Server-side stock check
        if ($validated['quantity'] > $stock) {
            return response()->json(['success' => false, 'message' => 'Not enough items in stock.'], 422);
        }
        
        // Define the unique properties for the cart item
        $itemIdentifiers = [
            'product_id' => $product->id, 
            'variant_id' => $variant->id ?? null
        ];
        
        // Get the correct key for a user or a guest
        $userOrSession = Auth::check() 
            ? ['user_id' => Auth::id()] 
            : ['session_id' => session()->getId()];

        // Find or create the cart item and SET its quantity
        Cart::updateOrCreate(
            array_merge($userOrSession, $itemIdentifiers),
            [
                'quantity' => $validated['quantity'],
                'price_at_add' => $price,
                'variant_data' => $variantDataForCart
            ]
        );

        return response()->json([
            'success'    => true,
            'message'    => 'Cart updated successfully.',
            'cart_count' => $this->getCartState()['item_count'],
        ]);
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
        
        return response()->json([
            'success'     => true,
            'message'     => 'Your cart has been cleared.',
            'cart_totals' => $this->getCartState()['totals'],
            'cart_count'  => 0,
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
}