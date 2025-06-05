<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Js;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

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

        // === BRAND FILTER LOGIC ===
        if ($request->filled('brands') && is_array($request->input('brands'))) {
            $brandSlugs = $request->input('brands');
            $query->whereHas('brand', function ($q) use ($brandSlugs) {
                $q->whereIn('slug', $brandSlugs)->where('is_active', true);
            });
        }

        // --- Filtering by Category ---
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');
            $activeCategory = Category::where('slug', $categorySlug)->where('is_active', true)->firstOrFail();
            $query->whereHas('categories', function ($q) use ($activeCategory) {
                $q->where('categories.id', $activeCategory->id);
            });
        }

        // === FETCH BRANDS FOR FILTER ===
        $brandsForFilter = Brand::where('is_active', true)
                                ->whereHas('products', fn($q) => $q->where('is_active', true)) // Only show brands with active products
                                ->withCount(['products' => fn($q) => $q->where('is_active', true)]) // Count only active products
                                ->orderBy('name')
                                ->get(['id', 'name', 'slug']);

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
                 $query->whereNotNull('products.compare_at_price')
                  ->where('products.compare_at_price', '>', 0)
                  ->whereColumn('products.compare_at_price', '>', 'products.price')
                  ->whereRaw('((products.compare_at_price - products.price) * 100.0 / products.compare_at_price) >= ?', [$minDiscount]);
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

    public function show(Product $product)
    {
        if (!$product->is_active) {
            abort(404);
        }

        // Eager load for the main product being viewed
        $product->load([
            'categories' => fn($q) => $q->orderBy('categories.id'), // Get categories, ordered by their own ID
            'images' => fn($q) => $q->orderBy('position'),
            'videos' => fn($q) => $q->orderBy('position'),
            'variants' => function ($query) {
                $query->with(['attributeValues' => function($q) {
                    $q->select('attribute_values.id', 'attribute_values.value', 'attribute_values.attribute_id')
                      ->with('attribute:id,name');
                }]);
            },
            'attributes' => fn($q) => $q->with('values:id,value,attribute_id')->orderBy('name'),
            'approvedReviews.user:id,name',
            'brand:id,name,slug' // Eager load the brand of the current product
        ]);

        // Prepare variant and options data for JavaScript
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

        // --- "YOU MAY ALSO LIKE" LOGIC ---
        $relatedProductsLimit = 10; // As per your Jumia example
        $relatedProducts = collect();
        $loadedProductIds = [$product->id]; // Start with current product to exclude it

        // Define a reusable closure for common eager loads for related product cards
        $withCardData = function (Builder $query) {
            return $query->with([
                'images' => fn($q) => $q->orderBy('position')->limit(1),
                'brand:id,name,slug', // Eager load brand for the card
            ])
            ->withCount([
                'approvedReviews as reviews_count',
            ])
            ->withAvg('approvedReviews', 'rating');
        };

        $primaryCategory = $product->categories->first();

        if ($primaryCategory) {
            $primaryCategoryId = $primaryCategory->id;

            // Query 1: Same Brand & Same Primary Category
            if ($product->brand_id) {
                $sameBrandAndCategoryQuery = Product::where('is_active', true)
                    ->where('id', '!=', $product->id)
                    ->where('brand_id', $product->brand_id)
                    ->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $primaryCategoryId))
                    ->whereNotIn('id', $loadedProductIds); // Ensure not already loaded

                $sameBrandAndCategoryProducts = $withCardData($sameBrandAndCategoryQuery)
                    ->inRandomOrder() // Or some other relevant ordering
                    ->limit($relatedProductsLimit) // Fetch up to the limit
                    ->get();

                $relatedProducts = $relatedProducts->merge($sameBrandAndCategoryProducts);
                $loadedProductIds = array_merge($loadedProductIds, $sameBrandAndCategoryProducts->pluck('id')->toArray());
            }

            // Query 2: Different Brand & Same Primary Category (if needed)
            if ($relatedProducts->count() < $relatedProductsLimit) {
                $differentBrandSameCategoryQuery = Product::where('is_active', true)
                    ->where('id', '!=', $product->id)
                    ->when($product->brand_id, function ($q) use ($product) { // If current product has a brand, exclude it
                        $q->where('brand_id', '!=', $product->brand_id);
                    })
                    // If current product has NO brand, this shows all brands in category
                    ->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $primaryCategoryId))
                    ->whereNotIn('id', $loadedProductIds); // Ensure not already loaded

                $differentBrandSameCategoryProducts = $withCardData($differentBrandSameCategoryQuery)
                    ->inRandomOrder()
                    ->limit($relatedProductsLimit - $relatedProducts->count()) // Fetch remaining needed
                    ->get();

                $relatedProducts = $relatedProducts->merge($differentBrandSameCategoryProducts);
                $loadedProductIds = array_merge($loadedProductIds, $differentBrandSameCategoryProducts->pluck('id')->toArray());
            }

            // Fallback Query 3: Any other product in the same primary category (if still needed)
            if ($relatedProducts->count() < $relatedProductsLimit) {
                $anyBrandSameCategoryQuery = Product::where('is_active', true)
                    ->where('id', '!=', $product->id)
                    ->whereHas('categories', fn (Builder $q) => $q->where('categories.id', $primaryCategoryId))
                    ->whereNotIn('id', $loadedProductIds); // Ensure not already loaded

                 $anyBrandSameCategoryProducts = $withCardData($anyBrandSameCategoryQuery)
                    ->inRandomOrder()
                    ->limit($relatedProductsLimit - $relatedProducts->count()) // Fetch remaining needed
                    ->get();
                $relatedProducts = $relatedProducts->merge($anyBrandSameCategoryProducts);
                // $loadedProductIds = array_merge($loadedProductIds, $anyBrandSameCategoryProducts->pluck('id')->toArray()); // Not strictly needed if this is the last step for this category
            }
        }

        // Fallback Query 4: Globally featured products if very few or no category matches
        if ($relatedProducts->count() < $relatedProductsLimit / 2) { // e.g., if less than half the desired amount
            $globalFeaturedQuery = Product::where('is_active', true)
                ->where('is_featured', true)
                ->whereNotIn('id', $loadedProductIds);

            $globalFeaturedProducts = $withCardData($globalFeaturedQuery)
                ->inRandomOrder()
                ->limit($relatedProductsLimit - $relatedProducts->count())
                ->get();
            $relatedProducts = $relatedProducts->merge($globalFeaturedProducts);
        }

        // Final processing: ensure unique products and enforce the limit strictly.
        $relatedProducts = $relatedProducts->unique('id')->take($relatedProductsLimit);

        $userWishlistProductIds = Auth::check() ? Auth::user()->wishlistItems()->pluck('product_id')->toArray() : [];

        // Defensive check for product names before passing to Blade or JS
        // This is to prevent the 'str_contains' error if a name is unexpectedly not a string.
        $mainProductNameForJs = $product->name; // Assuming $product->name is usually a string
        if (!is_string($mainProductNameForJs)) {
            Log::warning("Main product (ID: {$product->id}) name is not a string. Defaulting.", ['name_data' => $mainProductNameForJs]);
            $mainProductNameForJs = 'Product Details'; // Provide a safe default
        }

        $relatedProducts->transform(function ($rp) {
            if (!is_string($rp->name)) {
                Log::warning("Related product (ID: {$rp->id}) name is not a string. Defaulting.", ['name_data' => $rp->name]);
                $rp->name = 'Related Item'; // Provide a safe default
            }
            // You might want to do similar checks for other string fields used in your cards
            // e.g., $rp->brand ? (is_string($rp->brand->name) ? $rp->brand->name : 'Brand') : 'No Brand';
            return $rp;
        });

        Log::info("Current Product ID: {$product->id}");
if ($primaryCategory) {
    Log::info("Primary Category ID for related products: {$primaryCategory->id}");
} else {
    Log::info("No primary category found for product ID: {$product->id}.");
}
Log::info("Count of loadedProductIds before final filter: " . count($loadedProductIds), $loadedProductIds);
Log::info("Related products count BEFORE unique/take: " . $relatedProducts->count());

// Final processing: ensure unique products and enforce the limit strictly.
$relatedProducts = $relatedProducts->unique('id')->take($relatedProductsLimit);

Log::info("Related products count AFTER unique/take: " . $relatedProducts->count());
if ($relatedProducts->isNotEmpty()) {
    Log::info("First related product example:", $relatedProducts->first()->toArray()); // Log first item
} else {
    Log::info("No related products found to pass to view.");
}

        return view('products.show', [
            'product' => $product,
            'variantDataForJs' => Js::from($variantData),
            'optionsDataForJs' => Js::from($optionsData),
            // If you pass product name to JS: 'productNameForJs' => Js::from($mainProductNameForJs),
            'relatedProducts' => $relatedProducts,
            'userWishlistProductIds' => $userWishlistProductIds,
        ]);
    }
}