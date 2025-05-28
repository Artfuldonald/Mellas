<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Js;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        $query = Product::where('is_active', true)
                        ->with([
                            'images' => fn($q) => $q->orderBy('position')->limit(1),
                            // No need to load 'reviews' collection here, just counts/avg
                        ])
                        ->withCount([
                            'variants',
                            'approvedReviews as reviews_count' // Alias to reviews_count for card
                        ])
                        ->withAvg('approvedReviews as reviews_avg_rating', 'rating'); // Alias to reviews_avg_rating

        $activeCategory = null;

        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $activeCategory = Category::where('slug', $categorySlug)->where('is_active', true)->firstOrFail();
            $query->whereHas('categories', function ($q) use ($activeCategory) {
                $q->where('categories.id', $activeCategory->id);
            });
        }

        $userWishlistProductIds = [];
        if (Auth::check()) {
            $userWishlistProductIds = Auth::user()->wishlistItems()->pluck('product_id')->toArray();
        }   

        $sortOrder = $request->input('sort', 'latest');
        switch ($sortOrder) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            // Add sorting by rating if desired
            case 'rating_desc':
                // This sorts by the average rating. Requires the withAvg above or a subquery.
                // If using withAvg, you can orderBy the alias.
                $query->orderBy('reviews_avg_rating', 'desc');
                break;
            case 'latest':
            default:
                $query->latest('created_at');
                break;
        }

        $productsPerPage = 12;
        $products = $query->paginate($productsPerPage)->withQueryString();

        $filterCategories = Category::where('is_active', true)->orderBy('name')->get(['name', 'slug']);

        return view('products.index', compact('products', 'filterCategories', 'activeCategory', 'sortOrder', 'userWishlistProductIds'));
    }

    /**
     * Display the specified product.
     *
     * @param  Product $product
     * @return \Illuminate\View\View
     */
    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        $product->load([
            'categories:id,name,slug',
            'images' => fn($q) => $q->orderBy('position'),
            'videos' => fn($q) => $q->orderBy('position'),
            'variants' => function ($query) {
                $query->with(['attributeValues' => function($q) {
                    $q->select('attribute_values.id', 'attribute_values.value', 'attribute_values.attribute_id')
                      ->with('attribute:id,name');
                }]);
            },
            'attributes' => fn($q) => $q->with('values:id,value,attribute_id')->orderBy('name'),
            'approvedReviews.user:id,name' // Load approved reviews and the user (name only) who wrote them
        ]);
        // The accessors 'average_rating' and 'approved_reviews_count' will be available on the $product model.

        $variantData = $product->variants->mapWithKeys(function ($variant) {
            $key = $variant->attributeValues->sortBy('id')->pluck('id')->join('-');
            return [$key => [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'quantity' => (int) $variant->quantity,
                'is_active' => (bool) $variant->is_active,
            ]];
        });

        $optionsData = $product->attributes->mapWithKeys(function ($attribute) {
            return [$attribute->id => [
                'name' => $attribute->name,
                'values' => $attribute->values->map(function($value) {
                    return ['id' => $value->id, 'name' => $value->value];
                })->toArray()
            ]];
        });
        
        $relatedProducts = collect(); // Initialize as an empty collection

        if ($product->categories->isNotEmpty()) {
            $firstCategoryId = $product->categories->first()->id; // Get ID of the first category

            $relatedProducts = Product::where('is_active', true)
                ->where('id', '!=', $product->id) // Exclude current product
                ->whereHas('categories', function ($q) use ($firstCategoryId) {
                    $q->where('categories.id', $firstCategoryId); // Match products in the same first category
                })
                ->with([ // Eager load necessary data for product cards
                    'images' => fn($q) => $q->orderBy('position')->limit(1),
                ])
                ->withCount('approvedReviews as reviews_count') 
                ->withAvg('approvedReviews as reviews_avg_rating', 'rating')   
                ->inRandomOrder() 
                ->take(4)         
                ->get();
        }

        // If still not enough related products, you could fall back to other logic
        // e.g., recently viewed, or just any other featured products
        if ($relatedProducts->count() < 4) {
            $additionalProductsNeeded = 4 - $relatedProducts->count();
            $existingIds = $relatedProducts->pluck('id')->push($product->id)->all(); 

            $fallbackProducts = Product::where('is_active', true)
                ->whereNotIn('id', $existingIds)
                ->with([
                    'images' => fn($q) => $q->orderBy('position')->limit(1),
                ])
                ->withCount('approvedReviews as reviews_count')
                ->withAvg('approvedReviews as reviews_avg_rating', 'rating')
                ->inRandomOrder() 
                ->take($additionalProductsNeeded)
                ->get();

            $relatedProducts = $relatedProducts->merge($fallbackProducts);
        }


        return view('products.show', [
            'product' => $product,
            'variantDataForJs' => Js::from($variantData),
            'optionsDataForJs' => Js::from($optionsData),
            'relatedProducts' => $relatedProducts, 
        ]);
    }
}