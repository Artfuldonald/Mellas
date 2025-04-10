<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\AttributeController;

// Public Routes
Route::get('/', function () {
    return view('index');
});

// Product Overview Page (kept as a component)
Route::get('/product-overview', function () {
    return view('components.product-overview');
})->name('product-overview');

Route::get('/admin', function () {
    return view('admin.dashboard');
})->name('admin.dashboard');  

// Admin Dashboard Routes
Route::prefix('admin')->name('admin.')->group(function () {
   
    // Categories Resource Routes (index, create, store, etc.)
    Route::resource('categories', CategoryController::class);

    Route::resource('products', ProductController::class);

    Route::resource('attributes', AttributeController::class);

    
});

require __DIR__.'/auth.php';