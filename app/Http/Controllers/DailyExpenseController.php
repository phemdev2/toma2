<?php
namespace App\Http\Controllers;

use App\Models\DailyExpense;

class DailyExpenseController extends Controller
{

    public function store(Request $request)
{
    $validated = $request->validate([
        'store_id' => 'required|exists:stores,id',
        'date' => 'required|date',
        'description' => 'required|string|max:255',
        'amount' => 'required|numeric|min:0',
    ]);

    DailyExpense::create([
        'user_id' => auth()->id(),
        'store_id' => $validated['store_id'],
        'date' => $validated['date'],
        'description' => $validated['description'],
        'amount' => $validated['amount'],
    ]);

    return redirect()->back()->with('success', 'Expense saved!');
}

    public function destroy($id)
    {
        $expense = DailyExpense::findOrFail($id);
        $expense->delete();

        return response()->json(['success' => true]);
    }
}
