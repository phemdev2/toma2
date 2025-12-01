<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{
    public function index(Request $request)
{
    // Get filters from request
    $filter = $request->input('filter', 'daily'); // Default to daily
    $month = $request->input('month', date('Y-m'));

    // Initialize query
    $query = Transaction::query();

    // Filter transactions based on the selected filter
    if ($filter === 'monthly') {
        $query->whereMonth('created_at', Carbon::parse($month)->month);
    } else {
        $query->whereDate('created_at', Carbon::today());
    }
    
    if ($request->wantsJson()) {
        return response()->json([
            'transactions' => $transactions,
        ]);
    }
    
    // Order transactions by most recent first
    $query->orderBy('created_at', 'desc');

    // Fetch all transactions without pagination
    $transactions = $query->get();

    // Calculate balances
    $cashBalance = Transaction::where('type', 'cash')->sum('total');
    $posBalance = Transaction::where('type', 'pos')->sum('total');
    $totalBalance = $cashBalance + $posBalance;

    // Today's total
    $todaysTotal = Transaction::whereDate('created_at', Carbon::today())->sum('total');

    // Monthly balances
    $monthlyCashBalance = Transaction::where('type', 'cash')
        ->whereMonth('created_at', Carbon::parse($month)->month)
        ->sum('total');

    $monthlyPosBalance = Transaction::where('type', 'pos')
        ->whereMonth('created_at', Carbon::parse($month)->month)
        ->sum('total');

    return view('transactions.index', compact(
        'transactions',
        'cashBalance',
        'posBalance',
        'totalBalance',
        'todaysTotal',
        'monthlyCashBalance',
        'monthlyPosBalance'
    ));
}


    public function show($id)
    {
        // Fetch the transaction with its items
        $transaction = Transaction::with('items')->findOrFail($id);
        return view('transactions.show', compact('transaction'));
    }
}
