<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\Webhook\MtnMomoWebhookController;

// Stateless API routes (no session, no CSRF). Session-dependent JSON
// endpoints such as cart/wishlist counts are defined in routes/web.php.

Route::get('/products/filter-count', [ProductController::class, 'getFilterCount'])->name('api.products.filter-count');

Route::post('/webhooks/momo', MtnMomoWebhookController::class)->name('webhooks.momo');
