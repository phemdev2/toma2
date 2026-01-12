<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant; // Updated to match standard naming convention
use App\Models\StoreInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB; // Added for Transaction

class ProductController extends Controller
{
    public function fetch()
    {
        // Fetch products from the database
        $products = Product::all();
        return response()->json(['products' => $products]);
    }

    // Show the form to create a new product
    public function create()
    {
        return view('products.create');
    }

    // Store a new product and its variants
    public function store(Request $request)
    {
        // Validate the request
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'cost' => 'required|numeric|min:0',
            'sale' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date',
            'description' => 'nullable|string',
            'unit_type' => 'nullable|array',
            'unit_qty' => 'nullable|array',
            'price' => 'nullable|array',
        ]);

        // Automatically generate a barcode if not provided
        $barcode = $request->barcode ?? $this->generateBarcode();

        // Create the product
        $product = Product::create([
            'name' => $request->name,
            'barcode' => $barcode,
            'cost' => $request->cost,
            'sale' => $request->sale,
            'expiry_date' => $request->expiry_date,
            'description' => $request->description,
        ]);

        // Handle product variants if provided
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

    // Helper function to generate a unique barcode
    private function generateBarcode()
    {
        return strtoupper(Str::random(3)) . rand(1000, 9999);
    }

    // Show a list of all products
    public function index(Request $request)
    {
        $search = $request->input('search');
        $user = auth()->user();
        $storeId = $user->store_id;

        // Fetch products with filtered inventory quantities for the user's store
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

    public function edit(Product $product)
    {
        $variants = $product->variants;
        return view('products.edit', compact('product', 'variants'));
    }

    // Show the product details
    public function show($productId) 
    {
        $product = Product::with('variants', 'storeInventories.store', 'storeInventories.user')->findOrFail($productId);
    
        $quantitiesByStore = $product->storeInventories->groupBy('store_id')->map(function ($items) {
            return [
                'store' => $items->first()->store,
                'totalQuantity' => $items->sum('quantity'),
                'lastUpdatedBy' => $items->first()->user,
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
    
        return view('products.show', compact('product', 'quantitiesByStore', 'totalQuantity'));
    }

    // Update an existing product and its variants
    public function update(Request $request, $id)
    {
        // 1. Validate inputs
        $request->validate([
            // General Info
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric',
            'sale' => 'nullable|numeric',
            
            // Variants Arrays
            'variant_id' => 'array', // Captured from hidden inputs
            'unit_type' => 'array',
            'unit_type.*' => 'required|string|max:255',
            'unit_qty' => 'array',
            'unit_qty.*' => 'required|integer',
            'price' => 'array',
            'price.*' => 'required|numeric',
            
            // Deleted Variants
            'deleted_variants' => 'array'
        ]);

        // Use a transaction to ensure data integrity
        DB::transaction(function () use ($request, $id) {
            $product = Product::findOrFail($id);

            // 2. Update the product details
            $product->update($request->only(['name', 'barcode', 'description', 'cost', 'sale']));

            // 3. Handle deleted variants (explicitly requested deletions)
            if ($request->has('deleted_variants')) {
                ProductVariant::destroy($request->deleted_variants);
            }

            // 4. Handle Variants: Update existing ones or Create new ones
            if ($request->has('unit_type')) {
                foreach ($request->unit_type as $key => $type) {
                    $variantId = $request->variant_id[$key] ?? null;
                    $qty       = $request->unit_qty[$key];
                    $price     = $request->price[$key];

                    if ($variantId) {
                        // Update existing variant
                        $variant = ProductVariant::find($variantId);
                        if ($variant) {
                            $variant->update([
                                'unit_type' => $type,
                                'unit_qty'  => $qty,
                                'price'     => $price,
                            ]);
                        }
                    } else {
                        // Create new variant
                        ProductVariant::create([
                            'product_id' => $product->id,
                            'unit_type'  => $type,
                            'unit_qty'   => $qty,
                            'price'      => $price,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Product updated successfully!');
    }

    // Delete a product and its variants
    public function destroy(Product $product)
    {
        // Check if the product has any stock in StoreInventory
        $totalQuantity = StoreInventory::where('product_id', $product->id)->sum('quantity');

        // Prevent deletion if there is stock available (quantity > 0)
        if ($totalQuantity > 0) {
            return redirect()->route('products.index')->with('error', 'Product cannot be deleted because it has stock available.');
        }

        // Proceed to delete the product and its variants
        $product->variants()->delete();
        $product->storeInventories()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Product deleted successfully!');
    }

    // Show a product with the cart contents and total price
    public function showProductWithCart(Product $product)
    {
        $product->load('variants');
        $cart = session()->get('cart', []);
        $total = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart));

        return view('products.show_with_cart', compact('product', 'cart', 'total'));
    }

    // Fetch all products for displaying in card view
    public function cards()
    {
        $products = Product::all();
        return view('products.cards', compact('products'));
    }

    // Download products as CSV
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
                    $product->expiry_date,
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

    // Download products as PDF
    public function downloadPdf()
    {
        $products = Product::with('variants')->get();
        $pdf = \PDF::loadView('products.pdf', compact('products'));

        return $pdf->download('products.pdf');
    }

    /**
     * Remove multiple products from storage.
     */
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
    /**
     * Fetch products updated after a specific timestamp for the POS
     */
    public function getPosUpdates(Request $request)
    {
        $lastSync = $request->input('last_sync');
        $user = auth()->user();
        $storeId = $user->store_id ?? 0;

        // 1. Find products updated recently OR having variants updated recently
        $products = Product::with(['variants', 'storeInventories' => function ($query) use ($storeId) {
                $query->where('store_id', $storeId);
            }])
            ->where(function($q) use ($lastSync) {
                // Product itself updated
                $q->where('updated_at', '>', $lastSync);
                
                // OR variants updated
                $q->orWhereHas('variants', function($subQ) use ($lastSync) {
                    $subQ->where('updated_at', '>', $lastSync);
                });
                
                // OR inventory changed
                $q->orWhereHas('storeInventories', function($subQ) use ($lastSync, $storeId) {
                    $subQ->where('store_id', $storeId)
                         ->where('updated_at', '>', $lastSync);
                });
            })
            ->get();

        if ($products->isEmpty()) {
            return response()->json(['products' => [], 'timestamp' => now()->toIso8601String()]);
        }

        // 2. Transform to POS format (Must match the Blade @php logic)
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