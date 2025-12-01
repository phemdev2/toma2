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

    // Get today's cash out transactions sorted by created_at in descending order
    $transactions = CashOut::where('user_id', $user->id)
                            ->whereDate('created_at', today())
                            ->orderBy('created_at', 'desc')
                            ->paginate(10);

    // Calculate available cash and totals for today
    $availableCash = $this->calculateAvailableCash($user);
    $totalCashOutToday = $this->calculateTotalCashOut($user);
    $totalChargesToday = $this->calculateTotalCharges($user);
    $totalWithdrawn = $totalCashOutToday; // This may need to be clarified if different from total cash out

    // Calculate POS balance and total balance
    $maxWithdrawal = $user->getMaxWithdrawal();
    $posBalance = $user->getPosBalanceForToday();
    $totalBalance = $maxWithdrawal + $totalWithdrawn; // New total balance

    return view('cashout.index', compact('transactions', 'availableCash', 'totalCashOutToday', 'totalChargesToday', 'totalWithdrawn', 'totalBalance'));
}


    
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'charges' => 'nullable|numeric|min:0',
            'payment_method' => 'required|string',
        ]);
    
        $user = auth()->user();
        
        // Check the withdrawal amount against max allowed
        $maxWithdrawal = $user->getMaxWithdrawal();
        if ($request->amount > $maxWithdrawal) {
            return redirect()->back()->withErrors(['amount' => 'Amount exceeds maximum withdrawal limit.']);
        }
    
        // Create the CashOut record
        $cashOut = new CashOut();
        $cashOut->user_id = $user->id;
        $cashOut->amount = $request->amount;
        $cashOut->charges = $request->charges ?? 0;
        $cashOut->payment_method = $request->payment_method;
        
        $cashOut->save();
    
        // Update the POS balance if the payment method is 'pos'
        if ($request->payment_method === 'pos') {
            // Increase the POS balance by the withdrawal amount
            $user->incrementPosBalance($request->amount);
        }
    
        return redirect()->route('cashout.index')->with('success', 'Withdrawal processed successfully!');
    }
    
    private function calculateAvailableCash($user)
    {
        return Order::where('user_id', $user->id)
                    ->whereDate('order_date', today())
                    ->where('payment_method', 'cash')
                    ->sum('amount');
    }

    private function calculateTotalCashOut($user)
    {
        return CashOut::where('user_id', $user->id)
                      ->whereDate('created_at', today())
                      ->sum('amount');
    }

    private function calculateTotalCharges($user)
    {
        return CashOut::where('user_id', $user->id)
                      ->whereDate('created_at', today())
                      ->sum('charges');
    }
}
