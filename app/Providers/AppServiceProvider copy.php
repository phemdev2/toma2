<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register any application services here
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Share subscription expiry status with all views
        View::composer('*', function ($view) {
            if (Auth::check()) {
                // Check if the user's subscription has expired
                $isSubscriptionExpired = Auth::user()->subscription_expiry_date < now();

                // Share the 'isSubscriptionExpired' variable with all views
                $view->with('isSubscriptionExpired', $isSubscriptionExpired);
            } else {
                // If no user is authenticated, set subscription expiry to true (as a fallback)
                $view->with('isSubscriptionExpired', true);
            }
        });
    }
}
