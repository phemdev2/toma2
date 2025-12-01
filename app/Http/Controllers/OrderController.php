<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    /**
     * Display a listing of the orders.
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Determine the filters and sorting
        $paymentMethod = $request->input('payment_method');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $sortBy = $request->input('sort_by', 'desc'); // Default to descending if not provided

        // Build the query based on the filters
        $query = Order::query();

        // Apply payment method filter if provided
        if ($paymentMethod) {
            $query->where('payment_method', $paymentMethod);
        }

        // Apply date range filter if provided
        if ($startDate && $endDate) {
            $query->whereBetween('order_date', [$startDate, $endDate]);
        }

        // Apply sorting
        $query->orderBy('order_date', $sortBy);

        // Paginate the results and append query parameters for pagination links
        $orders = $query->paginate(100)->appends($request->query());

        return view('orders.index', compact('orders'));
    }

    /**
     * Display the specified order with its items.
     *
     * @param  int  $orderId
     * @return \Illuminate\View\View
     */
    public function show($orderId)
    {
        // Retrieve the order along with its items, products, variants, and related user and store
        $order = Order::with(['items.product', 'items.variant', 'user', 'store']) // Eager load user and store
            ->findOrFail($orderId);

        // Return the view with the order data
        return view('receipts.show', compact('order'));
    }
}
