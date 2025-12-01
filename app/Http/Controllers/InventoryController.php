<?php
// app/Http/Controllers/InventoryController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Store; // Ensure this line is present
use App\Models\StoreInventory;
class InventoryController extends Controller
{
   // app/Http/Controllers/InventoryController.php
   public function index()
   {
       $products = Product::all();
       return view('inventory.index', compact('products'));
   }

   public function update(Request $request, $id)
   {
       $product = Product::findOrFail($id);
       $product->update([
           'allow_overselling' => $request->has('allow_overselling')
       ]);

       return redirect()->back()->with('success', 'Product updated successfully.');
   }
public function showTopUpForm()
{
    $products = Product::all();
    $stores = Store::all(); // Ensure Store model exists
    return view('inventory.top-up', compact('products', 'stores'));
}


public function topUp(Request $request)
{
    // Validate incoming request
    $request->validate([
        'store_id' => 'required|exists:stores,id',
        'product_id' => 'required|exists:products,id',
        'quantity' => 'required|integer|min:1'
    ]);

    try {
        // Find or create the store inventory entry
        $inventory = StoreInventory::firstOrCreate(
            ['store_id' => $request->input('store_id'), 'product_id' => $request->input('product_id')],
            ['quantity' => 0] // Default quantity if not exists
        );

        // Increment the quantity
        $inventory->quantity += $request->input('quantity');
        $inventory->save();

        return redirect()->route('inventory.top-up.form')->with('success', 'Quantity updated successfully!');
    } catch (\Exception $e) {
        return redirect()->route('inventory.top-up.form')->withErrors(['error' => 'Failed to update quantity: ' . $e->getMessage()]);
    }
}

public function searchProducts(Request $request)
{
    $query = $request->input('query');
    $storeId = $request->input('store_id');

    // Adjust the query to fit your database schema and search logic
    $products = Product::where('name', 'like', "%{$query}%")
        ->orWhere('barcode', 'like', "%{$query}%")
        ->get();

    return response()->json(['products' => $products]);
}

}
