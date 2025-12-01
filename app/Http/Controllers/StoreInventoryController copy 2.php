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
        
        // Fetch all stores
        $stores = Store::with(['storeInventories' => function($query) use ($productId) {
            $query->where('product_id', $productId);
        }])->get();
        
        // Prepare inventory data for each store
        $quantitiesByStore = $stores->map(function ($store) {
            $totalQuantity = $store->storeInventories->sum('quantity');
            $lastUpdatedBy = $store->storeInventories->last() ? $store->storeInventories->last()->user : null;
    
            return [
                'store' => $store,
                'totalQuantity' => $totalQuantity,
                'lastUpdatedBy' => $lastUpdatedBy,
            ];
        });
    
        // Calculate total quantity across all stores
        $totalQuantity = $quantitiesByStore->sum('totalQuantity');
    
        return view('products.show', compact('product', 'quantitiesByStore', 'totalQuantity', 'stores'));
    }
    


    public function store(Request $request)
    {
        $this->validateInventory($request);

        try {
            StoreInventory::create([
                'store_id' => $request->store_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
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

        return response()->json(['quantity' => $quantity]);
    }

    private function validateInventory(Request $request)
    {
        return $request->validate([
            'store_id' => 'required|exists:stores,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);
    }

    public function addPurchase(Request $request, $inventoryId)
    {
        $request->validate([
            'purchase_quantity' => 'required|integer|min:1',
        ]);

        $inventory = StoreInventory::findOrFail($inventoryId);

        PurchaseHistory::create([
            'store_id' => $inventory->store_id,
            'product_id' => $inventory->product_id,
            'user_id' => Auth::id(),
            'quantity' => $request->purchase_quantity,
        ]);

        $inventory->quantity += $request->purchase_quantity;
        $inventory->save();

        return redirect()->route('store-inventories.index')->with('success', 'Quantity updated successfully!');
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
                            ->get();

        return response()->json($products);
    }
}
