<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreInventory;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CheckoutController extends Controller
{
    public function processCheckout(Request $request)
    {
        // 1. Validation including offline context fields
        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'paymentMethod' => 'required|string|in:cash,pos,bank',
            'store_id' => 'required|integer',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
            // Context fields from JS
            'offline_user_id' => 'nullable|integer', 
            'offline_created_at' => 'nullable|date',
        ]);

        $allowOverselling = Setting::where('key', 'allow_overselling')->value('value') === 'true';

        try {
            // Check DB connection
            DB::connection()->getPdo(); 
            
            return $this->handleOnlineCheckout($validated, $allowOverselling);

        } catch (\PDOException $e) {
            // Fallback to Offline Mode
            $this->saveOfflineOrder($validated);
            
            return response()->json([
                'message' => 'System offline. Order saved locally.',
                'order_id' => null
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    private function handleOnlineCheckout($data, $allowOverselling)
    {
        return DB::transaction(function () use ($data, $allowOverselling) {
            
            // 1. Context Logic: Use offline data if available, else current session
            $userId = !empty($data['offline_user_id']) ? $data['offline_user_id'] : auth()->id();
            $orderDate = !empty($data['offline_created_at']) ? Carbon::parse($data['offline_created_at']) : now();

            $order = Order::create([
                'payment_method' => $data['paymentMethod'],
                'order_date' => $orderDate,
                'created_at' => $orderDate, // Explicitly set created_at for reporting
                'store_id' => $data['store_id'],
                'user_id' => $userId, 
                'discount' => $data['discount'] ?? 0,
                'amount' => 0 
            ]);

            $calculatedTotal = 0;
            $affectedProductIds = [];

            foreach ($data['cart'] as $item) {
                $calculatedTotal += $this->processCartItem($item, $order, $data['store_id'], $allowOverselling, $userId);
                
                // Track standard products to refresh stock
                if (!empty($item['product_id'])) {
                    $affectedProductIds[] = $item['product_id'];
                }
            }
            
            $order->update(['amount' => $calculatedTotal - ($data['discount'] ?? 0)]);

            // Clear session if online
            if(empty($data['offline_created_at'])) {
                session()->forget('cart');
            }

            // 2. Real-time Stock Calculation
            $updatedStock = [];
            if (!empty($affectedProductIds)) {
                $inventory = StoreInventory::where('store_id', $data['store_id'])
                    ->whereIn('product_id', array_unique($affectedProductIds))
                    ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
                    ->groupBy('product_id')
                    ->get();

                foreach($inventory as $inv) {
                    $updatedStock[] = [
                        'id' => $inv->product_id,
                        'new_stock' => (int)$inv->total_qty
                    ];
                }
            }

            return response()->json([
                'message' => 'Checkout successful',
                'order_id' => $order->id,
                'updated_stock' => $updatedStock // Send back to Frontend
            ], 200);
        });
    }

    private function processCartItem($item, $order, $storeId, $allowOverselling, $userId)
    {
        // Custom Item Handling
        if (empty($item['product_id'])) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => null,
                'custom_name' => $item['custom_name'] ?? 'Custom Item',
                'variant_id' => null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'user_id' => $userId,
                'shop_id' => $storeId,
            ]);
            return $item['price'] * $item['quantity'];
        }

        // Standard Product Handling
        $variant = isset($item['variant_id']) ? ProductVariant::find($item['variant_id']) : null;
        $productId = $variant ? $variant->product_id : $item['product_id'];
        $unitQty = $variant ? $variant->unit_qty : 1;
        $qtyRequired = $item['quantity'] * $unitQty;

        // Lock for update
        $inventory = StoreInventory::where('store_id', $storeId)
                                   ->where('product_id', $productId)
                                   ->lockForUpdate()
                                   ->first();

        if ($inventory) {
            if (!$allowOverselling && $inventory->quantity < $qtyRequired) {
                throw new \Exception("Insufficient stock for Product ID: $productId");
            }
            $inventory->decrement('quantity', $qtyRequired);
        } else {
            if (!$allowOverselling) {
                throw new \Exception("Stock record missing for Product ID: $productId");
            }
            StoreInventory::create([
                'store_id' => $storeId, 
                'product_id' => $productId, 
                'quantity' => -$qtyRequired
            ]);
        }

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $productId,
            'variant_id' => $variant ? $variant->id : null,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'user_id' => $userId,
            'shop_id' => $storeId,
        ]);

        return $item['price'] * $item['quantity'];
    }

    private function saveOfflineOrder($data)
    {
        $file = storage_path('app/offline_orders.json');
        
        $orderEntry = [
            'id' => (string) Str::uuid(),
            'data' => $data,
            'saved_at' => now()->toIso8601String(),
        ];

        $fp = fopen($file, 'c+');
        if (flock($fp, LOCK_EX)) {
            $currentData = '';
            while (!feof($fp)) $currentData .= fread($fp, 8192);
            $orders = json_decode($currentData, true) ?? [];
            $orders[] = $orderEntry;
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($orders, JSON_PRETTY_PRINT));
            flock($fp, LOCK_UN);
        }
        fclose($fp);
    }

    public function syncOfflineOrders()
    {
        try { DB::connection()->getPdo(); } catch (\Exception $e) { return response()->json(['message' => 'Offline'], 503); }

        $file = storage_path('app/offline_orders.json');
        if (!file_exists($file)) return response()->json(['message' => 'No orders']);

        $fp = fopen($file, 'c+');
        if (flock($fp, LOCK_EX)) {
            $content = '';
            while (!feof($fp)) $content .= fread($fp, 8192);
            $offlineOrders = json_decode($content, true) ?? [];
            
            if (empty($offlineOrders)) {
                flock($fp, LOCK_UN); fclose($fp); return response()->json(['message' => 'Empty']);
            }

            $failedOrders = [];
            $syncedCount = 0;
            $allowOverselling = Setting::where('key', 'allow_overselling')->value('value') === 'true';

            foreach ($offlineOrders as $orderWrapper) {
                try {
                    // Sync using stored data (retaining context)
                    $this->handleOnlineCheckout($orderWrapper['data'], $allowOverselling);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $failedOrders[] = $orderWrapper;
                }
            }

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode($failedOrders, JSON_PRETTY_PRINT));
            flock($fp, LOCK_UN);
            fclose($fp);

            return response()->json(['message' => "Synced $syncedCount", 'failed' => count($failedOrders)]);
        }
        fclose($fp);
    }
}