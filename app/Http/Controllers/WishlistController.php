<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller; // Ensure this line
use App\Models\Product;
// use App\Models\User; // Not directly used if using Auth facade
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // For logging

class WishlistController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the user's wishlist.
     * (This method remains the same as it serves a full page)
     */
    public function index()
    {
        $user = Auth::user(); // Get the authenticated user
        $wishlistItems = $user->wishlistItems()->with([
            'product.images' => fn($q) => $q->select(['id', 'product_id', 'path', 'alt'])->orderBy('position')->limit(1), // Select specific image columns
            'product' => fn($q) => $q->select(['id', 'name', 'slug', 'price']) // Select specific product columns
                                     ->withCount('approvedReviews as reviews_count')
                                     ->withAvg('approvedReviews as reviews_avg_rating', 'rating')
        ])->latest()->paginate(10);

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add a product to the user's wishlist (AJAX).
     */
    public function add(Request $request, Product $product) // Route model binding for $product
    {
        $user = Auth::user();

        if ($user->hasInWishlist($product)) {
            return response()->json([
                'success' => false, // Or true, with a specific status like 'already_in_wishlist'
                'message' => $product->name . ' is already in your wishlist.',
                'is_in_wishlist' => true, // Current state
                'wishlist_count' => $user->wishlistItems()->count()
            ], 200); // 200 OK, but indicating it was already there
        }

        try {
            $user->wishlistItems()->create(['product_id' => $product->id]);
            $newCount = $user->wishlistItems()->count();
            // Dispatch event for server-side listeners if needed (e.g., analytics)
            // event(new ProductAddedToWishlist($user, $product));

            return response()->json([
                'success' => true,
                'message' => $product->name . ' added to your wishlist!',
                'is_in_wishlist' => true,
                'wishlist_count' => $newCount
            ]);
        } catch (\Exception $e) {
            Log::error("Error adding product {$product->id} to wishlist for user {$user->id}: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Could not add product to wishlist. Please try again.'
            ], 500); // Internal Server Error
        }
    }

    /**
     * Remove a product from the user's wishlist (AJAX).
     */
    public function remove(Request $request, Product $product) // Route model binding for $product
    {
        $user = Auth::user();
        $item = $user->wishlistItems()->where('product_id', $product->id)->first();

        if ($item) {
            try {
                $item->delete();
                $newCount = $user->wishlistItems()->count();
                // Dispatch event for server-side listeners if needed
                // event(new ProductRemovedFromWishlist($user, $product));

                return response()->json([
                    'success' => true,
                    'message' => $product->name . ' removed from your wishlist.',
                    'is_in_wishlist' => false,
                    'wishlist_count' => $newCount
                ]);
            } catch (\Exception $e) {
                Log::error("Error removing product {$product->id} from wishlist for user {$user->id}: " . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Could not remove product from wishlist. Please try again.'
                ], 500);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Product not found in your wishlist.'
        ], 404); // Not Found
    }
}