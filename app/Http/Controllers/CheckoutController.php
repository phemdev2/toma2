<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreInventory;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class CheckoutController extends Controller
{
    // Process checkout
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

        if (!$this->isOnline()) {
            $this->saveOfflineOrder($cartItems, $paymentMethod, $storeId);
            return response()->json([
                'message' => 'Checkout data saved locally. Will sync when back online.',
                'order_id' => null
            ], 200);
        }

        return $this->handleOnlineCheckout($cartItems, $paymentMethod, $storeId);
    }

    private function handleOnlineCheckout($cartItems, $paymentMethod, $storeId)
    {
        DB::beginTransaction();

        try {
            $order = Order::create([
                'payment_method' => $paymentMethod,
                'order_date' => now(),
                'store_id' => $storeId,
                'user_id' => auth()->id(), // Uncomment if you want to associate the order with the authenticated user
            ]);

            $totalAmount = 0;

            foreach ($cartItems as $item) {
                $totalAmount += $this->processCartItem($item, $order->id, $storeId);
            }

            // Update the order's amount
            $order->update(['amount' => $totalAmount]);

            DB::commit();
            $this->clearCart();

            return response()->json([
                'message' => 'Checkout successful',
                'order_id' => $order->id
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error during checkout: ' . $e->getMessage()], 500);
        }
    }

    private function processCartItem($item, $orderId, $storeId)
    {
        $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;

        if ($variant) {
            $productId = $variant->product_id;
            $unitQty = $variant->unit_qty;
        } else {
            $productId = $item['product_id'] ?? null;
            $unitQty = 1;
        }

        if (!$productId) {
            throw new \Exception('Product ID is required for checkout.');
        }

        $totalUnitsToReduce = $item['quantity'] * $unitQty;
        $allowOverselling = Setting::getValue('allow_overselling') === 'true';

        $inventory = StoreInventory::where('store_id', $storeId)
                                   ->where('product_id', $productId)
                                   ->first();

        if ($inventory) {
            if (!$allowOverselling && $inventory->quantity < $totalUnitsToReduce) {
                throw new \Exception('Not enough stock for product: ' . $productId);
            }
            $inventory->decrement('quantity', $totalUnitsToReduce);
        } else {
            StoreInventory::create(['store_id' => $storeId, 'product_id' => $productId, 'quantity' => -$totalUnitsToReduce]);
        }

        // Create the order item
        OrderItem::create([
            'order_id' => $orderId,
            'product_id' => $productId,
            'variant_id' => $variant ? $variant->id : null,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'user_id' => auth()->id(),
            'shop_id' => $storeId,
        ]);

        // Return the total amount for this item
        return $item['price'] * $item['quantity'];
    }

    private function isOnline()
    {
        return @fsockopen('www.google.com', 80) !== false;
    }

    private function saveOfflineOrder($cartItems, $paymentMethod, $storeId)
    {
        $offlineOrdersFile = storage_path('app/offline_orders.json');
        $offlineOrders = json_decode(file_get_contents($offlineOrdersFile), true) ?? [];
        $orderId = uniqid('offline_', true);

        $offlineOrders[] = [
            'order_id' => $orderId,
            'cart' => $cartItems,
            'paymentMethod' => $paymentMethod,
            'store_id' => $storeId,
            'created_at' => now(),
        ];

        file_put_contents($offlineOrdersFile, json_encode($offlineOrders));
    }

    public function syncOfflineOrders()
    {
        if ($this->isOnline()) {
            $offlineOrdersFile = storage_path('app/offline_orders.json');
            $offlineOrders = json_decode(file_get_contents($offlineOrdersFile), true) ?? [];
            
            foreach ($offlineOrders as $orderData) {
                $response = $this->processOfflineOrder($orderData);
                if ($response->status() === 200) {
                    $this->removeOfflineOrder($orderData['order_id']);
                } else {
                    $this->handleSyncError($response, $orderData);
                }
            }
        }
    }

    private function processOfflineOrder($orderData)
    {
        return Http::post('/api/process-offline-checkout', $orderData);
    }

    private function removeOfflineOrder($orderId)
    {
        $offlineOrdersFile = storage_path('app/offline_orders.json');
        $offlineOrders = json_decode(file_get_contents($offlineOrdersFile), true) ?? [];
        $offlineOrders = array_filter($offlineOrders, fn($order) => $order['order_id'] !== $orderId);
        file_put_contents($offlineOrdersFile, json_encode($offlineOrders));
    }

    private function handleSyncError($response, $orderData)
    {
        \Log::error('Failed to sync offline order:', [
            'order_data' => $orderData,
            'response' => $response->body()
        ]);
    }

    private function clearCart()
    {
        session()->forget('cart');
    }

    public function showReceipt($id)
    {
        $order = Order::with('items.product', 'items.variant')->findOrFail($id);
        return view('receipt', [
            'order' => $order,
            'cartItems' => $order->items
        ]);
    }

    public function showOfflineReceipt()
    {
        return view('offline_receipt');
    }
}
