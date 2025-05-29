<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Js;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
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
                        ])
                        ->withCount([
                            'variants',
                            'approvedReviews as reviews_count'
                        ])
                        ->withAvg('approvedReviews as reviews_avg_rating', 'rating');

        $activeCategory = null;

        // --- Filtering by Category ---
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $activeCategory = Category::where('slug', $categorySlug)->where('is_active', true)->firstOrFail();
            $query->whereHas('categories', function ($q) use ($activeCategory) {
                $q->where('categories.id', $activeCategory->id);
            });
        }

        // --- Filtering by Brand ---
        if ($request->filled('brands') && is_array($request->input('brands'))) {
            $brandSlugs = $request->input('brands');
            // Assuming Brand model has a 'slug' column
            $query->whereHas('brand', function ($q) use ($brandSlugs) { // Assumes 'brand' relationship on Product model
                $q->whereIn('slug', $brandSlugs);
            });
        }

        // --- Filtering by Price Range ---
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float)$request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float)$request->input('price_max'));
        }

        // --- Filtering by Discount Percentage ---
        if ($request->filled('discount_min')) {
            $minDiscount = (int)$request->input('discount_min');
            if ($minDiscount > 0) {
                // Calculate discount: ( (compare_at_price - price) / compare_at_price ) * 100 >= minDiscount
                // This requires compare_at_price to be set and greater than price.
                $query->whereNotNull('compare_at_price')
                      ->where('compare_at_price', '>', DB::raw('price')) // Ensure compare_at_price > price
                      ->whereRaw('((compare_at_price - price) / compare_at_price) * 100 >= ?', [$minDiscount]);
            }
        }

        // --- Other Filters (Example: Shipped From) ---
        // if ($request->filled('shipped_from')) {
        //     $query->where('shipping_origin', $request->input('shipped_from')); // Example field
        // }


        // --- Sorting ---
        $sortOrder = $request->input('sort', 'latest');
        switch ($sortOrder) {
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'name_asc': // Keep if you want it as an option
                $query->orderBy('name', 'asc');
                break;
            case 'rating_desc':
                $query->orderByDesc(DB::raw('(SELECT AVG(rating) FROM reviews WHERE reviews.product_id = products.id AND reviews.is_approved = 1)'));
                // Or if using the withAvg alias, ensure it's available for orderBy:
                // $query->orderBy('reviews_avg_rating', 'desc'); // Might need to adjust if alias isn't directly sortable this way without subquery
                break;
            case 'latest':
            default:
                $query->latest('created_at');
                break;
        }

        $productsPerPage = $request->input('per_page', 15); // Jumia often shows 10-20, make it configurable
        $products = $query->paginate($productsPerPage)->withQueryString();

        // Data for filter sidebars
        $filterCategories = Category::where('is_active', true)->orderBy('name')->get(['name', 'slug']);
        $brands = Brand::where('is_active', true)->whereHas('products')->withCount('products')->orderBy('name')->get(['id','name', 'slug']); // Fetch brands that have products

        $userWishlistProductIds = [];
        if (Auth::check()) {
            $userWishlistProductIds = Auth::user()->wishlistItems()->pluck('product_id')->toArray();
        }

        // If the request is an AJAX request (we'll use this later)
        if ($request->ajax()) {
            return response()->json([
                'products_html' => view('products.partials._product_grid', compact('products', 'userWishlistProductIds'))->render(),
                'pagination_html' => $products->links()->toHtml(),
                'result_count_html' => view('products.partials._result_count', compact('products'))->render(),
            ]);
        }

        return view('products.index', compact(
            'products',
            'filterCategories',
            'brands', 
            'activeCategory',
            'sortOrder',
            'userWishlistProductIds'
        ));
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