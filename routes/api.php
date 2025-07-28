<?php
use App\Models\Cart;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Webhook\MtnMomoWebhookController;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
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

Route::get('/cart/count', [CartController::class, 'getCount'])->name('api.cart.count');

Route::get('/products/filter-count', [ProductController::class, 'getFilterCount'])->name('api.products.filter-count');

Route::post('/webhooks/momo', MtnMomoWebhookController::class)->name('webhooks.momo');
?>