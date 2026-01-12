<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant; 
use App\Models\Store;
use App\Models\StoreInventory;
use App\Models\Audit; // Ensure you have an Audit or StockMovement model
use App\Models\SaleItem; // Assuming you have a sales tracking model
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProductController extends Controller
{
    // ==========================================
    // EXISTING HELPER METHODS (Preserved)
    // ==========================================

    public function fetch()
    {
        $products = Product::all();
        return response()->json(['products' => $products]);
    }

    public function create()
    {
        return view('products.create');
    }

    private function generateBarcode()
    {
        return strtoupper(Str::random(3)) . rand(1000, 9999);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'cost' => 'required|numeric|min:0',
            'sale' => 'nullable|numeric|min:0',
            
            'description' => 'nullable|string',
            'unit_type' => 'nullable|array',
            'unit_qty' => 'nullable|array',
            'price' => 'nullable|array',
        ]);

        $barcode = $request->barcode ?? $this->generateBarcode();

        $product = Product::create([
            'name' => $request->name,
            'barcode' => $barcode,
            'cost' => $request->cost,
            'sale' => $request->sale,
           
            'description' => $request->description,
        ]);

        if ($request->has('unit_type') && count($request->unit_type) > 0) {
            foreach ($request->unit_type as $index => $unitType) {
                if (!empty($unitType) && isset($request->unit_qty[$index]) && !empty($request->unit_qty[$index])) {
                    $product->variants()->create([
                        'unit_type' => $unitType,
                        'unit_qty' => $request->unit_qty[$index] ?? 1,
                        'price' => $request->price[$index] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();
        $storeId = $user->store_id;

        $productsWithVariants = Product::with(['variants', 'storeInventories' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])
        ->when($search, function ($query) use ($search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('barcode', 'like', "%{$search}%");
        })
        ->paginate(10);

        return view('products.index', compact('productsWithVariants', 'search', 'storeId'));
    }

    public function edit($id)
    {
        // 1. Load Product with Relations (Variants & Inventory)
        $product = Product::with(['variants', 'storeInventories.store'])->findOrFail($id);
        
        // 2. Load Stores (FIXES THE ERROR)
        $stores = Store::all();

        // 3. Analytics Data (Required for the "Audit & Profits" tab)
        // --- Unit Cost ---
        $qtyInPack = $product->quantity_in_pack ?? 1;
        $unitCost = ($product->cost > 0 && $qtyInPack > 0) ? $product->cost / $qtyInPack : 0;

        // --- Sales & Profits ---
        $startOfMonth = \Carbon\Carbon::now()->startOfMonth();
        
        // Check if SaleItem/Audit models exist to prevent crashes if features aren't built yet
        $hasSalesModel = class_exists(\App\Models\SaleItem::class);
        $hasAuditModel = class_exists(\App\Models\Audit::class);

        // Sales Queries
        $salesQuery = $hasSalesModel ? \App\Models\SaleItem::where('product_id', $product->id) : null;
        
        $soldMonth = $salesQuery ? (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->sum('quantity') : 0;
        $soldAllTime = $salesQuery ? (clone $salesQuery)->sum('quantity') : 0;
        $totalRevenue = $salesQuery ? (clone $salesQuery)->sum('total_price') : 0;

        // "Since Last Restock" Logic
        $lastRestock = $hasAuditModel 
            ? \App\Models\Audit::where('product_id', $product->id)->where('quantity', '>', 0)->latest()->first() 
            : null;
        $lastStockDate = $lastRestock ? $lastRestock->created_at : null;
        
        $soldSinceRestock = ($salesQuery && $lastStockDate)
            ? (clone $salesQuery)->where('created_at', '>=', $lastStockDate)->sum('quantity') 
            : $soldAllTime;

        // Profit Logic
        $totalCOGS = $soldAllTime * $unitCost;
        $netProfit = $totalRevenue - $totalCOGS;
        $isLoss = $netProfit < 0;

        // Inventory Logic
        $analytics = $product->storeInventories->map(function($inv) use ($product, $unitCost) {
            return [
                'store_name' => $inv->store->name ?? 'Unknown',
                'current_stock' => $inv->quantity,
                'status_color' => $inv->quantity < 10 ? 'red' : 'emerald',
                'stock_value' => $inv->quantity * $unitCost,
                'projected_profit' => ($product->sale - $unitCost) * $inv->quantity
            ];
        });

        // Audit History
        $auditHistory = $hasAuditModel 
            ? \App\Models\Audit::with(['user', 'store'])
                ->where('product_id', $product->id)
                ->latest()
                ->take(50)
                ->get()
            : [];

        // Return the view (Assuming your dashboard blade file is named 'products.edit')
        // If your file is 'products/show.blade.php', change 'products.edit' to 'products.show'
        return view('products.edit', compact(
            'product', 
            'stores', 
            'soldMonth', 
            'soldAllTime', 
            'soldSinceRestock', 
            'lastStockDate',
            'totalRevenue',
            'netProfit',
            'isLoss',
            'analytics',
            'auditHistory'
        ));
    }

    // ==========================================
    // UPDATED DASHBOARD LOGIC (New View Support)
    // ==========================================

    /**
     * Display the sophisticated Product Dashboard.
     */
    public function show($productId) 
    {
        // 1. Load Product with Relations
        $product = Product::with(['variants', 'storeInventories.store', 'storeInventories.user'])->findOrFail($productId);
        $stores = Store::all();

        // ==========================================
        // 1. RESTORED LOGIC (Fixes $quantitiesByStore error)
        // ==========================================
        $quantitiesByStore = $product->storeInventories->groupBy('store_id')->map(function ($items) {
            return [
                'store' => $items->first()->store,
                'totalQuantity' => $items->sum('quantity'),
                // user relation might not exist on storeInventory depending on your model, using null coalescing
                'lastUpdatedBy' => $items->first()->user ?? null, 
                'batches' => $items->groupBy('batch_number')->map(function ($batchItems) {
                    return [
                        'batch_number' => $batchItems->first()->batch_number,
                        'totalQuantity' => $batchItems->sum('quantity'),
                        'expiry_date' => $batchItems->first()->expiry_date,
                    ];
                }),
            ];
        });
        
        $totalQuantity = $quantitiesByStore->sum('totalQuantity');

        // ==========================================
        // 2. NEW DASHBOARD ANALYTICS
        // ==========================================
        
        // Unit Cost Calculation
        $qtyInPack = $product->quantity_in_pack ?? 1;
        $unitCost = ($product->cost > 0 && $qtyInPack > 0) ? $product->cost / $qtyInPack : 0;

        // Date Logic
        $startOfMonth = Carbon::now()->startOfMonth();

        // Sales Data Checks (Graceful fallback if models don't exist yet)
        $hasSalesModel = class_exists(\App\Models\SaleItem::class);
        $hasAuditModel = class_exists(\App\Models\Audit::class);

        $salesQuery = $hasSalesModel ? \App\Models\SaleItem::where('product_id', $product->id) : null;
        
        $soldMonth = $salesQuery ? (clone $salesQuery)->where('created_at', '>=', $startOfMonth)->sum('quantity') : 0;
        $soldAllTime = $salesQuery ? (clone $salesQuery)->sum('quantity') : 0;
        $totalRevenue = $salesQuery ? (clone $salesQuery)->sum('total_price') : 0;

        // "Since Last Restock" Logic
        $lastRestock = $hasAuditModel 
            ? \App\Models\Audit::where('product_id', $product->id)->where('quantity', '>', 0)->latest()->first() 
            : null;
        $lastStockDate = $lastRestock ? $lastRestock->created_at : null;
        $soldSinceRestock = ($salesQuery && $lastStockDate)
            ? (clone $salesQuery)->where('created_at', '>=', $lastStockDate)->sum('quantity') 
            : $soldAllTime;

        // Profit Logic
        $totalCOGS = $soldAllTime * $unitCost;
        $netProfit = $totalRevenue - $totalCOGS;
        $isLoss = $netProfit < 0;

        // Inventory Valuation (New Analytics Array)
        $analytics = $product->storeInventories->map(function($inv) use ($product, $unitCost) {
            return [
                'store_name' => $inv->store->name ?? 'Unknown Store',
                'current_stock' => $inv->quantity,
                'status_color' => $inv->quantity < 10 ? 'red' : 'emerald',
                'stock_value' => $inv->quantity * $unitCost,
                'projected_profit' => ($product->sale - $unitCost) * $inv->quantity
            ];
        });

        // Audit History
        $auditHistory = $hasAuditModel 
            ? \App\Models\Audit::with(['user', 'store'])
                ->where('product_id', $product->id)
                ->latest()
                ->take(50)
                ->get()
            : [];

        return view('products.show', compact(
            'product', 
            'stores', 
            // Restored Variables
            'quantitiesByStore',
            'totalQuantity',
            // New Analytics Variables
            'soldMonth', 
            'soldAllTime', 
            'soldSinceRestock', 
            'lastStockDate',
            'totalRevenue',
            'netProfit',
            'isLoss',
            'analytics',
            'auditHistory'
        ));
    }

    /**
     * Updated Update Method to handle Stock Injection & Form Logic
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|max:255',
            'cost' => 'required|numeric',
            'sale' => 'nullable|numeric',
            'quantity_in_pack' => 'nullable|integer|min:1', // New field support
            
            // Inventory Injection (from the "Receive Stock" card)
            'inventory' => 'array',
            
            // Variant Arrays
            'variant_id' => 'array',
            'unit_type' => 'array',
            'unit_qty' => 'array',
            'price' => 'array',
            
            // Deleted Variants
            'deleted_variants' => 'array'
        ]);

        DB::transaction(function () use ($request, $id) {
            $product = Product::findOrFail($id);

            // 1. Update Basic Details
            $product->update($request->only([
                'name', 'barcode', 'description', 'cost', 'sale', 'quantity_in_pack'
            ]));

            // 2. Handle Quick Stock Injection (Audit & Inventory)
            if ($request->has('inventory')) {
                foreach ($request->input('inventory') as $storeId => $data) {
                    $qtyToAdd = intval($data['quantity'] ?? 0);
                    
                    if ($qtyToAdd > 0) {
                        // Update Inventory
                        $inventory = StoreInventory::firstOrCreate(
                            ['store_id' => $storeId, 'product_id' => $product->id],
                            ['quantity' => 0]
                        );
                        $inventory->increment('quantity', $qtyToAdd);

                        // Create Audit Log (If Audit model exists)
                        if (class_exists(Audit::class)) {
                            Audit::create([
                                'product_id' => $product->id,
                                'store_id' => $storeId,
                                'user_id' => auth()->id(),
                                'quantity' => $qtyToAdd,
                                'batch_number' => $data['batch_number'] ?? null,
                                
                                'action' => 'restock',
                                'notes' => 'Quick add from dashboard'
                            ]);
                        }
                    }
                }
            }

            // 3. Handle Deleted Variants
            if ($request->has('deleted_variants')) {
                ProductVariant::destroy($request->deleted_variants);
            }

            // 4. Handle Variant Updates/Creates
            if ($request->has('unit_type')) {
                foreach ($request->unit_type as $key => $type) {
                    // Check if corresponding qty/price exist to avoid index offset errors
                    if(!isset($request->unit_qty[$key]) || !isset($request->price[$key])) continue;

                    $variantId = $request->variant_id[$key] ?? null;
                    $qty       = $request->unit_qty[$key];
                    $price     = $request->price[$key];

                    $variantData = [
                        'unit_type' => $type,
                        'unit_qty'  => $qty,
                        'price'     => $price,
                        'product_id' => $product->id
                    ];

                    if ($variantId) {
                        $variant = ProductVariant::find($variantId);
                        if ($variant && $variant->product_id == $product->id) {
                            $variant->update($variantData);
                        }
                    } else {
                        ProductVariant::create($variantData);
                    }
                }
            }
        });

        return redirect()->route('products.show', $id)->with('success', 'Product updated successfully!');
    }

    /**
     * NEW: AJAX Method for the single row save button
     */
    public function updateSingleVariant(Request $request, $id)
    {
        $request->validate([
            'unit_type' => 'required|string',
            'unit_qty' => 'required|numeric',
            'price' => 'required|numeric',
        ]);

        $variant = ProductVariant::findOrFail($id);
        
        $variant->update([
            'unit_type' => $request->unit_type,
            'unit_qty' => $request->unit_qty,
            'price' => $request->price,
        ]);

        return response()->json(['success' => true, 'message' => 'Variant saved']);
    }

    // ==========================================
    // PRESERVED HELPER METHODS
    // ==========================================

    public function destroy(Product $product)
    {
        $totalQuantity = StoreInventory::where('product_id', $product->id)->sum('quantity');

        if ($totalQuantity > 0) {
            return redirect()->route('products.index')->with('error', 'Product cannot be deleted because it has stock available.');
        }

        $product->variants()->delete();
        $product->storeInventories()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    public function showProductWithCart(Product $product)
    {
        $product->load('variants');
        $cart = session()->get('cart', []);
        $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        return view('products.show_with_cart', compact('product', 'cart', 'total'));
    }

    public function cards()
    {
        $products = Product::all();
        return view('products.cards', compact('products'));
    }

    public function downloadCsv()
    {
        $products = Product::with('variants')->get();
        $csvFileName = 'products.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $csvFileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $handle = fopen('php://output', 'w');

        fputcsv($handle, ['Product Name', 'Barcode', 'Cost', 'Sale Price', 'Expiry Date', 'Description', 'Unit Type', 'Unit Quantity', 'Price']);

        foreach ($products as $product) {
            foreach ($product->variants as $variant) {
                fputcsv($handle, [
                    $product->name,
                    $product->barcode,
                    $product->cost,
                    $product->sale,
                    
                    $product->description,
                    $variant->unit_type,
                    $variant->unit_qty,
                    $variant->price,
                ]);
            }
        }

        fclose($handle);
        exit;
    }

    public function downloadPdf()
    {
        $products = Product::with('variants')->get();
        // Ensure you have a 'products.pdf' view or update this reference
        $pdf = \PDF::loadView('products.pdf', compact('products')); 
        return $pdf->download('products.pdf');
    }

    public function bulkDelete(Request $request)
    {
        $ids = json_decode($request->input('ids'), true);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'No products selected for deletion.');
        }

        $products = Product::whereIn('id', $ids)->get();
        
        $count = 0;
        foreach ($products as $product) {
            $product->delete();
            $count++;
        }

        return redirect()->back()->with('success', "Successfully deleted {$count} products.");
    }

    public function getPosUpdates(Request $request)
    {
        $lastSync = $request->input('last_sync');
        $user = auth()->user();
        $storeId = $user->store_id ?? 0;

        $products = Product::with(['variants', 'storeInventories' => function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            }])
            ->where(function($q) use ($lastSync) {
                $q->where('updated_at', '>', $lastSync);
                $q->orWhereHas('variants', function($subQ) use ($lastSync) {
                    $subQ->where('updated_at', '>', $lastSync);
                });
                $q->orWhereHas('storeInventories', function($subQ) use ($lastSync, $storeId) {
                    $subQ->where('store_id', $storeId)
                         ->where('updated_at', '>', $lastSync);
                });
            })
            ->get();

        if ($products->isEmpty()) {
            return response()->json(['products' => [], 'timestamp' => now()->toIso8601String()]);
        }

        $preparedProducts = $products->map(function ($product) use ($storeId) {
            $stock = $product->storeInventories->sum('quantity'); 
            
            $variants = collect($product->variants ?? [])->map(function ($v) {
                return [
                    'id' => $v->id,
                    'n'  => $v->variant_name ?? $v->unit_type ?? 'Option',
                    'q'  => $v->unit_qty ?? 1,
                    'p'  => (float) ($v->price ?? 0)
                ];
            })->values()->all();
        
            return [
                'id' => $product->id,
                'n'  => $product->name,
                'b'  => (string)($product->barcode ?? ''),
                'p'  => (float) ($product->sale ?? $product->price ?? 0),
                's'  => (int)$stock,
                'v'  => $variants
            ];
        });

        return response()->json([
            'products' => $preparedProducts,
            'timestamp' => now()->toIso8601String()
        ]);
    }
}