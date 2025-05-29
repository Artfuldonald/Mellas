<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function __construct()
    {
        // Require authentication for all wishlist actions
        $this->middleware('auth');
    }

    /**
     * Display the user's wishlist.
     */
    public function index()
    {
         
        $wishlistItems = Auth::user()->wishlistItems()->with([
            'product.images' => fn($q) => $q->orderBy('position')->limit(1),
            'product' => fn($q) => $q->withCount('approvedReviews as reviews_count')
                                     ->withAvg('approvedReviews as reviews_avg_rating', 'rating')
        ])->latest()->paginate(10); // Paginate if needed

        return view('wishlist.index', compact('wishlistItems'));
    }

    /**
     * Add a product to the user's wishlist.
     */
    public function add(Request $request, Product $product)
    {
        $user = Auth::user();

        // Check if already in wishlist
        if ($user->hasInWishlist($product)) {
            return back()->with('info', $product->name . ' is already in your wishlist.');
        }

        $user->wishlistItems()->create([
            'product_id' => $product->id,
        ]);

        return back()->with('success', $product->name . ' added to your wishlist!');
    }

    /**
     * Remove a product from the user's wishlist.
     * Can be called with either WishlistItem $item or Product $product
     */
    public function remove(Request $request, $productId) // Use productId to be flexible
    {
        $user = Auth::user();
        $item = $user->wishlistItems()->where('product_id', $productId)->first();

        if ($item) {
            $productName = $item->product->name ?? 'Product'; // Get name before deleting
            $item->delete();
            return back()->with('success', $productName . ' removed from your wishlist.');
        }

        return back()->with('error', 'Product not found in your wishlist or could not be removed.');
    }
}