<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\Facades\Log;
use App\Events\OrderPlaced;         
use App\Listeners\UpdateStockLevel; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event; 
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
            // Check if already set to avoid re-querying if multiple composers hit the same view instance
            if (!isset($view->navCategories)) {
                try {
                    $navCategories = Category::whereNull('parent_id') // Get top-level categories
                                            ->where('is_active', true) // Only active ones
                                            ->with([ // Eager load children and their children (grandchildren)
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
                    $view->with('navCategories', collect()); // Pass empty collection on error
                }
            }
        });
    }
}