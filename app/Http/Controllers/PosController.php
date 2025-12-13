<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <--- FIXED: Added missing import

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Safety Check: Ensure user has a store
        if (!$user->store_id) {
            // Check if request has a referrer, otherwise default to home or dashboard
            return redirect(route('dashboard'))->with('error', 'You are not assigned to a store.');
        }

        $storeId = $user->store_id;

        // 1. Fetch Products with Eager Loading (Solves the 30-second timeout)
        // We load 'variants' and only the 'storeInventories' for the current store
        $products = Product::with(['variants', 'storeInventories' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])
        ->latest() // Optional: Order by newest
        ->get();

        // 2. Return View
        // We pass the Eloquent collection directly. 
        // The View (pos.index) handles the conversion to JSON for the JS frontend.
        return view('pos.index', [
            'products' => $products,
            'user' => $user,
            'store' => $user->store,
            'storeId' => $storeId
        ]);
    }
}