<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use App\Models\Cart;     
use App\Policies\CartPolicy;

class AuthServiceProvider extends ServiceProvider
{
   
    protected $policies = [
        Cart::class => CartPolicy::class, 
        \App\Models\Address::class => \App\Policies\AddressPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}