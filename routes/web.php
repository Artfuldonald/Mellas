<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
});
 
Route::get('/product-overview', function () {
    return view('components.product-overview');
})->name('product-overview');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Admin Dashboard Routes
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard - using existing view
    Route::get('/', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // Products - using existing view   
    Route::get('/products', function () {
        return view('products', ['activeSection' => 'products']);
    })->name('products');
    // Accounts Management
    Route::get('/accounts', function () {
        // Since you don't have a dedicated view, we'll reuse dashboard
        // with a different active section
        return view('dashboard', ['activeSection' => 'accounts']);
    })->name('accounts');
    
    // Transactions
    Route::get('/transactions', function () {
        return view('dashboard', ['activeSection' => 'transactions']);
    })->name('transactions');
    
    // Bills & Payments
    Route::get('/bills', function () {
        return view('dashboard', ['activeSection' => 'bills']);
    })->name('bills');
    
    // Reports
    Route::get('/reports', function () {
        return view('analytics', ['activeSection' => 'reports']);
    })->name('reports');
    
    // Users & Permissions
    Route::get('/users', function () {
        return view('dashboard', ['activeSection' => 'users']);
    })->name('users');
    
    // Settings
    Route::get('/settings', function () {
        return view('dashboard', ['activeSection' => 'settings']);
    })->name('settings');
    
    // Help & Support
    Route::get('/help', function () {
        return view('dashboard', ['activeSection' => 'help']);
    })->name('help');
});

require __DIR__.'/auth.php';