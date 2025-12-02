<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Safety Check: Ensure user has a store
        if (!$user->store_id) {
            return redirect()->back()->with('error', 'You are not assigned to a store.');
        }

        $storeId = $user->store_id;

        // 1. Fetch Products
        $products = Product::with(['variants', 'storeInventories' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])->get();

        // 2. Transform Data
        $mappedProducts = $products->map(function ($product) {
            return [
                'id' => $product->id,
                'name' => $product->name,
                'barcode' => $product->barcode,
                'price' => (float) $product->sale, 
                // Handle case where inventory might be missing
                'stock' => $product->storeInventories->sum('quantity') ?? 0,
                'variants' => $product->variants
            ];
        });

        // 3. PASS THE STORE VARIABLE HERE
        return view('pos.index', [
            'products' => $mappedProducts,
            'user' => $user,
            'store' => $user->store // <--- THIS LINE IS CRITICAL
        ]);
    }
}