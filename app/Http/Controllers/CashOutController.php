<?php

namespace App\Http\Controllers;

use Maatwebsite\Excel\Facades\Excel;
use App\Models\Order;
use App\Models\CashOut;
use Illuminate\Http\Request;
use App\Exports\CashOutExport;

class CashOutController extends Controller
{
    public function index(Request $request)
    {
        $query = CashOut::where('user_id', auth()->id())->with('store')->latest();

        // Filter by date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Search by amount
        if ($request->search) {
            $query->where('amount', 'like', '%' . $request->search . '%');
        }

        $transactions = $query->paginate(10);
        return view('cashout.index', compact('transactions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'charges' => 'nullable|numeric|min:0',
            // Remove or comment out the store_id validation
            'store_id' => 'nullable|exists:stores,id', 
        ]);
    
        $user = auth()->user();
        $availableCash = $user->getAvailableCash();
    
        if ($request->amount > $availableCash) {
            return redirect()->back()->withErrors(['amount' => 'Insufficient cash available.']);
        }
    
        // Create the CashOut record
        $cashOut = CashOut::create([
            'user_id' => $user->id,
            'amount' => $request->amount,
            'charges' => $request->charges ?? 0,
            'payment_method' => 'cash', // Default to 'withdraw'
            'store_id' => $request->store_id, // This can be null now if not provided
        ]);
    
        // Log the cash out transaction in the orders table
        Order::create([
            'user_id' => $user->id,
            'store_id' => $request->store_id, // This can be null if not provided
            'payment_method' => 'cash',
            'amount' => -$request->amount, // Deduct cash
            'order_date' => now(),
        ]);
    
        // Log the equivalent addition to POS
        Order::create([
            'user_id' => $user->id,
            'store_id' => $request->store_id, // This can be null if not provided
            'payment_method' => 'pos',
            'amount' => $request->amount, // Add to POS
            'order_date' => now(),
        ]);
    
        return redirect()->route('cashout.index')->with('success', 'Withdrawal processed successfully!');
    }
    

    public function export()
    {
        return Excel::download(new CashOutExport, 'cashouts.xlsx');
    }
}
