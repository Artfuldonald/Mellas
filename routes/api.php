<?php
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\Webhook\MtnMomoWebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) { // Default API route
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/wishlist/count', function () { 
    return response()->json(['count' => Auth::user()->wishlistItems()->count()]);
})->name('api.wishlist.count');

Route::middleware('auth:sanctum')->get('/wishlist/status/{product}', function (Product $product) {
    return response()->json([
        'is_in_wishlist' => Auth::user()->hasInWishlist($product)
    ]);
});

Route::get('/cart/count', function () {
    $count = Cart::where(
        Auth::check() ? ['user_id' => Auth::id()] : ['session_id' => session()->getId()]
    )->count();

    return response()->json(['count' => $count]);
});

Route::get('/cart/status/{product}', function (Product $product) {
    // We can just call our global helper function here!
    return response()->json([
        'is_in_cart' => is_product_in_cart($product)
    ]);
});

Route::post('/webhooks/momo', MtnMomoWebhookController::class)->name('webhooks.momo');
?>