<?php

namespace App\Http\Controllers;

use App\Models\Cart;
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
        'products.compare_at_price', 'products.quantity', 
        'products.brand_id', 
        'products.created_at',        
    ];

        $query = Product::select($baseProductFields) 
                    ->where('products.is_active', true)
                    ->with([
                        'media',
                        'brand:id,name,slug', // Eager load the brand
                    ])
                    ->withCount([
                        'variants', // <-- ENSURE THIS IS PRESENT
                        'approvedReviews as reviews_count'
                    ])
                    ->withAvg('approvedReviews', 'rating'); 

        $activeCategory = null;
        $breadcrumbs = [];          
        

        //  BRAND FILTER LOGIC 
        if ($request->filled('brands') && is_array($request->input('brands'))) {
            $brandSlugs = $request->input('brands');
            $query->whereHas('brand', function ($q) use ($brandSlugs) {
                $q->whereIn('slug', $brandSlugs)->where('is_active', true);
            });
        }

        //  Filtering by Category & BUILDING BREADCRUMBS 
        if ($request->filled('category')) {
            $categorySlug = $request->input('category');            
            
            $activeCategory = Category::where('slug', $categorySlug)
                ->where('is_active', true)
                ->with('parent.parent') // Load parent, and grandparent. Add more .parent for deeper levels if needed.
                ->firstOrFail();

            $query->whereHas('categories', function ($q) use ($activeCategory) {
                $q->where('categories.id', $activeCategory->id);
            });

            //  THIS IS THE NEW LOGIC TO BUILD THE HIERARCHY 
            $current = $activeCategory;
            // Loop backwards from the current category to its top-level parent
            while ($current) {
                // Add the category to the start of the array
                array_unshift($breadcrumbs, $current);
                $current = $current->parent; // Move to the ne xt parent up the chain
            }

        }

        $navCategories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->with('children.children') // Eager load a few levels deep
            ->orderBy('name')
            ->get();


        //$genders = ['Male', 'Female', 'Unisex'];

        //  FETCH BRANDS FOR FILTER 
        $brandsForFilter = Brand::where('is_active', true)
                                ->whereHas('products', fn($q) => $q->where('is_active', true)) // Only show brands with active products
                                ->withCount(['products' => fn($q) => $q->where('is_active', true)]) // Count only active products
                                ->orderBy('name')
                                ->get(['id', 'name', 'slug']);

        //  Filtering by Price Range 
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float)$request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float)$request->input('price_max'));
        }

        //  Filtering by Discount Percentage 
        if ($request->filled('discount_min')) {
            $minDiscount = (int)$request->input('discount_min');
            if ($minDiscount > 0) {
                 $query->whereNotNull('products.compare_at_price')
                  ->where('products.compare_at_price', '>', 0)
                  ->whereColumn('products.compare_at_price', '>', 'products.price')
                  ->whereRaw('((products.compare_at_price - products.price) * 100.0 / products.compare_at_price) >= ?', [$minDiscount]);
            }
        }

        // Filtering by Product Rating 
        if ($request->filled('rating_min')) {
            $minRating = (int) $request->input('rating_min');
            if ($minRating > 0) {
                // We filter products that HAVE an average rating greater than or equal to the minimum.
                // The `has('approvedReviews')` ensures we don't include products with no reviews.
                $query->whereHas('approvedReviews', function (Builder $query) use ($minRating) {
                    $query->select(DB::raw('avg(rating)'))
                        ->groupBy('product_id')
                        ->havingRaw('avg(rating) >= ?', [$minRating]);
                });
            }
        }

        // --- Other Filters (Example: Shipped From) ---
        
        
        $activeFilters = [
            'category' => $request->input('category'),
            'brands' => $request->input('brands', []), 
            'price_min' => $request->input('price_min'),
            'price_max' => $request->input('price_max'),
            'discount_min' => $request->input('discount_min'),
            'rating_min' => $request->input('rating_min'), 
            //'gender' => $request->input('gender'),            
        ];

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

         return view('products.index', [
            'products' => $products,
            'filterCategories' => $filterCategories, // You can probably remove this and just use navCategories for both
            'brands' => $brandsForFilter, // Changed to use the more complete brands list
            'activeCategory' => $activeCategory,
            'sortOrder' => $sortOrder,
            'userWishlistProductIds' => $userWishlistProductIds,
            'breadcrumbs' => $breadcrumbs,
            // New variables for the mobile filter
            'navCategories' => $navCategories,
            //'genders' => $genders,
            'activeFilters' => $activeFilters,
        ]);
    }

    private function applyFiltersToQuery(Request $request, Builder $query): Builder
    {
        if ($request->filled('brands') && is_array($request->input('brands'))) {
            $query->whereHas('brand', fn($q) => $q->whereIn('slug', $request->input('brands'))->where('is_active', true));
        }
        if ($request->filled('category')) {
            $query->whereHas('categories', fn($q) => $q->where('slug', $request->input('category')));
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', (float)$request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', (float)$request->input('price_max'));
        }
        if ($request->filled('discount_min')) {
            $minDiscount = (int)$request->input('discount_min');
            if ($minDiscount > 0) {
                 $query->whereNotNull('products.compare_at_price')->where('products.compare_at_price', '>', 0)
                      ->whereColumn('products.compare_at_price', '>', 'products.price')
                      ->whereRaw('((products.compare_at_price - price) * 100.0 / products.compare_at_price) >= ?', [$minDiscount]);
            }
        }
        if ($request->filled('rating_min')) {
            $minRating = (int) $request->input('rating_min');
            if ($minRating > 0) {
                $query->whereHas('approvedReviews', function (Builder $q) use ($minRating) {
                    $q->select(DB::raw('avg(rating)'))->groupBy('product_id')->havingRaw('avg(rating) >= ?', [$minRating]);
                });
            }
        }
        // Add any other filters (like gender) here in the future
        
        return $query;
    }

    // ***** NEW: API method to return only the count *****
    public function getFilterCount(Request $request)
    {
        $query = Product::query()->where('is_active', true);

        // Use our reusable filter method
        $query = $this->applyFiltersToQuery($request, $query);

        // Get the count and return it
        $count = $query->count();

        return response()->json(['count' => $count]);
    }

   public function show(Product $product)
    {
        if (!$product->is_active) { abort(404); }
     
        $product->load([
            'media', 
            'brand', 
            'variants.attributeValues.attribute',
            'attributes.values', 
            'approvedReviews.user',           
            'categories' => function ($query) {              
                $query->with('parent.parent.parent'); 
            }
        ]);
        $product->loadCount(['variants', 'approvedReviews']);
        $product->loadAvg('approvedReviews as average_rating', 'rating');
        
        $productDataForView = $this->transformProductForView($product);
        
        //BREADCRUMB DATA PREPARATION 
        $breadcrumbsData = [];
        $primaryCategory = $product->categories->first();

        if ($primaryCategory) {
            $current = $primaryCategory;
            $categoryPath = [];
            while ($current) {
                // Add to the start of the temporary array
                array_unshift($categoryPath, $current);
                $current = $current->parent;
            }

            // Now convert the category objects into the simple array structure
            foreach ($categoryPath as $category) {
                $breadcrumbsData[] = [
                    'name' => $category->name,
                    'url' => route('products.index', ['category' => $category->slug])
                ];
            }
        }
        // Finally, add the current product to the end of the breadcrumbs
        $breadcrumbsData[] = [
            'name' => Str::limit($product->name, 30), // Limit the name for display
            'url' => route('products.show', $product->slug) // The current page
        ];
        //END BREADCRUMB DATA PREPARATION 
     
        $reviews = $product->approvedReviews()->with('user')->latest()->paginate(5);
        $ratingDistribution = $this->getRatingDistribution($product);
        $relatedProducts = $this->getRelatedProducts($product);
        $userWishlistProductIds = auth()->check() ? auth()->user()->wishlistItems()->pluck('product_id')->toArray() : [];
       
        return view('products.show', [
            'productData' => $productDataForView,
            'reviews' => $reviews,
            'ratingDistribution' => $ratingDistribution,
            'relatedProducts' => $relatedProducts,
            'userWishlistProductIds' => $userWishlistProductIds,
            'breadcrumbs' => $breadcrumbsData, 
        ]);
    }   
    
    private function transformProductForView(Product $product): array
    {
        $variantsTransformed = [];
          
        $variantDataMap = [];

        $hasVariants = $product->variants_count > 0;

        if ($hasVariants) {
            // Eager load the relationships on the variants collection
            $product->variants->load('attributeValues.attribute');

            foreach ($product->variants as $variant) {
                if ($variant->is_active) {
                    $options = [];
                    $attributesForThisVariant = [];

                    foreach ($variant->attributeValues as $attributeValue) {
                        $attributeName = strtolower($attributeValue->attribute->name);
                        $optionValue = $attributeValue->value;
                        
                        // Build the options for display (e.g., all available colors)
                        if (!isset($variantsTransformed[$attributeName])) $variantsTransformed[$attributeName] = [];
                        if (!in_array($optionValue, $variantsTransformed[$attributeName])) {
                            $variantsTransformed[$attributeName][] = $optionValue;
                        }
                        
                        $options[] = $optionValue;
                        $attributesForThisVariant[$attributeName] = $optionValue;
                    }

                    // Create a unique key for this combination (e.g., "Black-XL")
                    sort($options);
                    $combinationKey = implode('-', $options);
                    
                    // Add this variant's full details to our new master map
                    $variantDataMap[$combinationKey] = [
                        'id'    => $variant->id,
                        'price' => (float) $variant->price,
                        'stock' => (int) $variant->quantity,
                        'sku'   => $variant->sku,
                        'name'  => $variant->name, // The "Black / XL" name
                        'attributes' => $attributesForThisVariant,
                    ];
                }
            }
            
            // Make sure the option lists are unique
            foreach ($variantsTransformed as $key => $values) {
                $variantsTransformed[$key] = array_values(array_unique($values));
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


        $mediaItems = $product->getMedia('default');

        $imageGallery = $mediaItems->map(function ($media) {
            return [
                'thumb_url' => $media->getUrl('gallery_thumbnail'), 
                'large_url' => $media->getUrl('gallery_main'),   
                'original_url' => $media->getUrl(),   
            ];
        })->all();

        $mainImageUrl = $mediaItems->first()?->getUrl('gallery_main') ?? asset('images/placeholder.png');

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
            'images' => $imageGallery, 
            'main_image' => $mainImageUrl,
            'description' => $product->description,
            'features' => json_decode($product->features, true) ?? [],
            'specifications' => (array)$product->specifications,
            'variants' => $variantsTransformed,            
            'shipping' => [ 'free' => true, 'estimated_days' => '2-3 days', 'return_policy' => '30-day returns' ],
            'variant_data_map' => $variantDataMap,
            
        ];
    }

       private function getRelatedProducts(Product $product) {

        $relatedProductsLimit = 10;
        $relatedProducts = collect();
        $loadedProductIds = [$product->id];

        $withCardData = function (Builder $query) {
            return $query->with([
                'media',
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

    public function removeSimpleProduct(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $query = Auth::check()
            ? Cart::where('user_id', auth()->id())
            : Cart::where('session_id', session()->getId());

        $cartItem = $query->where('product_id', $validated['product_id'])
                        ->whereNull('variant_id') // Ensure we only target simple products
                        ->first();

        if ($cartItem) {
            $cartItem->delete();
        }

        // Return the fresh cart state, even if the item wasn't found (harmless)
        return response()->json([
            'success'     => true,
            'message'     => 'Item removed from cart.',
            'cart_totals' => $this->getCartState()['totals'],
            'cart_count'  => $this->getCartState()['item_count'],
        ]);
    }
}