<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreInventory;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function processCheckout(Request $request)
{
    $validatedData = $request->validate([
        'cart' => 'required|array',
        'paymentMethod' => 'required|string|in:cash,pos,bank',
        'store_id' => 'required|integer'
    ]);

    $cartItems = $validatedData['cart'];
    $paymentMethod = $validatedData['paymentMethod'];
    $storeId = $validatedData['store_id'];

    if (empty($cartItems)) {
        return response()->json(['message' => 'Cart is empty.'], 400);
    }

    DB::beginTransaction();

    try {
        $order = Order::create([
            'payment_method' => $paymentMethod,
            'order_date' => now(),
            'store_id' => $storeId
        ]);

        foreach ($cartItems as $item) {
            // Check if the variant_id is present
            if (isset($item['variant_id'])) {
                $variant = ProductVariant::find($item['variant_id']);

                if (!$variant) {
                    throw new \Exception('Product variant not found: ' . $item['variant_id']);
                }

                $productId = $variant->product_id;
                $unitQty = $variant->unit_qty;
                $totalUnitsToReduce = $item['quantity'] * $unitQty;
            } else {
                // Handle case where no variant is specified
                $productId = $item['product_id'];
                $product = Product::find($productId);

                if (!$product) {
                    throw new \Exception('Product not found: ' . $productId);
                }

                $unitQty = 1; // Default unit quantity if no variant
                $totalUnitsToReduce = $item['quantity'] * $unitQty;
            }

            $inventory = StoreInventory::where('store_id', $storeId)
                                       ->where('product_id', $productId)
                                       ->first();

            if (!$inventory || $inventory->quantity < $totalUnitsToReduce) {
                throw new \Exception('Not enough stock for product: ' . $productId);
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'variant_id' => $item['variant_id'] ?? null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                
            ]);

            $inventory->quantity -= $totalUnitsToReduce;
            $inventory->save();
        }

        DB::commit();
        return response()->json([
            'message' => 'Checkout successful',
            'order_id' => $order->id
        ], 200);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json(['message' => 'Error during checkout: ' . $e->getMessage()], 500);
    }
}


    public function clearCart(Request $request)
    {
        $request->session()->forget('cart');
        return redirect()->back()->with('success', 'Cart cleared successfully.');
    }

    public function showReceipt($id)
    {
        $order = Order::with('items.product', 'items.variant')->findOrFail($id);

        return view('receipt', [
            'order' => $order,
            'cartItems' => $order->items
        ]);
    }
}
