<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product; // To fetch products by brand
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // If you need wishlist status on brand product listings

class BrandController extends Controller
{
    /**
     * Display a listing of active brands.
     */
    public function index()
    {
        $brands = Brand::where('is_active', true)
                        ->orderBy('name')
                        ->withCount(['products' => fn($query) => $query->where('is_active', true)]) // Count active products
                        ->paginate(20); // Or ->get() if you don't need pagination

        return view('brands.index-public', compact('brands')); // Use a different view name
    }

    /**
     * Display products for a specific active brand.
     */
    public function show(Brand $brand) // Route model binding by slug (make sure Brand model has getRouteKeyName() if not using ID)
    {
        if (!$brand->is_active) {
            abort(404); // Don't show inactive brands publicly
        }

        // Columns needed for product cards on this brand page
        $productCardColumns = [
            'products.id', 'products.name', 'products.slug', 'products.price', 'products.compare_at_price',
            'products.quantity' // For simple product stock check by card
            // Add other fields directly used by x-product-card from the Product model itself
        ];

        $products = $brand->products() // Use the relationship
                         ->where('products.is_active', true)
                         ->select($productCardColumns) // Select specific columns
                         ->with([
                             'images' => fn($q) => $q->select(['id', 'product_id', 'path', 'alt'])->orderBy('position')->limit(1),
                         ])
                         ->withCount('variants') // For the x-product-card logic
                         ->withCount('approvedReviews as reviews_count')
                         ->withAvg('approvedReviews as reviews_avg_rating', 'rating')
                         ->latest('products.created_at')
                         ->paginate(12); // Paginate products

        // For dynamic wishlist icons on product cards
        $userWishlistProductIds = [];
        if (Auth::check()) {
            $userWishlistProductIds = Auth::user()->wishlistItems()->pluck('product_id')->toArray();
        }

        return view('brands.show-public', compact('brand', 'products', 'userWishlistProductIds')); // Use a different view name
    }
}