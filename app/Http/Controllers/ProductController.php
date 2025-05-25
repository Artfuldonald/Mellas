<?php

namespace App\Http\Controllers;

use App\Models\Product; 
use App\Models\Category; 
use Illuminate\Support\Js;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the active products.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Start query for active products
        $query = Product::where('is_active', true);

        // --- Eager Loading ---
        // Load the first image for each product for the listing view
        // Also load variant count to potentially adjust display on product card
        $query->with(['images' => fn($q) => $q->orderBy('position')->limit(1)])
              ->withCount('variants');

        // --- Filtering (Example: By Category) ---
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug); // Filter by category slug
            });
        }

        // --- Sorting (Example: Add more options later) ---
        // Default to newest first, maybe allow sorting by price, name etc.
        $query->latest(); // Or orderBy('name', 'asc'), orderBy('price', 'asc/desc')

        // --- Pagination ---
        $productsPerPage = 12; // Number of products per page for the grid
        $products = $query->paginate($productsPerPage)->withQueryString(); // Keep filters in pagination links

        // --- Fetch Categories for Filtering UI ---
        // Fetch all for now, assuming not too many. Could filter by active later if needed.
        $categories = Category::orderBy('name')->get(['name', 'slug']); // Get only needed columns

        // Return the view, passing the products and categories
        return view('products.index', compact('products', 'categories'));
    }

    /**
     * Display the specified product.
     * (We'll implement this later for the product detail page)
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        // 1. Check if the product is active
        if (!$product->is_active) {
            // Optional: Redirect to product listing or show 404 if inactive products shouldn't be accessed directly
             abort(404);
            // return redirect()->route('products.index')->with('error', 'Product not available.');
        }

        // 2. Eager load all necessary relationships for the detail page
        $product->load([
            'categories:id,name,slug', // Load categories (select specific columns)
            'images' => fn($q) => $q->orderBy('position'), // Load all images, ordered
            'videos' => fn($q) => $q->orderBy('position'), // Load all videos, ordered (if you display them)
            // Crucial for variant selection:
            'variants' => function ($query) {
                // Load variants that are active (optional, if you have is_active on variants)
                // $query->where('is_active', true);
                // Load the specific attribute values associated *with each variant*
                $query->with(['attributeValues' => function($q) {
                    // For each attribute value, also load its parent attribute name/id
                    $q->select('attribute_values.id', 'attribute_values.value', 'attribute_values.attribute_id') // Select only needed value columns
                      ->with('attribute:id,name'); // Eager load the parent Attribute model (select needed columns)
                }]);
            },
            // Load the main attributes *assigned to the product* (e.g., "Color", "Size")
            // This helps build the selection UI even before variants are loaded by JS
            'attributes' => fn($q) => $q->with('values:id,value,attribute_id')->orderBy('name') // Load attributes with their possible values
        ]);

        // 3. Prepare data for potential Alpine.js variant selection component
        // Group variants by their attribute value combinations for easy lookup in JS
        $variantData = $product->variants->mapWithKeys(function ($variant) {
            // Create a unique key based on sorted attribute value IDs (e.g., "10-25")
            $key = $variant->attributeValues->sortBy('id')->pluck('id')->join('-');
            return [$key => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price, // Ensure float
                'quantity' => (int) $variant->quantity,
                'is_active' => (bool) $variant->is_active,
                // Add image ID if variants can have unique images later
                // 'image_id' => $variant->image_id,
            ]];
        });

        // Group available attribute values by the attribute ID
        $optionsData = $product->attributes->mapWithKeys(function ($attribute) {
            return [$attribute->id => [
                'name' => $attribute->name,
                'values' => $attribute->values->map(function($value) {
                    return ['id' => $value->id, 'name' => $value->value];
                })->toArray() // Convert collection to array
            ]];
        });


        // 4. Pass data to the view
        return view('products.show', [
            'product' => $product,
            'variantDataForJs' => Js::from($variantData), // Pass variant lookup data as JSON
            'optionsDataForJs' => Js::from($optionsData), // Pass attribute options data as JSON
        ]);
    }
}