<?php

namespace App\Http\Controllers;

use App\Models\CashOut;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CashOutController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Calculate available cash and POS totals
        $availableCash = $this->calculateAvailableCash($user);
        $availablePos = $this->calculateAvailablePos($user);
        
        // Calculate total charges for today
        $totalCharges = CashOut::where('user_id', $user->id)
                                ->whereDate('created_at', today())
                                ->sum('charges');

        // Calculate total transactions for today
        $totalTransactions = CashOut::where('user_id', $user->id)
                                     ->whereDate('created_at', today())
                                     ->count();

        // Pass the calculated values to the view
        return view('cashout.index', compact('availableCash', 'availablePos', 'totalCharges', 'totalTransactions'));
    }

    public function store(Request $request)
    {
        // Validate and process the cash out request
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . auth()->user()->getCashBalanceForToday(),
            'charges' => 'required|numeric|min:0',
        ]);

        // Process the cash out logic here
        CashOut::create([
            'user_id' => auth()->id(),
            'amount' => $request->amount,
            'charges' => $request->charges,
            'status' => 'pending', // Set status as needed
        ]);

        return redirect()->route('cashout.index')->with('success', 'Cash out successful!');
    }

    private function calculateAvailableCash($user)
    {
        return Order::where('user_id', $user->id)
                    ->whereDate('order_date', today())
                    ->where('payment_method', 'cash')
                    ->sum('amount');
    }

    private function calculateAvailablePos($user)
    {
        return Order::where('user_id', $user->id)
                    ->where('payment_method', 'pos')
                    ->whereDate('order_date', today())
                    ->sum('amount');
    }
}
