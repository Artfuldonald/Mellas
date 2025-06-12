<?php
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

Route::get('/cart/count', function () {
    return response()->json([
        'cart_distinct_items_count' => count(Session::get('cart', []))
    ]);
})->name('api.cart.count');

?>