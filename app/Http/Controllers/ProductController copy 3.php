<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreInventory;
use Illuminate\Http\Request;
use App\Models\Variant; // Add this line
use Illuminate\Support\Str; // Add this line to use Str::random()

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
            'barcode' => 'nullable|string|max:255', // Allow barcode to be nullable
            'cost' => 'required|numeric|min:0',
            'sale' => 'nullable|numeric|min:0',
            'expiry_date' => 'nullable|date', // Ensure this validation is present
            'description' => 'nullable|string',
            'unit_type' => 'nullable|array',
            'unit_qty' => 'nullable|array',
            'price' => 'nullable|array',
        ]);

        // Automatically generate a barcode if not provided
        $barcode = $request->barcode ?? $this->generateBarcode();

        // Create the product with expiry_date
        $product = Product::create([
            'name' => $request->name,
            'barcode' => $barcode, // Save generated barcode if not provided
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
        // Generate a random barcode (You can use a more sophisticated method if needed)
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
        ->paginate(10); // Change this value if you want a different number of products per page

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
        // Fetch the product with its variants and store inventories
        $product = Product::with('variants', 'storeInventories.store', 'storeInventories.user')->findOrFail($productId);
    
        // Aggregate quantities for each store and organize by batch number
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
    
        // Calculate total quantity across all stores
        $totalQuantity = $quantitiesByStore->sum('totalQuantity');
    
        return view('products.show', compact('product', 'quantitiesByStore', 'totalQuantity'));
    }
    

    // Update an existing product and its variants
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        // Validate your inputs
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'required|string|max:255',
            'description' => 'nullable|string',
            'cost' => 'required|numeric',
            'sale' => 'nullable|numeric',
            'unit_type.*' => 'required|string|max:255',
            'unit_qty.*' => 'required|integer',
            'price.*' => 'required|numeric',
        ]);

        // Update the product details
        $product->update($request->only(['name', 'barcode', 'description', 'cost', 'sale']));

        // Handle deleted variants
        if ($request->has('deleted_variants')) {
            $deletedVariantIds = $request->input('deleted_variants');
            Variant::whereIn('id', $deletedVariantIds)->delete();
        }

        // Clear existing variants before adding new ones
        $product->variants()->delete();

        // Handle variants update or creation
        $unitTypes = $request->input('unit_type', []); // Default to an empty array
        $unitQuantities = $request->input('unit_qty', []); // Default to an empty array
        $prices = $request->input('price', []); // Default to an empty array

        foreach ($unitTypes as $index => $unitType) {
            $product->variants()->create([
                'unit_type' => $unitType,
                'unit_qty' => $unitQuantities[$index] ?? 0, // Default to 0 if not set
                'price' => $prices[$index] ?? 0.00, // Default to 0.00 if not set
            ]);
        }

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

        // Set CSV headers
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $csvFileName . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $handle = fopen('php://output', 'w');

        // Add CSV column headers
        fputcsv($handle, ['Product Name', 'Barcode', 'Cost', 'Sale Price', 'Expiry Date', 'Description', 'Unit Type', 'Unit Quantity', 'Price']);

        // Add product data to CSV
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
        // 1. Decode the JSON array of IDs sent from the frontend
        $ids = json_decode($request->input('ids'), true);

        if (empty($ids) || !is_array($ids)) {
            return redirect()->back()->with('error', 'No products selected for deletion.');
        }

        // 2. Perform the delete
        // Using get() and loop ensures Model Events (like deleting images) are triggered
        $products = \App\Models\Product::whereIn('id', $ids)->get();
        
        $count = 0;
        foreach ($products as $product) {
            // Add any specific logic here (e.g., check if product has sales history)
            $product->delete();
            $count++;
        }

        return redirect()->back()->with('success', "Successfully deleted {$count} products.");
    }
}
