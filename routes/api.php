<?php
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) { // Default API route
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/wishlist/count', function () { // Or just 'auth' if using web sessions
    return response()->json(['count' => Auth::user()->wishlistItems()->count()]);
})->name('api.wishlist.count');

Route::middleware('auth:sanctum')->get('/wishlist/status/{product}', function (Product $product) {
    return response()->json([
        'is_in_wishlist' => Auth::user()->hasInWishlist($product)
    ]);
});

Route::get('/cart/count', function () {
    return response()->json([
        'cart_distinct_items_count' => count(Session::get('cart', []))
    ]);
})->name('api.cart.count');

Route::get('/cart/status/{product}', function (Product $product) {
    // We can just call our global helper function here!
    return response()->json([
        'is_in_cart' => is_product_in_cart($product)
    ]);
});
?>