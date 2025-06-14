<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Str;

class CartController extends Controller
{
    /**
     * Get the cart from session or initialize it.
     */
    private function getCart(): array
    {
        return Session::get('cart', []);
    }

    /**
     * Save the cart to session.
     */
    private function saveCart(array $cart): void
    {
        Session::put('cart', $cart);
    }

    /**
     * Calculate total quantity of items in cart.
     */
    private function getCartTotalQuantity(): int
    {
        $cart = $this->getCart();
        $totalQuantity = 0;
        foreach ($cart as $item) {
            $totalQuantity += $item['quantity'] ?? 0;
        }
        return $totalQuantity;
    }

    private function getCartDistinctItemsCount(): int
    {
        return count($this->getCart());
    }

    /**
     * Display the cart page.
     */
    public function index()
    {
        $cartSessionItems = $this->getCart();
        $detailedCartItems = [];
        $subtotal = 0;

        $productIds = array_column($cartSessionItems, 'product_id');
        // Eager load products and their first images
        $products = Product::whereIn('id', $productIds)
                            ->with(['images' => fn($q) => $q->select(['id', 'product_id', 'path', 'alt'])->orderBy('position')->limit(1)])
                            ->select(['id', 'name', 'slug']) // Select only necessary product fields
                            ->get()
                            ->keyBy('id');

        // Eager load variants if any cart items have variant_id
        $variantIds = array_filter(array_column($cartSessionItems, 'variant_id'));
        $variants = [];
        if (!empty($variantIds)) {
            $variants = ProductVariant::whereIn('id', $variantIds)
                                      ->select(['id', 'product_id', 'name', 'price']) // Select necessary variant fields
                                      ->get()
                                      ->keyBy('id');
        }


        foreach ($cartSessionItems as $cartItemId => $item) {
            $product = $products->get($item['product_id']);

            if ($product) {
                $itemPrice = $item['price_at_add']; // Price at the time of adding
                $lineTotal = $itemPrice * $item['quantity'];
                $subtotal += $lineTotal;

                $variantNamePart = '';
                if (!empty($item['variant_id']) && $variants->has($item['variant_id'])) {
                    // If variant specific name is stored in cart item, use it, otherwise construct
                     $variantNamePart = $variants->get($item['variant_id'])->name ?: $item['variant_display_name_part'] ?? '';
                } elseif (!empty($item['variant_display_name_part'])) {
                    $variantNamePart = $item['variant_display_name_part'];
                }

                $detailedCartItems[$cartItemId] = [
                    'cart_item_id' => $cartItemId, // Pass this to the view for update/remove forms
                    'product_id' => $product->id,
                    'variant_id' => $item['variant_id'] ?? null,
                    'name' => $product->name,
                    'variant_display_name_part' => $variantNamePart,
                    'display_name' => $product->name . ($variantNamePart ? ' - ' . $variantNamePart : ''),
                    'price_at_add' => $itemPrice,
                    'current_price' => $item['variant_id'] ? ($variants->get($item['variant_id'])->price ?? $itemPrice) : ($product->price ?? $itemPrice), // For display if price changed
                    'quantity' => $item['quantity'],
                    'image_url' => $product->images->first()?->image_url ?? asset('images/placeholder.png'),
                    'slug' => $product->slug, // For linking back to product
                    'attributes_display' => $item['attributes_display'] ?? [],
                    'line_total' => $lineTotal,
                ];
            } else {
                // Product associated with cart item not found, remove it silently
                $this->removeFromCartSession($cartItemId);
                Log::warning("Product ID {$item['product_id']} not found for cart item {$cartItemId}. Item removed from cart.");
            }
        }
        // Re-save cart in case any items were removed
        $this->saveCart($cartSessionItems);


        return view('cart.index', [
            'cartItems' => $detailedCartItems,
            'subtotal' => $subtotal,
        ]);
    }

    /**
     * Add an item to the cart (AJAX).
     */
    public function add(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid input.', 'errors' => $validator->errors()], 422);
        }

        $productId = (int) $request->input('product_id');
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $quantity = (int) $request->input('quantity');

        $product = Product::find($productId);
        if (!$product || !$product->is_active) {
            return response()->json(['success' => false, 'message' => 'Product not available.'], 404);
        }

        $cartItemId = $productId . ($variantId ? '-' . $variantId : '');
        $priceAtAdd = $product->price;
        $stock = $product->quantity ?? 0;
        $displayName = $product->name;
        $variantDisplayNamePart = '';
        $attributesDisplay = []; // For storing human-readable attributes

        if ($variantId) {
            $variant = ProductVariant::with('attributeValues.attribute')->find($variantId);
            if (!$variant || $variant->product_id != $productId /* || !$variant->is_active */) {
                return response()->json(['success' => false, 'message' => 'Selected option not available.'], 404);
            }
            $priceAtAdd = $variant->price;
            $stock = $variant->quantity;

            $attrStrings = [];
            if ($variant->attributeValues->isNotEmpty()) {
                foreach ($variant->attributeValues as $value) {
                    $attrStrings[] = $value->value; // Just the value for simple display part
                    $attributesDisplay[] = ['name' => $value->attribute->name, 'value' => $value->value];
                }
                $variantDisplayNamePart = implode(' / ', $attrStrings);
            }
            $displayName = $product->name . ($variantDisplayNamePart ? ' - ' . $variantDisplayNamePart : '');
        }

        $cart = $this->getCart();
        $currentCartQuantity = $cart[$cartItemId]['quantity'] ?? 0;

        if (($currentCartQuantity + $quantity) > $stock) {
            return response()->json([
                'success' => false,
                'message' => 'Not enough stock. Only ' . $stock . ' ' . Str::plural('item', $stock) . ' available.' . ($currentCartQuantity > 0 ? ' You have ' . $currentCartQuantity . ' in cart.' : '')
            ], 422); // 422 Unprocessable Entity
        }

        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity'] += $quantity;
        } else {
            $cart[$cartItemId] = [
                'product_id' => $productId,
                'variant_id' => $variantId,
                'name_at_add' => $displayName, // Full display name at time of add
                'variant_display_name_part' => $variantDisplayNamePart, // Just "Red / Large"
                'price_at_add' => $priceAtAdd,
                'quantity' => $quantity,
                'attributes_display' => $attributesDisplay, // Store like [['name' => 'Color', 'value' => 'Red'], ...]
            ];
        }

        $this->saveCart($cart);

        return $this->getJsonResponse($displayName . ' added to cart!');
    }

    /**
     * Update item quantity in cart (from cart page form).
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|string',
            'quantity' => 'required|integer|min:0', // Allow 0 to remove
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput()->with('error_cart_item_id', $request->input('cart_item_id'));
        }

        $cartItemId = $request->input('cart_item_id');
        $quantity = (int) $request->input('quantity');
        $cart = $this->getCart();

        if (!isset($cart[$cartItemId])) {
            return back()->with('error', 'Item not found in cart.');
        }

        $item = $cart[$cartItemId];
        $stock = $item['variant_id']
            ? (ProductVariant::find($item['variant_id'])->quantity ?? 0)
            : (Product::find($item['product_id'])->quantity ?? 0);

        if ($quantity > $stock) {
             return back()->with('error', 'Not enough stock for ' . $item['name_at_add'] . '. Only ' . $stock . ' available.')->with('error_cart_item_id', $cartItemId);
        }

        $itemName = $item['name_at_add'];
        if ($quantity > 0) {
            $cart[$cartItemId]['quantity'] = $quantity;
            $message = $itemName . ' quantity updated.';
        } else {
            unset($cart[$cartItemId]);
            $message = $itemName . ' removed from cart.';
        }
        $this->saveCart($cart);

        return back()->with('success', $message);
    }

    /**
     * Remove an item from the cart (from cart page form).
     */
    public function remove(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cart_item_id' => 'required|string',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $cartItemId = $request->input('cart_item_id');
        $itemName = $this->removeFromCartSession($cartItemId);

        if ($itemName) {
            return back()->with('success', $itemName . ' removed from your cart.');
        }
        return back()->with('error', 'Item not found in cart or could not be removed.');
    }

    /**
     * Clear the entire cart.
     */
    public function clear()
    {
        Session::forget('cart');
        // Also update global cart count for header if not doing full page reload
        // This requires a bit more if using Alpine for header count directly,
        // or ensure pages reload to pick up new session state via View Composer.
        // For now, just rely on redirect.
        return redirect()->route('cart.index')->with('success', 'Cart cleared successfully.');
    }

    /**
     * Helper to remove item from session and return its name.
     */
    private function removeFromCartSession(string $cartItemIdToModify): ?string
    {
        $cart = $this->getCart(); // Get current cart
        $itemName = null;
        if (isset($cart[$cartItemIdToModify])) {
            $itemName = $cart[$cartItemIdToModify]['name_at_add']; // Get name before unsetting
            unset($cart[$cartItemIdToModify]);
            $this->saveCart($cart); // Save modified cart
        }
        return $itemName;
    } 

    /**
     * Set a specific quantity for an item, or remove it if quantity is 0.
     * This is for AJAX updates from the PDP modal.
     */
    public function updateItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer|exists:products,id',
            'variant_id' => 'nullable|integer|exists:product_variants,id',
            'quantity' => 'required|integer|min:0', // Allow 0 for removal
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => 'Invalid data.', 'errors' => $validator->errors()], 422);
        }

        $productId = (int) $request->input('product_id');
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $newQuantity = (int) $request->input('quantity');

        // Determine stock limit
        $stock = $variantId
            ? (ProductVariant::find($variantId)->quantity ?? 0)
            : (Product::find($productId)->quantity ?? 0);

        if ($newQuantity > $stock) {
            return response()->json(['success' => false, 'message' => 'Not enough stock available. Only ' . $stock . ' left.'], 422);
        }

        $cartItemId = $productId . ($variantId ? '-' . $variantId : '');
        $cart = $this->getCart();

        if ($newQuantity > 0) {
            // Add or update item in cart
            if (isset($cart[$cartItemId])) {
                $cart[$cartItemId]['quantity'] = $newQuantity;
            } else {
                // Item is not in cart, need to add it fully
                $product = Product::find($productId);
                $priceAtAdd = $product->price;
                $variantDisplayNamePart = '';
                
                if($variantId) {
                    $variant = ProductVariant::with('attributeValues.attribute')->find($variantId);
                    $priceAtAdd = $variant->price;
                    $variantDisplayNamePart = $variant->attributeValues->pluck('value')->join(' / ');
                }

                $cart[$cartItemId] = [
                    'product_id' => $productId,
                    'variant_id' => $variantId,
                    'name_at_add' => $product->name,
                    'variant_display_name_part' => $variantDisplayNamePart,
                    'price_at_add' => $priceAtAdd,
                    'quantity' => $newQuantity,
                    'attributes_display' => [], // Add this if needed
                ];
            }
        } else {
            // Remove item if quantity is 0
            unset($cart[$cartItemId]);
        }

        $this->saveCart($cart);
        
        // Return a full state update
        return response()->json([
            'success' => true,
            'message' => 'Cart updated.',
            'cart_items' => $this->getCart(), // Send back the entire cart session
            'cart_distinct_items_count' => $this->getCartDistinctItemsCount(),
            'cart_total_quantity' => $this->getCartTotalQuantity(),
        ]);
    }

    /**
     * Remove an item from the cart via AJAX.
     * This is different from the form-based remove method.
     */
    public function removeItem(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|integer',
            'variant_id' => 'nullable|integer',
        ]);
        if ($validator->fails()) { return response()->json(['success' => false, 'message' => 'Invalid request.'], 422); }
        
        $productId = (int) $request->input('product_id');
        $variantId = $request->input('variant_id') ? (int) $request->input('variant_id') : null;
        $cartItemId = $productId . ($variantId ? '-' . $variantId : '');
        $cart = $this->getCart();

        if (isset($cart[$cartItemId])) {
            $itemName = $cart[$cartItemId]['name_at_add'];
            unset($cart[$cartItemId]);
            $this->saveCart($cart);

            // Use the new helper to create the response
            return $this->getJsonResponse($itemName . ' removed from cart!');
        }

        return $this->getJsonResponse('Item not found in cart.', false, 404);
    }

    private function getJsonResponse(string $message, bool $success = true, int $statusCode = 200)
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'cart_distinct_items_count' => $this->getCartDistinctItemsCount(), // <-- This is what the header needs
        ], $statusCode);
    }
}