<?php
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;

Route::middleware('auth:sanctum')->get('/user', function (Request $request) { // Default API route
    return $request->user();
});

Route::middleware('auth:sanctum')->get('/wishlist/count', function () { // Or just 'auth' if using web sessions
    return response()->json(['count' => Auth::user()->wishlistItems()->count()]);
})->name('api.wishlist.count');

?>