<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Added for Transactions

class InventoryController extends Controller
{
    /**
     * Display a listing of the products with pagination.
     */
    public function index(Request $request)
    {
        // Optimization: Eager load relations and use pagination instead of all()
        $products = Product::with('storeInventories')
            ->orderBy('name')
            ->paginate(20);

        return view('inventory.index', compact('products'));
    }

    /**
     * Update product settings (e.g., overselling).
     */
    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);
        
        $product->update([
            'allow_overselling' => $request->boolean('allow_overselling') // Improved boolean handling
        ]);

        return redirect()->back()->with('success', 'Product settings updated.');
    }

    /**
     * Show the form for adding stock.
     */
    public function showTopUpForm()
    {
        // Optimization: Don't load products here. Let the search endpoint handle it via AJAX.
        // Just load stores for the dropdown.
        $stores = Store::select('id', 'name')->get(); 
        
        return view('inventory.top-up', compact('stores'));
    }

    /**
     * Handle the stock addition logic.
     */
    public function topUp(Request $request)
    {
        $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1'
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Find or Create the inventory record
                $inventory = StoreInventory::firstOrCreate(
                    [
                        'store_id' => $request->store_id,
                        'product_id' => $request->product_id
                    ],
                    ['quantity' => 0] // Default if new
                );

                // 2. Concurrency Safe Update
                // using increment() prevents race conditions if multiple users edit at once
                $inventory->increment('quantity', $request->quantity);
                
                // Optional: Log this movement to a 'StockHistory' table here for auditing
            });

            return redirect()->route('inventory.top-up.form')
                ->with('success', 'Stock updated successfully!');

        } catch (\Exception $e) {
            // Log the error internally for debugging
            \Log::error("Inventory TopUp Error: " . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Could not update stock. Please try again.']);
        }
    }

    /**
     * AJAX Search for products with context of specific store stock.
     */
    public function searchProducts(Request $request)
    {
        $query = $request->input('query');
        $storeId = $request->input('store_id');

        if (empty($query)) {
            return response()->json(['products' => []]);
        }

        $products = Product::query()
            ->select('id', 'name', 'barcode') // Select only what's needed
            ->where('name', 'like', "%{$query}%")
            ->orWhere('barcode', 'like', "%{$query}%")
            // Eager load only the specific store's inventory to show current stock in UI
            ->with(['storeInventories' => function($q) use ($storeId) {
                $q->where('store_id', $storeId);
            }])
            ->limit(10) // Limit results for speed
            ->get()
            ->map(function ($product) {
                // Format for frontend
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'barcode' => $product->barcode,
                    // Get current stock for the selected store safely
                    'current_stock' => $product->storeInventories->first()?->quantity ?? 0
                ];
            });

        return response()->json(['products' => $products]);
    }
}