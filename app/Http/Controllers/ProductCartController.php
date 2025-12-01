<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCartController extends Controller
{
    // 🔎 Search product by name or barcode
    public function search(Request $request)
    {
        $searchTerm = $request->input('query', '');

        $products = Product::where('name', 'like', "%{$searchTerm}%")
            ->orWhere('barcode', 'like', "%{$searchTerm}%")
            ->limit(20)
            ->get();

        return response()->json($products);
    }

    // ➕ Add product to cart
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        try {
            DB::transaction(function () use ($request, $product) {
                $product->refresh();

                if ($product->quantity < $request->quantity) {
                    abort(422, 'Not enough stock available');
                }

                Cart::create([
                    'store_id'   => auth()->user()->store_id,
                    'user_id'    => auth()->id(),
                    'product_id' => $product->id,
                    'quantity'   => $request->quantity,
                ]);

                $product->decrement('quantity', $request->quantity);
            });

            return response()->json([
                'message' => 'Product added to cart successfully',
                'cart' => Cart::with('product')
                    ->where('user_id', auth()->id())
                    ->get()
            ]);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
