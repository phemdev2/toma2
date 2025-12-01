<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    // Share subscription expiry status and date with all views
    View::composer('*', function ($view) {
        // Initialize default values for unauthenticated users
        $isSubscriptionExpired = true;
        $planType = null;
        $startDate = null; // Initialize start date variable
        $subscriptionExpiryDateFormatted = 'No expiry date available'; // Default message
        $subscriptionType = null; // Default value for subscription type
    
        if (Auth::check()) {
            $user = Auth::user();
            
            // Ensure start_date is available
            if (!empty($user->start_date)) {
                $startDate = Carbon::parse($user->start_date)->format('Y-m-d'); // Format the date
                
                // Define subscription durations in days
                $plans = [
                    'trial' => 15,
                    'basic' => 30,
                    'premium' => 365,
                    'etc' => 1,
                ];
            
                // Get the user's plan type
                $planType = $user->plan_type;
    
                // Check if the plan type exists in the defined plans
                if (array_key_exists($planType, $plans)) {
                    // Calculate the expiry date based on the user's start date and plan type
                    $subscriptionExpiryDate = Carbon::parse($user->start_date)->addDays($plans[$planType]);
                    $isSubscriptionExpired = $subscriptionExpiryDate->isPast();
                    
                    // Format the expiry date for display
                    $subscriptionExpiryDateFormatted = $subscriptionExpiryDate->format('Y-m-d');
                } else {
                    // Handle invalid plan types if necessary (optional)
                    // Log an error or set default values as needed
                }

                // Set the subscription type (planType) for the view
                $subscriptionType = $planType;
            }
        }
    
        // Pass all relevant data to the view
        $view->with([
            'isSubscriptionExpired' => $isSubscriptionExpired,
            'planType' => $planType,
            'startDate' => $startDate, // Pass the start date to the view
            'subscriptionExpiryDate' => $subscriptionExpiryDateFormatted,
            'subscriptionType' => $subscriptionType,
        ]);
    });
}

}
