<?php
namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function create()
    {
        $store = auth()->user()->store;
        return view('expenses.create', compact('store'));
    }

  public function store(Request $request)
{
    $request->validate([
        'store_id' => 'required|exists:stores,id',
        'date' => 'required|date',
        'description' => 'required|string',
        'amount' => 'required|numeric|min:0',
    ]);

    DailyExpense::create([
        'user_id' => auth()->id(),
        'store_id' => $request->store_id,
        'date' => $request->date,
        'description' => $request->description,
        'amount' => $request->amount,
    ]);

    return redirect()->back()->with('success', 'Expense added.');
}


    public function index(Request $request)
    {
        $user = auth()->user();

        // Optionally filter by date/store
        $query = Expense::where('user_id', $user->id);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        $expenses = $query->orderBy('date', 'desc')->paginate(20);

        // Also pass stores list if needed (for filtering dropdown)
        $stores = $user->stores;  // assuming user has many stores

        return view('expenses.index', compact('expenses', 'stores'));
    }

    public function edit(Expense $expense)
    {
        $this->authorize('update', $expense);  // policy to protect
        return view('expenses.edit', compact('expense'));
    }

   public function update(User $user, Expense $expense)
{
    return $user->id === $expense->user_id;
}

public function delete(User $user, Expense $expense)
{
    return $user->id === $expense->user_id;
}


    public function destroy(Expense $expense)
    {
        $this->authorize('delete', $expense);
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense deleted.');
    }

    public function report(Request $request)
    {
        $user = auth()->user();

        $query = Expense::where('user_id', $user->id);

        if ($request->filled('store_id')) {
            $query->where('store_id', $request->store_id);
        }
        if ($request->filled('from_date')) {
            $query->where('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->where('date', '<=', $request->to_date);
        }

        $expenses = $query->orderBy('date')->get();

        $total = $expenses->sum('amount');

        return view('expenses.report', compact('expenses', 'total'));
    }
}
