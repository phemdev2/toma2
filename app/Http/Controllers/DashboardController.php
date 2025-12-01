<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Order; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Subscription;

class DashboardController extends Controller
{
    
    public function index() 
    {
        // Fetch the subscription for the logged-in user
        $subscription = Subscription::where('user_id', auth()->id())->first();
        
        if ($subscription) {
            // Get the plan type and trial start date
            $planType = $subscription->plan_type;
            $trialStartDate = $subscription->trial_start_date;
    
            // Initialize trialEndDate to 'N/A' (default)
            $trialEndDate = 'N/A'; 
            
            // Calculate trial end date based on plan type
            if ($trialStartDate) {
                if ($planType == 'trial') {
                    // For trial plan, 30 days trial duration
                    $trialEndDate = Carbon::parse($trialStartDate)->addDays(30)->toDateString();
                } elseif ($planType == 'basic') {
                    // For basic plan, 90 days after trial start
                    $trialEndDate = Carbon::parse($trialStartDate)->addDays(90)->toDateString();
                } elseif ($planType == 'premium') {
                    // For premium plan, 365 days after trial start
                    $trialEndDate = Carbon::parse($trialStartDate)->addYear()->toDateString();
                }
    
                // Set trialStatus based on the plan type
                if ($planType == 'trial') {
                    $trialStatus = "Trial Plan - Active until: " . Carbon::parse($trialEndDate)->toFormattedDateString();
                } elseif ($planType == 'basic') {
                    $trialStatus = "Basic Plan - Active until: " . Carbon::parse($trialEndDate)->toFormattedDateString();
                } elseif ($planType == 'premium') {
                    $trialStatus = "Premium Plan - Active until: " . Carbon::parse($trialEndDate)->toFormattedDateString();
                }
    
                // Check if the subscription has expired
                $isSubscriptionExpired = Carbon::parse($trialEndDate)->isPast();
            } else {
                // In case trial start date is not available, assume subscription expired
                $trialStatus = "No Active Subscription";
                $trialEndDate = 'N/A';
                $isSubscriptionExpired = true; // No subscription found, it's considered expired
            }
        } else {
            // If no subscription is found for the user
            $trialStatus = "No Active Subscription";
            $trialEndDate = 'N/A';
            $isSubscriptionExpired = true; // No subscription, so it's considered expired
        }
    
        // Calculate today's totals for different payment methods
        $totalCash = $this->getOrderStats('cash', today());
        $totalPOS = $this->getOrderStats('pos', today());
        $totalBank = $this->getOrderStats('bank', today());
    
        // Calculate total amount for today
        $totalAmount = $this->getTodayTotalAmount();
    
        // Weekly and Monthly totals
        list($totalWeeklyOrders, $totalWeeklyAmount) = $this->getTotals('week');
        list($totalMonthlyOrders, $totalMonthlyAmount) = $this->getTotals('month');
    
        // Calculate store totals for today
        $storeTotals = $this->getStoreTotals(today());
    
        // Retrieve paginated order items for today
        $orderItems = Order::with(['user', 'store'])
                           ->whereDate('order_date', today())
                           ->orderBy('order_date', 'desc')
                           ->paginate(20);
    
        // Pass the data to the view
        return view('dashboard', compact(
            'totalCash', 'totalPOS', 'totalBank', 
            'totalAmount', 
            'totalWeeklyOrders', 'totalWeeklyAmount', 
            'totalMonthlyOrders', 'totalMonthlyAmount', 
            'orderItems', 
            'storeTotals', 'trialStatus', 'trialEndDate', 'isSubscriptionExpired'
        ));
    }
    // In your Controller
public function dashboard()
{
    $isSubscriptionExpired = Auth::user() ? Auth::user()->subscription_expired : true;

    return view('dashboard', compact('isSubscriptionExpired'));
}

    private function getOrderStats($method = null, $date = null)
    {
        $query = Order::query();

        if ($date) {
            $query->whereDate('order_date', $date);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        return [
            'count' => $query->count(),
            'amount' => $query->sum('amount'),
        ];
    }

    private function getTodayTotalAmount()
    {
        return Order::whereDate('order_date', today())->sum('amount');
    }

    private function getTotals($period)
    {
        $dateRange = $this->getDateRange($period);
        return [
            Order::whereBetween('order_date', $dateRange)->count(),
            Order::whereBetween('order_date', $dateRange)->sum('amount'),
        ];
    }

    private function getDateRange($period)
    {
        switch ($period) {
            case 'week':
                return [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()];
            case 'month':
                return [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()];
            default:
                return [today(), today()];
        }
    }

    private function getStoreTotals($date)
    {
        return Store::with(['orders' => function ($query) use ($date) {
            $query->whereDate('order_date', $date);
        }])->get()->map(function ($store) {
            return [
                'name' => $store->name,
                'total_orders' => $store->orders->count(),
                'total_amount' => $store->orders->sum('amount'),
            ];
        });
    }
    public function transactions(Request $request)
    {
        $date = $request->input('date', Carbon::yesterday()->toDateString());
        
        // Fetching the authenticated user's store_id
        $storeId = auth()->user()->store_id;
    
        $transactions = Order::with(['user', 'store'])
                             ->whereDate('order_date', $date)
                             ->where('store_id', $storeId) // Filter by store_id
                             ->orderBy('order_date', 'desc')
                             ->paginate(20);
    
        $totalPerStore = Store::with(['orders' => function($query) use ($date) {
            $query->whereDate('order_date', $date);
        }])
        ->where('id', $storeId) // Only get the current user's store
        ->get()
        ->map(function ($store) {
            return [
                'name' => $store->name,
                'total_orders' => $store->orders->count(),
                'total_amount' => $store->orders->sum('amount'),
            ];
        });
    
        return view('transactions', compact('transactions', 'date', 'totalPerStore'));
    }
    

    public function userTotals(Request $request)
{
    $date = $request->input('date', Carbon::today()->toDateString());

    $userTotals = Order::with(['user', 'store'])
    ->select('user_id', 'store_id', 
        DB::raw('count(*) as total_orders'), 
        DB::raw('sum(case when payment_method = "cash" then amount else 0 end) as totalCash'),
        DB::raw('sum(case when payment_method = "pos" then amount else 0 end) as totalPOS'),
        DB::raw('sum(case when payment_method = "bank" then amount else 0 end) as totalBank')
    )
    ->whereDate('order_date', $date)
    ->groupBy('user_id', 'store_id')
    ->get();


    $storeTotals = Store::with(['orders' => function ($query) use ($date) {
            $query->whereDate('order_date', $date);
        }])->get()->map(function ($store) {
            return [
                'name' => $store->name,
                'total_orders' => $store->orders->count(),
                'total_amount' => $store->orders->sum('amount'),
            ];
        });

    return view('user_totals', compact('userTotals', 'storeTotals', 'date'));
}

public function totalsPerUserAndStore(Request $request)
{
    $date = $request->input('date', Carbon::today()->toDateString());

    $userTotals = Order::with(['user', 'store'])
        ->select('user_id', 'store_id', 
            DB::raw('count(*) as total_orders'), 
            DB::raw('sum(case when payment_method = "cash" then amount else 0 end) as totalCash'),
            DB::raw('sum(case when payment_method = "pos" then amount else 0 end) as totalPOS'),
            DB::raw('sum(case when payment_method = "bank" then amount else 0 end) as totalBank')
        )
        ->whereDate('order_date', $date)
        ->groupBy('user_id', 'store_id')
        ->get();

    // Overall totals calculation
    $overallTotals = [
        'total_orders' => $userTotals->sum('total_orders'), // Ensure to sum here
        'totalCash' => $userTotals->sum('totalCash'),
        'totalPOS' => $userTotals->sum('totalPOS'),
        'totalBank' => $userTotals->sum('totalBank'),
    ];

    // Fetch store totals
    $storeTotals = $this->getStoreTotals($date);

    return view('totals_per_user_and_store', compact('userTotals', 'overallTotals', 'storeTotals', 'date'));
}

}
