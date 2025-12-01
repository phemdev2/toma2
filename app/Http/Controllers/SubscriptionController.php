<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class SubscriptionController extends Controller
{
    /**
     * Show the subscription details page.
     */
    public function index()
    {
        // Default values for subscription status
        $isSubscriptionExpired = true;
        $planType = null;
        $subscriptionExpiryDateFormatted = 'No expiry date available'; // Default message

        // Check if the user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Ensure that the user has a start date
            if (!empty($user->start_date)) {
                $startDate = Carbon::parse($user->start_date);
                
                // Define subscription durations in days for each plan type
                $plans = [
                    'trial' => 15,   // Trial plan expires in 15 days
                    'basic' => 30,   // Basic plan expires in 30 days
                    'premium' => 365, // Premium plan expires in 365 days
                    'etc' => 1,      // Example "etc" plan for 1 day (for testing purposes)
                ];

                // Get the user's subscription plan type
                $planType = $user->plan_type;

                // Ensure the plan type exists in the predefined plans
                if (array_key_exists($planType, $plans)) {
                    // Calculate the subscription expiry date based on the plan
                    $subscriptionExpiryDate = $startDate->copy()->addDays($plans[$planType]);

                    // Check if the subscription is expired
                    $isSubscriptionExpired = $subscriptionExpiryDate->isPast();

                    // Format the subscription expiry date for display
                    $subscriptionExpiryDateFormatted = $subscriptionExpiryDate->format('Y-m-d H:i:s');
                }
            }
        }

        // Return the subscription index view with relevant data
        return view('subscription.index', [
            'isSubscriptionExpired' => $isSubscriptionExpired,
            'planType' => $planType,
            'subscriptionExpiryDate' => $subscriptionExpiryDateFormatted,
        ]);
    }

    /**
     * Show the subscription creation form.
     */
    public function create()
    {
        return view('subscription.create');
    }

    /**
     * Handle the subscription creation form submission and store the subscription.
     */
    public function store(Request $request)
    {
        // Validate the incoming form data
        $request->validate([
            'plan_type' => 'required|string|in:trial,basic,premium', // Validate the plan type
            'payment_method' => 'required|string|in:credit_card,paypal,bank_transfer', // Validate the payment method
            'coupon_code' => 'nullable|string|max:50', // Optional coupon code
        ]);

        // Get the authenticated user
        $user = Auth::user();

        // Store the subscription details for the user
        $user->plan_type = $request->plan_type;  // Set the selected plan type
        $user->start_date = now();  // Set the current date as the start date
        $user->save();

        // Redirect the user to the subscription details page with a success message
        return redirect()->route('subscription.index')->with('success', 'Subscription successfully created!');
    }
}
