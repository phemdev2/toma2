<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BaseController extends Controller
{
    /**
     * Get the subscription expiry status for the current user.
     *
     * @return bool
     */
    protected function getSubscriptionExpiryStatus()
    {
        $user = auth()->user();
        return $user->subscription_expiry_date < now();
    }
}
