<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\StoreInventory;
use App\Models\PurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StoreInventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $inventories = StoreInventory::with('store', 'product')
            ->when($search, function ($query) use ($search) {
                return $query->whereHas('store', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhereHas('product', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
            })
            ->paginate(10);

        return view('store_inventories.index', compact('inventories'));
    }

    public function create()
    {
        $stores = Store::all();
        $products = Product::all();
        $purchaseHistories = PurchaseHistory::with('store', 'product', 'user')
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return view('store_inventories.create', compact('stores', 'products', 'purchaseHistories'));
    }

    public function showInventory($storeId)
    {
        $store = Store::findOrFail($storeId);
        $inventories = $store->storeInventories()->with('product', 'user')->get();
        $purchaseHistories = PurchaseHistory::where('store_id', $storeId)->with('product', 'user')->get();

        // Aggregate quantities for products in the selected store
        $quantitiesByProduct = $inventories->groupBy('product_id')->map(function ($items) {
            return $items->sum('quantity');
        });

        // Get all products and their quantities across all stores
        $allStoreInventories = StoreInventory::with('store')->get();
        $quantitiesAcrossStores = $allStoreInventories->groupBy('product_id')->map(function ($items) {
            return $items->map(function ($inventory) {
                return [
                    'store_name' => $inventory->store->name,
                    'quantity' => $inventory->quantity,
                ];
            });
        });

        return view('store_inventories.show', compact('store', 'inventories', 'purchaseHistories', 'quantitiesByProduct', 'quantitiesAcrossStores'));
    }

    public function showProduct($productId)
{
    $product = Product::with(['variants'])->findOrFail($productId);

    // Fetch all stores and their store inventories for the given product
    $stores = Store::with(['storeInventories' => function($query) use ($productId) {
        $query->where('product_id', $productId)
              ->orderBy('id', 'desc');  // Sort by id in descending order
    }])->get();

    // Group inventories by batch number and expiry date
    $inventoriesByBatch = [];
    foreach ($stores as $store) {
        foreach ($store->storeInventories as $inventory) {
            $batchKey = $inventory->batch_number . '|' . $inventory->expiry_date;
            if (!isset($inventoriesByBatch[$batchKey])) {
                $inventoriesByBatch[$batchKey] = [
                    'batch_number' => $inventory->batch_number,
                    'expiry_date' => $inventory->expiry_date,
                    'total_quantity' => 0,
                    'stores' => []
                ];
            }
            $inventoriesByBatch[$batchKey]['total_quantity'] += $inventory->quantity;
            $inventoriesByBatch[$batchKey]['stores'][] = [
                'store_name' => $store->name,
                'quantity' => $inventory->quantity,
                'created_at' => $inventory->created_at,
                'updated_at' => $inventory->updated_at,
                'user_id' => $inventory->user_id
            ];
        }
    }

    // Calculate total quantity for all batches combined
    $totalQuantity = array_sum(array_column($inventoriesByBatch, 'total_quantity'));

    // Pass the variables to the view
    return view('products.show', compact('product', 'inventoriesByBatch', 'totalQuantity', 'stores'));
}


    public function store(Request $request)
    {
        $this->validateInventory($request);
    
        try {
            // Create the store inventory record, including batch_number and expiry_date
            StoreInventory::create([
                'store_id' => $request->store_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity, // Accepts negative quantities
                'batch_number' => $request->batch_number,  // Save batch number
                'expiry_date' => $request->expiry_date,    // Save expiry date
                'user_id' => Auth::id(),
            ]);
    
            return redirect()->route('products.show', $request->product_id)->with('success', 'Inventory added successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Failed to add inventory: ' . $e->getMessage()]);
        }
    }
    

    public function getStock(Request $request)
    {
        $storeId = $request->input('store_id');
        $productId = $request->input('product_id');

        $quantity = StoreInventory::where('store_id', $storeId)
                                  ->where('product_id', $productId)
                                  ->sum('quantity');

        // Handle case where no stock is found
        if ($quantity === 0) {
            return response()->json(['quantity' => 0, 'message' => 'No stock available.']);
        }

        return response()->json(['quantity' => $quantity]);
    }

    private function validateInventory(Request $request)
    {
        return $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer', // Allow any integer (positive or negative)
        ]);
    }

    public function addPurchase(Request $request, $storeId)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric',  // Allow both positive and negative values
        ]);
    
        $store = Store::findOrFail($storeId);
        $inventory = StoreInventory::where('store_id', $store->id)
                                   ->where('product_id', $request->product_id)
                                   ->firstOrFail();
    
        // Update the inventory quantity (can be positive or negative)
        $inventory->quantity += $request->quantity; // Adding negative values will subtract
        $inventory->save();
    
        // Log the purchase or deduction in purchase history
        PurchaseHistory::create([
            'store_id' => $store->id,
            'product_id' => $request->product_id,
            'user_id' => Auth::id(),
            'quantity' => $request->quantity,
        ]);
    
        return redirect()->route('store-inventories.show', $storeId)->with('success', 'Inventory updated successfully!');
    }
    

    public function edit($id)
    {
        $inventory = StoreInventory::findOrFail($id);
        $stores = Store::all();
        $products = Product::all();

        return view('store_inventories.edit', compact('inventory', 'stores', 'products'));
    }

    public function searchProducts(Request $request)
    {
        $search = $request->input('search');

        $products = Product::where('name', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%")
                            ->limit(10) // Add a limit to reduce the number of results
                            ->get();

        return response()->json($products);
    }
}
