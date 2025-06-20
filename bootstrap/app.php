<?php

use Illuminate\Support\Facades\App;
use App\Providers\AuthServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))

    ->withProviders([       
        AuthServiceProvider::class,
    ])
    
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        //
         // --- REGISTER MIDDLEWARE ALIASES HERE ---
         $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

            // **** ADD YOUR ADMIN ALIAS ****
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            // **** END ADD ADMIN ALIAS ****
         ]);

         $middleware->redirectGuestsTo(fn () => route('login'));

         // --- CONFIGURE CSRF EXCEPTIONS HERE ---
        $middleware->validateCsrfTokens(except: [
            '/webhooks/mtn-momo', // Add your webhook URI pattern
            'stripe/*',          // Example: Exclude all Stripe webhooks
            'webhooks/*',        // Example: Exclude all under /webhooks
        ]);
       
        // This middleware will set headers to prevent caching
         $middleware->appendToGroup('web', [
            \App\Http\Middleware\PreventCaching::class,
        ]);
    })
    
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();