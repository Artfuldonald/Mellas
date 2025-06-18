<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Js;
use Illuminate\Support\Str;
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
        $baseProductFields = [
        'products.id', 'products.name', 'products.slug', 'products.price',
        'products.compare_at_price', 'products.quantity', // <-- IMPORTANT
        'products.brand_id', // <-- Needed for brand relationship
        'products.created_at',
        // 'products.is_active' // Already filtered by where clause
    ];

        $query = Product::select($baseProductFields) 
                    ->where('products.is_active', true)
                    ->with([
                        'images' => fn($q) => $q->select(['id', 'product_id', 'path', 'alt'])->orderBy('position')->limit(1),
                        'brand:id,name,slug', // Eager load the brand
                    ])
                    ->withCount([
                        'variants', // <-- ENSURE THIS IS PRESENT
                        'approvedReviews as reviews_count'
                    ])
                    ->withAvg('approvedReviews', 'rating'); 

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
        if (!$product->is_active) { abort(404); }

        // Eager load all relationships needed for the entire page
        $product->load([
            'images', 'brand', 'variants.attributeValues.attribute',
            'attributes.values', 'approvedReviews.user'
        ]);
        $product->loadCount(['variants', 'approvedReviews']);
        $product->loadAvg('approvedReviews as average_rating', 'rating');

        // --- Data for the main interactive component ---
        $productDataForView = $this->transformProductForView($product);

        // --- Data for other partials ---
        $reviews = $product->approvedReviews()->latest()->take(5)->get();
        $ratingDistribution = $this->getRatingDistribution($product);
        $relatedProducts = $this->getRelatedProducts($product); // Use your existing helper
        $userWishlistProductIds = Auth::check() ? Auth::user()->wishlistItems()->pluck('product_id')->toArray() : [];

        return view('products.show', [
            'productData' => $productDataForView,
            'reviews' => $reviews,
            'ratingDistribution' => $ratingDistribution,
            'relatedProducts' => $relatedProducts,
            'userWishlistProductIds' => $userWishlistProductIds
        ]);
    }

    // This helper creates the array structure that your V0 design expects
    private function transformProductForView(Product $product): array
    {
        $variantsTransformed = [];
        $variantStock = [];
        
        // Check if product has variants
        $hasVariants = $product->variants()->exists();
        
        if($hasVariants) {
            // Group variants by attribute for display
            $variantsByAttribute = [];
            foreach ($product->variants as $variant) {
                if ($variant->is_active) {
                    foreach ($variant->attributeValues as $attributeValue) {
                        $attributeName = strtolower($attributeValue->attribute->name);
                        $variantsByAttribute[$attributeName][] = [
                            'value' => $attributeValue->value,
                            'variant_id' => $variant->id,
                            'price' => (float)$variant->price,
                            'stock' => (int)$variant->quantity,
                            'sku' => $variant->sku
                        ];
                    }
                }
            }
            
            // Transform for frontend
            foreach ($variantsByAttribute as $attribute => $variants) {
                $variantsTransformed[$attribute] = array_unique(array_column($variants, 'value'));
                
                // Store stock info for each variant option
                foreach ($variants as $variant) {
                    $variantStock[$attribute][$variant['value']] = [
                        'stock' => $variant['stock'],
                        'price' => $variant['price'],
                        'variant_id' => $variant['variant_id']
                    ];
                }
            }
        }

        // Calculate total stock
        $totalStock = $hasVariants 
            ? $product->variants()->where('is_active', true)->sum('quantity')
            : $product->quantity;

        // Check if in stock
        $inStock = $hasVariants 
            ? $product->variants()->where('is_active', true)->where('quantity', '>', 0)->exists()
            : $product->quantity > 0;

        return [
            'id' => $product->id,
            'name' => $product->name,
            'brand' => $product->brand?->name ?? 'Unbranded',
            'price' => (float)$product->price,
            'original_price' => (float)$product->compare_at_price,
            'discount' => ($product->compare_at_price > $product->price) ? round((($product->compare_at_price - $product->price) / $product->compare_at_price) * 100) : 0,
            'rating' => round($product->average_rating ?? 0, 1),
            'review_count' => (int)$product->approved_reviews_count,
            'in_stock' => $inStock,
            'stock_count' => (int)$totalStock,
            'has_variants' => $hasVariants,
            'images' => $product->images->pluck('image_url')->all(),
            'main_image' => $product->images->first()?->image_url ?? asset('images/placeholder.png'),
            'description' => $product->description,
            'features' => json_decode($product->features, true) ?? [],
            'specifications' => (array)$product->specifications,
            'variants' => $variantsTransformed,
            'variant_stock' => $variantStock, // Add this for stock info per variant
            'shipping' => [ 'free' => true, 'estimated_days' => '2-3 days', 'return_policy' => '30-day returns' ],
        ];
    }

    
    private function getRelatedProducts(Product $product) {

        $relatedProductsLimit = 10;
        $relatedProducts = collect();
        $loadedProductIds = [$product->id];

        $withCardData = function (Builder $query) {
            return $query->with([
                'images' => fn($q) => $q->orderBy('position')->limit(1),
                'brand:id,name,slug',
            ])
            ->withCount(['approvedReviews as reviews_count'])
            ->withAvg('approvedReviews', 'rating');
        };

        $primaryCategory = $product->categories->first();

        if ($primaryCategory && $product->brand_id) {
            $sameBrandProducts = $withCardData(
                Product::where('is_active', true)
                    ->where('id', '!=', $product->id)
                    ->where('brand_id', $product->brand_id)
                    ->whereHas('categories', fn($q) => $q->where('categories.id', $primaryCategory->id))
            )->inRandomOrder()->limit($relatedProductsLimit)->get();
            
            $relatedProducts = $relatedProducts->merge($sameBrandProducts);
        }

        if ($relatedProducts->count() < $relatedProductsLimit && $primaryCategory) {
            $sameCategoryProducts = $withCardData(
                Product::where('is_active', true)
                    ->where('id', '!=', $product->id)
                    ->whereNotIn('id', $relatedProducts->pluck('id'))
                    ->whereHas('categories', fn($q) => $q->where('categories.id', $primaryCategory->id))
            )->inRandomOrder()->limit($relatedProductsLimit - $relatedProducts->count())->get();
            
            $relatedProducts = $relatedProducts->merge($sameCategoryProducts);
        }

        $relatedProducts = $relatedProducts->unique('id')->take($relatedProductsLimit);
        
        return $relatedProducts->unique('id')->take($relatedProductsLimit);
    }


     private function getRatingDistribution(Product $product)
    {
        // Get the total number of approved reviews for this product
        $totalReviews = $product->approved_reviews_count;

        if ($totalReviews === 0) {
            return collect(); // Return an empty collection if there are no reviews
        }

        // Use a database query to get the count for each rating (5, 4, 3, 2, 1)
        // This is much more efficient than loading all reviews into memory
        $distribution = $product->approvedReviews()
            ->select('rating', DB::raw('count(*) as count'))
            ->groupBy('rating')
            ->orderBy('rating', 'desc')
            ->get()
            ->keyBy('rating'); // Key the collection by the rating for easy access

        $ratingDistribution = collect();

        // Build the final structure, ensuring all 5 levels are present
        for ($i = 5; $i >= 1; $i--) {
            $count = $distribution->get($i)?->count ?? 0;
            $ratingDistribution->push([
                'stars' => $i,
                'count' => $count,
                'percentage' => round(($count / $totalReviews) * 100),
            ]);
        }

        return $ratingDistribution;
    }
}
