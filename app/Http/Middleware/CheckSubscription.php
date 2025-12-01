<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Subscription;
use Carbon\Carbon;

class CheckSubscription
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if the user is authenticated
        if (auth()->check()) {
            $user = auth()->user();

            // Fetch the subscription for the logged-in user
            $subscription = Subscription::where('user_id', $user->id)->first();

            if ($subscription) {
                $planType = $subscription->plan_type;
                $trialStartDate = $subscription->trial_start_date;
                $trialEndDate = 'N/A'; // Default if no trial end date
                $subscriptionStatus = 'No Active Subscription';
                $isSubscriptionExpired = false;

                // Check if trialStartDate is available
                if ($trialStartDate) {
                    if ($planType == 'trial') {
                        // Trial plan, expires after 30 days
                        $trialEndDate = Carbon::parse($trialStartDate)->addDays(30);
                    } elseif ($planType == 'basic') {
                        // Basic plan, expires 90 days after the trial start
                        $trialEndDate = Carbon::parse($trialStartDate)->addDays(90);
                    } elseif ($planType == 'premium') {
                        // Premium plan, expires 365 days after the trial start
                        $trialEndDate = Carbon::parse($trialStartDate)->addYear();
                    }

                    // Format the expiration date
                    $trialEndDateFormatted = $trialEndDate->format('F d, Y');

                    // Set the subscription status message
                    $subscriptionStatus = "{$planType} Plan - Active until: {$trialEndDateFormatted}";

                    // Check if the subscription has expired
                    $isSubscriptionExpired = Carbon::parse($trialEndDate)->isPast();
                } else {
                    // If no trial start date, assume the subscription is expired
                    $isSubscriptionExpired = true;
                }
            } else {
                // No subscription found, mark as expired
                $isSubscriptionExpired = true;
                $subscriptionStatus = 'No Active Subscription';
            }

            // Share subscription data globally using the view composer
            view()->share([
                'isSubscriptionExpired' => $isSubscriptionExpired,
                'subscriptionStatus' => $subscriptionStatus,
                'trialEndDate' => $trialEndDateFormatted ?? 'N/A',  // Make sure to include the formatted date
            ]);
        }

        return $next($request);
    }
}
