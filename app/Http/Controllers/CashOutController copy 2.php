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

        // Get cash out transactions sorted by created_at in descending order
        $transactions = CashOut::where('user_id', $user->id)
                                ->orderBy('created_at', 'desc')
                                ->paginate(10);

        // Calculate available cash and total charges
        $availableCash = $this->calculateAvailableCash($user);
        $totalCharges = CashOut::where('user_id', $user->id)
                                ->whereDate('created_at', today())
                                ->sum('charges');

        return view('cashout.index', compact('transactions', 'availableCash', 'totalCharges'));
    }

    public function store(Request $request)
    {
        $maxWithdrawal = auth()->user()->getMaxWithdrawal();
    
        $request->validate([
            'amount' => 'required|numeric|min:1|max:' . $maxWithdrawal,
            'charges' => 'nullable|numeric|min:0', // Make charges nullable
            'payment_method' => 'required|in:withdraw',
        ]);
    
        // Set charges to 0 if not provided
        $charges = $request->charges ?? 0;
    
        // Process the cash out logic
        CashOut::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'charges' => $charges,
            'payment_method' => $request->payment_method,
            'status' => 'pending', // Initially set status to pending
        ]);
    
        return redirect()->route('cashout.index')->with('success', 'Cash out request submitted successfully!');
    }
    

    private function createCashOutRequest(Request $request)
    {
        CashOut::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'charges' => $request->charges,
            'payment_method' => $request->payment_method,
            'status' => 'pending',
        ]);
    }

    private function calculateAvailableCash($user)
    {
        return Order::where('user_id', $user->id)
                    ->whereDate('order_date', today())
                    ->where('payment_method', 'cash')
                    ->sum('amount');
    }
}
