<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session; 
use Illuminate\Support\Facades\Validator;

class CartController extends Controller 
{
    /**
     * Display the cart page.
     */
    public function index()
    {
        $cartItems = Session::get('cart', []);
        $subtotal = 0;
        $detailedCartItems = [];

        foreach ($cartItems as $cartItemId => $item) {
            // The cartItemId could be 'productID' or 'productID-variantID'
            // For now, let's assume it's just product ID for simplicity,
            // and variant details are stored within the item.
            // You'll need to fetch product details to display them properly.

            $product = Product::with(['images' => fn($q) => $q->orderBy('position')->limit(1)])
                              ->find($item['product_id']);

            if ($product) {
                $itemPrice = $item['price']; // Price at the time of adding to cart
                $lineTotal = $itemPrice * $item['quantity'];
                $subtotal += $lineTotal;

                $detailedCartItems[$cartItemId] = [
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'name' => $product->name,
                    'display_name' => $item['name'], // Name including variant info
                    'price' => $itemPrice,
                    'quantity' => $item['quantity'],
                    'image_url' => $product->images->first()?->image_url ?? asset('images/placeholder.png'),
                    'slug' => $product->slug,
                    'attributes' => $item['attributes'] ?? [], // e.g., ['Color' => 'Red', 'Size' => 'M']
                    'line_total' => $lineTotal,
                ];
            } else {
                // Product not found, remove from cart (or handle error)
                $this->removeFromCartSession($cartItemId);
            }
        }

        return view('cart.index', [
            'cartItems' => $detailedCartItems,
            'subtotal' => $subtotal,
            // Add other totals like tax, shipping, grand_total later
        ]);
    }

    /**
     * Add an item to the cart.
     * This will be called from the PDP Alpine component.
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'variant_id' => 'nullable|exists:product_variants,id', // If you have variants
            'quantity' => 'required|integer|min:1',
            // 'price' => 'required|numeric|min:0', // Price might be fetched server-side for security
            // 'name' => 'required|string', // Name with variant info
            // 'attributes' => 'nullable|array' // e.g. ['Color' => 'Red']
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $productId = $request->input('product_id');
        $variantId = $request->input('variant_id');
        $quantity = (int) $request->input('quantity');

        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Product not available.'], 404);
        }

        $cartItemId = $productId; // Base cart item ID
        $price = $product->price;
        $stock = $product->quantity ?? 0;
        $displayName = $product->name;
        $itemAttributes = [];


        if ($variantId) {
            $variant = ProductVariant::with('attributeValues.attribute')->find($variantId);
            if (!$variant || $variant->product_id != $productId /* || !$variant->is_active */) {
                return response()->json(['success' => false, 'message' => 'Selected option not available.'], 404);
            }
            $cartItemId .= '-' . $variantId; // Unique ID for product-variant combination
            $price = $variant->price;
            $stock = $variant->quantity;
            // Construct display name with attributes
            $displayName .= ' (';
            $attrStrings = [];
            foreach ($variant->attributeValues as $value) {
                $attrStrings[] = $value->attribute->name . ': ' . $value->value;
                $itemAttributes[$value->attribute->name] = $value->value;
            }
            $displayName .= implode(', ', $attrStrings) . ')';

        }

        // Stock Check
        $cart = Session::get('cart', []);
        $currentCartQuantity = $cart[$cartItemId]['quantity'] ?? 0;
        if (($currentCartQuantity + $quantity) > $stock) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock available. Only ' . $stock . ' left.' . ($currentCartQuantity > 0 ? ' You have ' . $currentCartQuantity . ' in cart.' : '')
            ], 422);
        }


        if (isset($cart[$cartItemId])) {
            // Item already in cart, update quantity
            $cart[$cartItemId]['quantity'] += $quantity;
        } else {
            // Add new item
            $cart[$cartItemId] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name' => $displayName, // Product name with variant info
                'price' => $price,       // Price of this specific item/variant
                'quantity' => $quantity,
                'attributes' => $itemAttributes, // Store selected attributes
            ];
        }

        Session::put('cart', $cart);
        $cartCount = $this->getCartCount(); // Helper to get total items in cart

        return response()->json([
            'success' => true,
            'message' => $displayName . ' added to cart!',
            'cart_count' => $cartCount,
            // You can also return the updated cart HTML for a mini-cart if needed
        ]);
    }

    /**
     * Update item quantity in cart.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|string', // e.g., 'productID' or 'productID-variantID'
            'quantity' => 'required|integer|min:0', // min:0 to allow removal by setting quantity to 0
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput(); // Or JSON response if AJAX
        }

        $cartItemId = $request->input('cart_item_id');
        $quantity = (int) $request->input('quantity');
        $cart = Session::get('cart', []);

        if (!isset($cart[$cartItemId])) {
            return back()->with('error', 'Item not found in cart.'); // Or JSON
        }

        // Stock Check before update
        $item = $cart[$cartItemId];
        $stock = $item['variant_id'] ? ProductVariant::find($item['variant_id'])->quantity : Product::find($item['product_id'])->quantity;

        if ($quantity > $stock) {
             return back()->with('error', 'Not enough stock. Only ' . $stock . ' available.'); // Or JSON
        }


        if ($quantity > 0) {
            $cart[$cartItemId]['quantity'] = $quantity;
        } else {
            unset($cart[$cartItemId]); // Remove item if quantity is 0
        }

        Session::put('cart', $cart);
        $message = $quantity > 0 ? 'Cart updated successfully.' : $cart[$cartItemId]['name'] . ' removed from cart.'; // Adjust message if item was removed

        return back()->with('success', $message); // Or JSON
    }

    /**
     * Remove an item from the cart.
     */
    public function remove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator); // Or JSON
        }

        $cartItemId = $request->input('cart_item_id');
        $itemName = $this->removeFromCartSession($cartItemId);


        if ($itemName) {
            return back()->with('success', $itemName . ' removed from your cart.'); // Or JSON
        }
        return back()->with('error', 'Item not found in cart.'); // Or JSON
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        Session::forget('cart');
        return back()->with('success', 'Cart cleared successfully.'); // Or JSON
    }


    /**
     * Helper to remove item and return its name.
     */
    private function removeFromCartSession(string $cartItemId): ?string
    {
        $cart = Session::get('cart', []);
        if (isset($cart[$cartItemId])) {
            $itemName = $cart[$cartItemId]['name'];
            unset($cart[$cartItemId]);
            Session::put('cart', $cart);
            return $itemName;
        }
        return null;
    }


    /**
     * Helper to get total number of unique items or total quantity in cart.
     */
    private function getCartCount(): int
    {
        $cart = Session::get('cart', []);
        // return count($cart); // Number of unique line items
        $totalQuantity = 0;
        foreach ($cart as $item) {
            $totalQuantity += $item['quantity'];
        }
        return $totalQuantity; // Total quantity of all items
    }
}