<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Subscription;

class SubscriptionStatus
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
        // Fetch the subscription for the logged-in user
        $isSubscriptionExpired = true;  // Default value in case there's no subscription

        if (auth()->check()) {
            $subscription = Subscription::where('user_id', auth()->id())->first();

            if ($subscription) {
                $planType = $subscription->plan_type;
                $trialStartDate = $subscription->trial_start_date;
                $trialEndDate = 'N/A';

                // Calculate the end date based on the plan type
                if ($planType == 'trial' && $trialStartDate) {
                    $trialEndDate = Carbon::parse($trialStartDate)->addDays(30)->toDateString();
                } elseif ($planType == 'basic' && $trialStartDate) {
                    $trialEndDate = Carbon::parse($trialStartDate)->addDays(90)->toDateString();
                } elseif ($planType == 'premium' && $trialStartDate) {
                    $trialEndDate = Carbon::parse($trialStartDate)->addYear()->toDateString();
                }

                // Check if the subscription has expired
                $isSubscriptionExpired = Carbon::parse($trialEndDate)->isPast();
            }
        }

        // Share this value with all views
        view()->share('isSubscriptionExpired', $isSubscriptionExpired);

        return $next($request);
    }
}
