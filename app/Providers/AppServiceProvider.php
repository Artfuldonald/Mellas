<?php

namespace App\Providers;

use App\Models\Cart;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Events\OrderPlaced;         
use App\Listeners\UpdateStockLevel; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event; 
use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RedirectIfAuthenticated::redirectUsing(function ($request) {
            if (Auth::check()) { // Check if logged in
                
                if (Auth::user()->is_admin) {
                    // Logged-in Admin trying guest route -> redirect to admin dash
                    return route('admin.dashboard'); // <<< Correct
                } else {
                    // Logged-in Customer trying guest route -> redirect to public homepage
                    return route('home'); // <<< Redirect non-admins to 'home'
                }
            }
            // If not logged in, let the request continue (to login/register etc.)
        });

        Event::listen(
            OrderPlaced::class,
            UpdateStockLevel::class
        );
         
        
   View::composer(['components.header', 'layouts.app'], function ($view) {
            // Categories for Navigation
            if (!isset($view->navCategories)) {
                try {
                    $navCategories = Category::whereNull('parent_id')
                                            ->where('is_active', true)
                                            ->with([
                                                'children' => function($query) {
                                                    $query->where('is_active', true)
                                                          ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('name')])
                                                          ->orderBy('name');
                                                }
                                            ])
                                            ->orderBy('name')
                                            ->get();
                    $view->with('navCategories', $navCategories);
                } catch (\Exception $e) {
                    Log::error('View Composer Error fetching navCategories: ' . $e->getMessage());
                    $view->with('navCategories', collect());
                }
            }

            // Wishlist Count for Header
            if (Auth::check() && !isset($view->wishlistCountGlobal)) {                 
                try {
                    
                    $wishlistCountGlobal = Auth::user()->wishlistItems()->count();
                    $view->with('wishlistCountGlobal', $wishlistCountGlobal);
                } catch (\Exception $e) {
                    Log::error('View Composer Error fetching wishlistCount: ' . $e->getMessage());
                    $view->with('wishlistCountGlobal', 0); // Default to 0 on error
                }
            } elseif (!Auth::check() && !isset($view->wishlistCountGlobal)) {
                 $view->with('wishlistCountGlobal', 0); // Default to 0 for guests
            }

            // Cart Count for Header
            if (!isset($view->cartDistinctItemsCountGlobal)) {
            $cartDistinctItemsCountGlobal = count(session('cart', []));
            $view->with('cartDistinctItemsCountGlobal', $cartDistinctItemsCountGlobal);        
        }
        });

        /**
     * Custom Blade directive to check if a product (or any of its variants) is in the cart.
     * Usage: @if_in_cart($product) ... @endif
     */
    Blade::if('if_in_cart', function ($product) {
        if (!$product) {
            return false;
        }

        $cart = session('cart', []);

        // Check for simple product first
        if (isset($cart[$product->id])) {
            return true;
        }

        // If it's a variant product, check if any of its variant IDs exist as a key prefix
        // This is more robust than looping through all cart items
        if ($product->variants_count > 0) {
            // This checks if any key in the cart array starts with "PRODUCT_ID-"
            foreach (array_keys($cart) as $cartItemId) {
                if (is_string($cartItemId) && Str::startsWith($cartItemId, $product->id . '-')) {
                    return true;
                }
            }
        }
        
        return false;
    });

    Blade::if('isProductInCart', function (Product $product) {
            // This logic checks if a simple product (no variant) is in the cart.
            $query = Auth::check()
                ? Cart::where('user_id', auth()->id())
                : Cart::where('session_id', session()->getId());

            return $query->where('product_id', $product->id)
                         ->whereNull('variant_id')
                         ->exists();
        });

    }
}