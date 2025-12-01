<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Product;
use App\Models\StoreInventory;
use App\Models\PurchaseHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            ->paginate(10); // Adjust the number as needed

        return view('store_inventories.index', compact('inventories'));
    }

    public function create()
    {
        $stores = Store::all(); // Retrieve all stores
        $products = Product::all(); // Retrieve all products
        $purchaseHistories = PurchaseHistory::with('store', 'product', 'user') // Ensure relationships are eager-loaded
            ->orderBy('created_at', 'desc') // Order by date, most recent first
            ->take(10) // Limit the number of records to display, adjust as needed
            ->get();

        return view('store_inventories.create', compact('stores', 'products', 'purchaseHistories'));
    }

    public function showPurchaseForm($inventoryId)
    {
        $inventory = StoreInventory::findOrFail($inventoryId);
        $stores = Store::all();
        $products = Product::all();

        return view('store_inventories.purchase', compact('inventory', 'stores', 'products'));
    }
    public function show($id)
    {
        $product = Product::with(['variants'])->findOrFail($id);
    
        // Get inventories along with the user who added them
        $inventories = DB::table('store_inventories AS si')
            ->join('users AS u', 'si.user_id', '=', 'u.id')
            ->select('si.*', 'u.id as user_id', 'u.name as user_name')
            ->where('si.product_id', $product->id)
            ->get();
    
        $quantitiesByStore = $this->getQuantitiesByStore($product); // Existing method
        $totalQuantity = $this->calculateTotalQuantity($quantitiesByStore); // Existing method
    
        return view('products.show', compact('product', 'quantitiesByStore', 'totalQuantity', 'inventories'));
    }

   public function show($storeId)
{
    $store = Store::findOrFail($storeId); // Retrieve the store by its ID
    
    // Retrieve all inventories for the selected store with eager loading
    $inventories = $store->storeInventories()->with('product', 'user')->get();
    
    // Retrieve purchase histories for the store
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


    public function store(Request $request)
    {
        $this->validateInventory($request);

        try {
            // Create the store inventory
            StoreInventory::create([
                'store_id' => $request->store_id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'user_id' => Auth::id(), // Store the ID of the currently authenticated user
            ]);

            // Redirect to the product details page with a success message
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

        // Create a new purchase history record
        PurchaseHistory::create([
            'store_id' => $inventory->store_id,
            'product_id' => $inventory->product_id,
            'user_id' => Auth::id(), // Track the user who added the stock
            'quantity' => $request->purchase_quantity,
        ]);

        // Update the inventory quantity
        $inventory->quantity += $request->purchase_quantity;
        $inventory->save();

        return redirect()->route('store-inventories.index')->with('success', 'Quantity updated successfully!');
    }

    public function edit($id)
    {
        $inventory = StoreInventory::findOrFail($id);
        $stores = Store::all(); // Retrieve all stores
        $products = Product::all(); // Retrieve all products

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
