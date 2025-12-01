<?php
namespace App\Http\Controllers;

use App\Models\PurchaseHistory;
use Illuminate\Http\Request;

class PurchaseHistoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        
        $purchaseHistories = PurchaseHistory::with('store', 'product', 'user')
            ->when($search, function ($query) use ($search) {
                $query->whereHas('store', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10); // Adjust the number as needed
        
        return view('purchase_histories.index', compact('purchaseHistories'));
    }
}
