<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Order; 
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller
{
    public function index()
    {
        // Calculate today's totals for payment methods
        $totalCash = $this->getOrderStatsByPaymentMethod('cash');
        $totalPOS = $this->getOrderStatsByPaymentMethod('pos');
        $totalBank = $this->getOrderStatsByPaymentMethod('bank');

        // Total amount for today
        $totalAmount = Order::whereDate('order_date', today())->sum('amount');

        // Weekly and Monthly totals
        $totalWeeklyOrders = Order::whereBetween('order_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        $totalWeeklyAmount = Order::whereBetween('order_date', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->sum('amount');

        $totalMonthlyOrders = Order::whereBetween('order_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->count();

        $totalMonthlyAmount = Order::whereBetween('order_date', [
            Carbon::now()->startOfMonth(),
            Carbon::now()->endOfMonth()
        ])->sum('amount');

// Calculate totals per store
$stores = Store::withCount(['orders' => function($query) {
    $query->whereDate('order_date', today());
}])->get();

$storeTotals = $stores->map(function ($store) {
    return [
        'name' => $store->name,
        'totalOrders' => $store->orders_count,
        'totalAmount' => $store->orders()->whereDate('order_date', today())->sum('amount'),
    ];
});



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
            'storeTotals'
        ));
    }

    private function getOrderStatsByPaymentMethod($method)
    {
        return [
            'count' => Order::whereDate('order_date', today())
                            ->where('payment_method', $method)
                            ->count(),
            'amount' => Order::whereDate('order_date', today())
                             ->where('payment_method', $method)
                             ->sum('amount'),
        ];
    }

    public function transactions(Request $request)
{
    // Determine the date to filter
    $date = $request->input('date', Carbon::yesterday()->toDateString());

    // Retrieve transactions for the specified date
    $transactions = Order::with(['user', 'store'])
                         ->whereDate('order_date', $date)
                         ->orderBy('order_date', 'desc')
                         ->paginate(20);

    // Calculate total amount per store
    $totalPerStore = Order::select('store_id', DB::raw('sum(amount) as total_amount'))
                          ->whereDate('order_date', $date)
                          ->groupBy('store_id')
                          ->with('store') // Eager load the store relation
                          ->get();

    // Pass the data to the view
    return view('transactions', compact('transactions', 'date', 'totalPerStore'));
}

public function userTotals(Request $request)
{
    // Determine the date to filter
    $date = $request->input('date', Carbon::today()->toDateString());

    // Retrieve total transactions per user for the specified date
    $userTotals = Order::select('user_id', 'store_id', DB::raw('count(*) as total_orders, sum(amount) as total_amount'))
                       ->whereDate('order_date', $date)
                       ->groupBy('user_id', 'store_id')
                       ->with(['user', 'store']) // Eager load user and store relations
                       ->get();

    // Calculate total amounts per store, including stores with no transactions
    $storeTotals = Store::with(['orders' => function($query) use ($date) {
        $query->whereDate('order_date', $date);
    }])->get()->map(function($store) use ($date) {
        $totalOrders = $store->orders->count();
        $totalAmount = $store->orders->sum('amount');

        return [
            'name' => $store->name,
            'total_orders' => $totalOrders,
            'total_amount' => $totalAmount,
        ];
    });

    return view('user_totals', compact('userTotals', 'storeTotals', 'date'));
}
}
