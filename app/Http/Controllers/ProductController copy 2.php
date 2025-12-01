<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StoreInventory;
use Illuminate\Http\Request;
use App\Models\Variant; // Add this line
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
            'expiry_date' => 'nullable|date', // Ensure this validation is present
            'description' => 'nullable|string',
            'unit_type' => 'nullable|array',
            'unit_qty' => 'nullable|array',
            'price' => 'nullable|array',
        ]);
    
        // Create the product with expiry_date
        $product = Product::create([
            'name' => $request->name,
            'barcode' => $request->barcode,
            'cost' => $request->cost,
            'sale' => $request->sale,
            'expiry_date' => $request->expiry_date, // Save expiry date from request
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
        ->paginate(1000); // Change this value if you want a different number of products per page
    
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

        // Aggregate quantities for each store
        $quantitiesByStore = $product->storeInventories->groupBy('store_id')->map(function ($items) {
            return [
                'store' => $items->first()->store,
                'totalQuantity' => $items->sum('quantity'),
                'lastUpdatedBy' => $items->first()->user,
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

    // Validate product data
    private function validateProduct(Request $request, Product $product = null)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'cost' => 'required|numeric',
            'sale' => 'nullable|numeric',
            'description' => 'nullable|string',
            'unit_type.*' => 'nullable|string|max:255',
            'unit_qty.*' => 'nullable|numeric',
            'price.*' => 'nullable|numeric',
        ]);
    }

    // Manage product variants
    private function manageProductVariants(Product $product, Request $request)
    {
        $existingVariantIds = $request->variant_id ?? [];

        // Delete variants that are not in the provided IDs
        ProductVariant::where('product_id', $product->id)
            ->whereNotIn('id', $existingVariantIds)
            ->delete();

        foreach ($request->unit_type as $index => $unitType) {
            $variantData = [
                'unit_type' => $unitType,
                'unit_qty' => $request->unit_qty[$index],
                'price' => $request->price[$index],
            ];

            $variantId = $request->variant_id[$index] ?? null;

            if ($variantId) {
                // Update existing variant
                ProductVariant::where('id', $variantId)->update($variantData);
            } else {
                // Create new variant
                ProductVariant::create(array_merge(['product_id' => $product->id], $variantData));
            }
        }
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
        fputcsv($handle, ['Name', 'Barcode', 'Cost', 'Sale', 'Description', 'Variants']);

        foreach ($products as $product) {
            $variants = $product->variants->map(function ($variant) {
                return "{$variant->unit_type} - {$variant->unit_qty} units - &#8358;{$variant->price}";
            })->implode('; ');

            fputcsv($handle, [
                $product->name,
                $product->barcode,
                $product->cost,
                $product->sale,
                $product->description,
                $variants,
            ]);
        }

        fclose($handle);
        exit; // Prevent further output
    }

    // Download products as PDF
    public function downloadPdf()
    {
        $products = Product::with('variants')->get();
        $pdf = \PDF::loadView('products.pdf', compact('products'));

        return $pdf->download('products.pdf');
    }

    // Get available quantities for a product
    public function getAvailableQuantities($productId)
    {
        // Fetch store inventory for the specified product
        $quantities = StoreInventory::where('product_id', $productId)
            ->with('store') // Ensure to eager load the store relationship
            ->get();

        // Format the result to include store names and their respective quantities
        $result = $quantities->map(function ($inventory) {
            return [
                'store_name' => $inventory->store->name, // Store name
                'quantity' => $inventory->quantity, // Quantity in stock
                'last_updated_by' => $inventory->user ? $inventory->user->name : 'N/A', // Last updated by user
            ];
        });

        return response()->json($result);
    }

    // Function to generate a unique barcode based on name and random number
    private function generateBarcode($productName)
    {
        // Remove spaces and special characters from the product name
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $productName);

        // Generate a random number
        $randomNumber = rand(1000, 9999); // Adjust the range as needed

        // Combine the cleaned name and random number to create the barcode
        return strtoupper($cleanName) . '-' . $randomNumber; // Convert to uppercase for consistency
    }


    public function getProductQuantities($id)
{
    $product = Product::with('stores')->find($id); // Assuming you have a relationship set up
    $quantities = [];

    if ($product) {
        foreach ($product->stores as $store) {
            $quantities[] = [
                'store_id' => $store->id,
                'store_name' => $store->name,
                'quantity' => $store->pivot->quantity // Adjust this based on how you store quantities
            ];
        }
    }

    return response()->json($quantities);
}
public function storeQuantities()
{
    return $this->hasMany(StoreQuantity::class); // Adjust according to your actual relationship
}

}
