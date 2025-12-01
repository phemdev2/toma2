<?php
namespace App\Http\Controllers;

use App\Models\Store; 
use App\Models\Product; 
use App\Models\StoreInventory; 
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    // Display the list of stores and inventory
    public function index()
    {
        $stores = Store::all(); // Fetch all stores, or use any specific query based on your needs.
        $inventories = StoreInventory::with(['store', 'product'])->get();
    
        return view('purchases.index', compact('stores', 'inventories'));
    }
    

    // Show form for creating a new purchase (adding multiple items)
    public function create()
    {
        $stores = Store::all(); // Get all stores
        $products = Product::all(); // Get all products
        return view('purchases.create', compact('stores', 'products'));
    }

    // Store multiple purchases (products and quantities)
    public function store(Request $request)
    {
        // Validate that the form data is an array of products with name and quantity
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',  // Ensure store exists
            'products' => 'required|array',  // Products should be an array
            'products.*.product_id' => 'required|exists:products,id',  // Each product ID must exist
            'products.*.quantity' => 'required|integer|min:1',  // Quantity must be a positive integer
        ]);

        // Loop through each product and quantity pair
        foreach ($request->products as $productData) {
            // Fetch the product based on product ID
            $product = Product::find($productData['product_id']);
            if ($product) {
                // Check if inventory already exists for the given store and product
                $inventory = StoreInventory::where('store_id', $request->store_id)
                    ->where('product_id', $product->id)
                    ->first();

                // If inventory exists, update the quantity
                if ($inventory) {
                    $inventory->quantity += $productData['quantity'];
                    $inventory->save();
                } else {
                    // If inventory doesn't exist, create a new record
                    StoreInventory::create([
                        'store_id' => $request->store_id,
                        'product_id' => $product->id,
                        'quantity' => $productData['quantity'],
                    ]);
                }
            } else {
                return redirect()->route('purchases.create')->withErrors(['products' => 'Invalid product data.']);
            }
        }

        return redirect()->route('purchases.create')->with('success', 'Products successfully added to inventory.');
    }
}
