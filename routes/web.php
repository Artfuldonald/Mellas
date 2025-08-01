<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CartController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\BrandController;
use App\Services\MtnMomo\CollectionClient;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaxRateController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DiscountController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AttributeController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminBrandController;
use App\Http\Controllers\Admin\ShippingZoneController;
use App\Http\Controllers\Webhook\MtnMomoWebhookController;
use App\Http\Controllers\OrderController as ClientOrderController;
use App\Http\Controllers\ReviewController as ClientReviewController;
use App\Http\Controllers\ProductController as PublicProductController;

// Public Routes
Route::get('/', [HomeController::class, 'index'])->name('home'); 
    
// Product Overview Page (kept as a component)
Route::get('/product-overview', function () {
    return view('components.product-overview');
})->name('product-overview');

// --- Public Product Catalog Route ---
Route::get('/products', [PublicProductController::class, 'index'])->name('products.index');

Route::get('/products/{product:slug}', [PublicProductController::class, 'show'])->name('products.show');

// Route for submitting reviews
Route::post('/reviews', [ClientReviewController::class, 'store'])->name('reviews.store');

// Route for client brands
Route::get('/brands', [BrandController::class, 'index'])->name('brands.index'); // Page to list all brands
Route::get('/brands/{brand:slug}', [BrandController::class, 'show'])->name('brands.show');

//WISHLIST
Route::middleware(['auth'])->prefix('wishlist')->name('wishlist.')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');   
    Route::post('/add/{product}', [WishlistController::class, 'add'])->name('add');
    Route::post('/remove/{product}', [WishlistController::class, 'remove'])->name('remove');
});

// Cart routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add'); 
    Route::post('/update', [CartController::class, 'update'])->name('update');   
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');   
    Route::post('/update-item', [CartController::class, 'updateItem'])->name('update-item');
    Route::post('/remove-item', [CartController::class, 'removeItem'])->name('remove-item');    
    Route::post('/remove-simple', [CartController::class, 'removeSimpleProduct'])->name('remove-simple');   
    Route::post('/set-quantity', [CartController::class, 'setQuantity'])->name('set-quantity');
});

// Checkout Routes
Route::middleware('auth')->prefix('checkout')->name('checkout.')->group(function () {

     Route::get('/', [CheckoutController::class, 'index'])->name('index');
     Route::post('/process', [CheckoutController::class, 'process'])->name('process');
     Route::get('/payment', [CheckoutController::class, 'showPaymentPage'])->name('payment.show');    

     // --- ADDRESS MANAGEMENT DURING CHECKOUT ---   
    Route::get('/addresses', [CheckoutController::class, 'showAddresses'])->name('addresses.show');
    Route::post('/addresses/select', [CheckoutController::class, 'selectAddress'])->name('addresses.select');
    Route::get('/addresses/create', [CheckoutController::class, 'createAddress'])->name('addresses.create');
    Route::post('/addresses', [CheckoutController::class, 'storeAddress'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [CheckoutController::class, 'editAddress'])->name('addresses.edit');
    Route::put('/addresses/{address}', [CheckoutController::class, 'updateAddress'])->name('addresses.update');   
    
    Route::post('/payment-method/select', [CheckoutController::class, 'selectPaymentMethod'])->name('payment.select');

    Route::get('/processing/{order}', [CheckoutController::class, 'showProcessingPage'])
    ->name('processing')->middleware('auth');
    Route::get('/status/{order}', [CheckoutController::class, 'checkPaymentStatus'])
    ->name('status')->middleware('auth');

    // --- POST-CHECKOUT & PAYMENT STATUS --- 
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');    
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('cancel');   
    Route::get('/status/{order}', [CheckoutController::class, 'checkPaymentStatus'])->name('payment.status');

});    

//Order Routes
Route::middleware(['auth'])->group(function () {   

    Route::get('/orders', [ClientOrderController::class, 'index'])->name('orders.index');   
    Route::get('/orders/{order}', [ClientOrderController::class, 'show'])->name('orders.show');
    Route::get('/profile/orders', [ClientOrderController::class, 'index'])->name('profile.orders.index');

});

// --- MTN MOMO Webhook Route ---
Route::post('/webhooks/mtn-momo', [MtnMomoWebhookController::class, 'handle'])
     ->name('webhooks.mtn-momo');
    
// --- Profile Management ---
Route::middleware('auth')->prefix('profile')->name('profile.')->group(function () {
    Route::get('/overview', [ProfileController::class, 'overview'])->name('overview');
    Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
    Route::patch('/update', [ProfileController::class, 'update'])->name('update');
    Route::delete('/destroy', [ProfileController::class, 'destroy'])->name('destroy');
    
    // These routes will handle address management from the "My Account" section.
    Route::get('/addresses', [CheckoutController::class, 'showAddresses'])->name('addresses.show');
    Route::get('/addresses/create', [CheckoutController::class, 'createAddress'])->name('addresses.create');
    Route::post('/addresses', [CheckoutController::class, 'storeAddress'])->name('addresses.store');
    Route::get('/addresses/{address}/edit', [CheckoutController::class, 'editAddress'])->name('addresses.edit');
    Route::put('/addresses/{address}', [CheckoutController::class, 'updateAddress'])->name('addresses.update');    
    
    Route::get('/inbox', function () {
        return view('profile.inbox');
    })->name('inbox');
});

// Admin Dashboard Routes
Route::prefix('admin')->middleware(['auth', 'admin'])->name('admin.')->group(function () {
   
    Route::get('/dashboard', [DashboardController::class, 'index']) 
    ->name('dashboard');
    
    // Categories Resource Routes (index, create, store, etc.) 
    Route::resource('categories', CategoryController::class);

    Route::resource('products', ProductController::class);

     // Brand Resource Routes (index, create, store, etc.) 
    Route::resource('brands', AdminBrandController::class);
    
    // Stock Adjustment Routes
    // For simple product
    Route::get('products/{product}/stock/adjust', [ProductController::class, 'showStockAdjustmentForm'])->name('products.stock.adjust.form');
    Route::post('products/{product}/stock/adjust', [ProductController::class, 'adjustStock'])->name('products.stock.adjust');
    // For product variant
    Route::get('products/{product}/variants/{variant}/stock/adjust', [ProductController::class, 'showStockAdjustmentForm'])->name('products.variants.stock.adjust.form');
    Route::post('products/{product}/variants/{variant}/stock/adjust', [ProductController::class, 'adjustStock'])->name('products.variants.stock.adjust');

     // attributes  Route 
    Route::resource('attributes', AttributeController::class);
    
    Route::resource('orders', OrderController::class)->except([
        'create', 'store',  
    ]);

     Route::prefix('reviews')->name('reviews.')->group(function () {
        Route::get('/', [ReviewController::class, 'index'])->name('index');
        Route::patch('/{review}/approve', [ReviewController::class, 'approve'])->name('approve');
        Route::patch('/{review}/unapprove', [ReviewController::class, 'unapprove'])->name('unapprove');
        Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    });
    //want destroy later:
        // Route::delete('orders/{order}', [OrderController::class, 'destroy'])->name('orders.destroy');
    //});

    Route::resource('admin-users', AdminUserController::class);

    Route::resource('customers', CustomerController::class)->except([
        'create', 'store', 'destroy'
    ]);
    Route::post('customers/{customer}/send-reset-link', [CustomerController::class, 'sendPasswordResetLink'])
        ->name('customers.send_reset_link');    
        
    //Route::get('settings', [App\Http\Controllers\Admin\SettingController::class, 'index'])
    //->name('settings.index');    
    Route::resource('shipping-zones', ShippingZoneController::class);

        // Nested routes for managing RATES *within* a specific Zone
        Route::prefix('shipping-zones/{shipping_zone}/rates')->name('shipping-zones.rates.')->group(function() {
            Route::get('/create', [ShippingZoneController::class, 'createRate'])->name('create');
            Route::post('/', [ShippingZoneController::class, 'storeRate'])->name('store');
            Route::get('/{shipping_rate}/edit', [ShippingZoneController::class, 'editRate'])->name('edit');
            Route::put('/{shipping_rate}', [ShippingZoneController::class, 'updateRate'])->name('update');
            Route::delete('/{shipping_rate}', [ShippingZoneController::class, 'destroyRate'])->name('destroy');
        });

         // --- Tax Management Routes ---
    Route::resource('tax-rates', TaxRateController::class);
    
    // --- Discount Management Routes ---
    Route::resource('discounts', DiscountController::class)->except(['show']);

    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit'); // Route to show the form
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';