<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Order; 
use App\Models\Product; 
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index() 
    {
        $user = Auth::user();
        $storeId = $user->store_id; // Limits data if user is assigned to a specific store

        // ==========================================
        // 1. SUBSCRIPTION LOGIC
        // ==========================================
        $subscription = Subscription::where('user_id', $user->id)->first();
        
        $subscriptionType = 'none';
        $subscriptionExpiryDate = null;
        $isSubscriptionExpired = true;
        $trialStatus = "No Active Subscription";

        if ($subscription) {
            $subscriptionType = $subscription->plan_type;
            $trialStartDate = $subscription->trial_start_date;
    
            if ($trialStartDate) {
                $startDate = Carbon::parse($trialStartDate);
                
                // Determine duration
                if ($subscriptionType == 'trial') $endDate = $startDate->copy()->addDays(30);
                elseif ($subscriptionType == 'basic') $endDate = $startDate->copy()->addDays(90);
                elseif ($subscriptionType == 'premium') $endDate = $startDate->copy()->addYear();
                else $endDate = $startDate->copy()->addDays(30); 

                $subscriptionExpiryDate = $endDate->toDateTimeString(); // For JS
                $trialEndDate = $endDate->toFormattedDateString(); // For Text
                $isSubscriptionExpired = $endDate->isPast();
                $trialStatus = ucfirst($subscriptionType) . " Plan - Active until: " . $trialEndDate;
            }
        } else {
            $isSubscriptionExpired = $user->subscription_expired ?? true;
        }
    
        // ==========================================
        // 2. FINANCIAL STATS (Existing)
        // ==========================================
        $totalCash = $this->getOrderStats('cash', today(), $storeId);
        $totalPOS = $this->getOrderStats('pos', today(), $storeId);
        $totalBank = $this->getOrderStats('bank', today(), $storeId);
        $totalAmount = $this->getTodayTotalAmount($storeId);
    
        list($totalWeeklyOrders, $totalWeeklyAmount) = $this->getTotals('week', $storeId);
        list($totalMonthlyOrders, $totalMonthlyAmount) = $this->getTotals('month', $storeId);
    
        $storeTotals = $this->getStoreTotals(today());

        // New: Average Order Value (AOV)
        $totalOrdersCount = $totalCash['count'] + $totalPOS['count'] + $totalBank['count'];
        $averageOrderValue = $totalOrdersCount > 0 ? $totalAmount / $totalOrdersCount : 0;
    
        // ==========================================
        // 3. ANALYTICS (Charts & Lists)
        // ==========================================

        // A. Low Stock Alerts (Joined with Store Inventories)
        $lowStockQuery = DB::table('store_inventories')
            ->join('products', 'store_inventories.product_id', '=', 'products.id')
            ->select('products.name', DB::raw('SUM(store_inventories.quantity) as quantity'))
            ->groupBy('store_inventories.product_id', 'products.name');

        if ($storeId) {
            $lowStockQuery->where('store_inventories.store_id', $storeId);
        }

        $lowStockProducts = $lowStockQuery
            ->having('quantity', '<', 10)
            ->orderBy('quantity', 'asc')
            ->limit(5)
            ->get();

        // B. Top Selling Products
        $topProductsQuery = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_sold')
            ->limit(5);

        if ($storeId) {
            $topProductsQuery->where('orders.store_id', $storeId);
        }
        $topProducts = $topProductsQuery->get();

        // C. Sales Trend (Last 7 Days)
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('D'); 
            $dayQuery = Order::whereDate('order_date', $date);
            if ($storeId) $dayQuery->where('store_id', $storeId);
            $chartData[] = $dayQuery->sum('amount');
        }

        // D. Hourly Traffic (Peak Hours)
        $hourlyTraffic = array_fill(0, 24, 0); // 00 to 23
        $hourlyQuery = Order::select(DB::raw('HOUR(order_date) as hour'), DB::raw('COUNT(*) as count'))
            ->whereDate('order_date', today())
            ->groupBy('hour');
            
        if ($storeId) $hourlyQuery->where('store_id', $storeId);
        
        $hourlyResults = $hourlyQuery->get();
        foreach ($hourlyResults as $traffic) {
            $hourlyTraffic[$traffic->hour] = $traffic->count;
        }

        // E. Top Staff Leaderboard
        $staffQuery = Order::with('user')
            ->select('user_id', DB::raw('SUM(amount) as total_sales'), DB::raw('COUNT(*) as total_orders'))
            ->whereDate('order_date', today())
            ->groupBy('user_id')
            ->orderByDesc('total_sales')
            ->limit(4);

        if ($storeId) $staffQuery->where('store_id', $storeId);
        $topStaff = $staffQuery->get();

        // ==========================================
        // 4. RECENT TRANSACTIONS
        // ==========================================
        $orderItemsQuery = Order::with(['user', 'store'])
                           ->whereDate('order_date', today())
                           ->orderBy('order_date', 'desc');
        
        if ($storeId) $orderItemsQuery->where('store_id', $storeId);
        $orderItems = $orderItemsQuery->paginate(15);
    
        return view('dashboard', compact(
            'totalCash', 'totalPOS', 'totalBank', 'totalAmount', 
            'totalWeeklyOrders', 'totalWeeklyAmount', 
            'totalMonthlyOrders', 'totalMonthlyAmount', 
            'storeTotals', 'orderItems', 'averageOrderValue',
            'trialStatus', 'isSubscriptionExpired', 'subscriptionType', 'subscriptionExpiryDate',
            'lowStockProducts', 'topProducts', 'chartLabels', 'chartData',
            'hourlyTraffic', 'topStaff'
        ));
    }

    // Helper Methods
    private function getOrderStats($method = null, $date = null, $storeId = null)
    {
        $query = Order::query();
        if ($date) $query->whereDate('order_date', $date);
        if ($method) $query->where('payment_method', $method);
        if ($storeId) $query->where('store_id', $storeId);

        return ['count' => $query->count(), 'amount' => $query->sum('amount')];
    }

    private function getTodayTotalAmount($storeId = null)
    {
        $query = Order::whereDate('order_date', today());
        if ($storeId) $query->where('store_id', $storeId);
        return $query->sum('amount');
    }

    private function getTotals($period, $storeId = null)
    {
        $dateRange = $this->getDateRange($period);
        $query = Order::whereBetween('order_date', $dateRange);
        if ($storeId) $query->where('store_id', $storeId);
        
        return [(clone $query)->count(), (clone $query)->sum('amount')];
    }

    private function getDateRange($period)
    {
        return match ($period) {
            'week' => [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()],
            'month' => [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()],
            default => [today(), today()],
        };
    }

    private function getStoreTotals($date)
    {
        $userStoreId = Auth::user()->store_id;
        $query = Store::with(['orders' => fn($q) => $q->whereDate('order_date', $date)]);

        if ($userStoreId) $query->where('id', $userStoreId);

        return $query->get()->map(fn($store) => [
            'name' => $store->name,
            'total_orders' => $store->orders->count(),
            'total_amount' => $store->orders->sum('amount'),
        ]);
    }
}