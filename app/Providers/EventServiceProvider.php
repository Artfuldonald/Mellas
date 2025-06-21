<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Login; // 1. IMPORT THE LOGIN EVENT
use App\Listeners\MergeGuestCartAfterLogin; // 2. IMPORT YOUR NEW LISTENER
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],       
        Login::class => [
            MergeGuestCartAfterLogin::class,
        ],
    ];
    
}