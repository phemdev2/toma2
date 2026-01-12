<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\StoreInventory;
use App\Models\Variant;  // <--- ADD THIS (Crucial)
use App\Models\Store;
class PosController extends Controller
{
    /**
     * Load the main POS interface.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Safety Check: Ensure user has a store
        if (!$user || !$user->store_id) {
            return redirect(route('dashboard'))->with('error', 'You are not assigned to a store.');
        }

        $storeId = $user->store_id;

        // 1. Fetch Products with Eager Loading
        // We load 'variants' and only the 'storeInventories' for the current store
        $products = Product::with(['variants', 'storeInventories' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])
        ->latest()
        ->get();

        // 2. Return View
        return view('pos.index', [
            'products' => $products,
            'user' => $user,
            'store' => $user->store,
            'storeId' => $storeId
        ]);
    }

    /**
     * Polling Endpoint: Returns products updated since the last check.
     * Route Name: pos.updates
     */
   public function getUpdates(Request $request)
{
    $user = \Illuminate\Support\Facades\Auth::user();
    if (!$user || !$user->store_id) return response()->json(['error' => 'Unauthorized'], 401);

    $storeId = $user->store_id;
    $lastSync = $request->input('last_sync');

    // 1. Query: Get products updated OR products with variants updated
    $updatedProducts = \App\Models\Product::with(['variants', 'storeInventories' => function ($q) use ($storeId) {
            $q->where('store_id', $storeId);
        }])
        ->where(function($query) use ($lastSync) {
            $query->where('updated_at', '>', $lastSync)
                  ->orWhereHas('variants', function($q) use ($lastSync) {
                      $q->where('updated_at', '>', $lastSync);
                  });
        })
        ->get();

    // 2. Format Data (Same as before)
    $formatted = $updatedProducts->map(function ($product) use ($storeId) {
        $stock = $product->storeInventories->sum('quantity');
        
        // Map Variants safely
        $variants = collect($product->variants ?? [])->map(function ($v, $k) {
            $get = fn($key) => is_array($v) ? ($v[$key] ?? null) : ($v->$key ?? null);
            return [
                'id' => $get('id') ?? $k,
                'n'  => $get('variant_name') ?? $get('unit_type') ?? 'Option',
                'q'  => $get('unit_qty') ?? 1,
                'p'  => (float) ($get('price') ?? 0)
            ];
        })->values()->all();

        return [
            'id' => $product->id,
            'n'  => $product->name,
            'b'  => (string)($product->barcode ?? ''),
            'p'  => (float) ($product->sale ?? $product->price ?? 0),
            's'  => (int) $stock,
            'v'  => $variants
        ];
    });

    return response()->json([
        'products' => $formatted,
        'timestamp' => now()->toIso8601String(),
    ]);
}
public function enterPos(Request $request)
    {
        // 1. Validate the input
        $request->validate([
            'store_id' => 'required|exists:stores,id',
        ]);

        // 2. Update the user's active store
        $user = Auth::user();
        $user->store_id = $request->store_id; // Assuming you have a store_id column on users
        $user->save();

        // 3. Redirect to the main POS page
        // We pass the new store_id explicitly to match your existing URL structure
        return redirect()->route('pos.index', [
            'user_id'  => $user->id,
            'store_id' => $request->store_id
        ])->with('success', 'Resumed shift at ' . $user->store->name);
    }
}