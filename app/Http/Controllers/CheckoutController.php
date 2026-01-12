<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

// Models
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\StoreInventory;
use App\Models\Product;
use App\Models\Variant; // Fixed: Matches your file structure
use App\Models\Store;
use App\Models\User;

// Mailables
use App\Mail\PosOrderReceipt; 

class CheckoutController extends Controller
{
    /**
     * Main Entry Point (Matched to Route)
     */
    public function processCheckout(Request $request)
    {
        // 1. Validation
        $validated = $request->validate([
            'cart' => 'required|array|min:1',
            'paymentMethod' => 'required|string|in:cash,pos,bank',
            'store_id' => 'required|integer|exists:stores,id',
            'discount' => 'nullable|numeric',
            'total' => 'required|numeric',
            // Context fields from JS (Offline Syncing)
            'offline_user_id' => 'nullable|integer', 
            'offline_created_at' => 'nullable',
        ]);

        // Default setting (Use config or env if Setting model missing)
        $allowOverselling = env('POS_ALLOW_OVERSELLING', true); 

        try {
            // Check DB connection before trying transaction
            DB::connection()->getPdo(); 
            
            return $this->handleOnlineCheckout($validated, $allowOverselling);

        } catch (\PDOException $e) {
            // Database is down -> Fallback to Offline Mode
            Log::warning("DB Connection failed, saving offline: " . $e->getMessage());
            $this->saveOfflineOrder($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'System offline. Order saved locally.',
                'order_id' => 'OFFLINE-' . time(),
                'offline_mode' => true
            ], 200);
        } catch (\Exception $e) {
            Log::error("Checkout Critical Error: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Handle the Standard Online Checkout
     */
    private function handleOnlineCheckout($data, $allowOverselling)
    {
        return DB::transaction(function () use ($data, $allowOverselling) {
            
            // 1. Context Logic: Use offline data if available, else current session
            $userId = !empty($data['offline_user_id']) ? $data['offline_user_id'] : auth()->id();
            $orderDate = !empty($data['offline_created_at']) 
                ? Carbon::parse($data['offline_created_at']) 
                : now();

            // 2. Create Order Header
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(8)) . '-' . time(),
                'store_id' => $data['store_id'],
                'user_id' => $userId, 
                'payment_method' => $data['paymentMethod'],
                'total_amount' => $data['total'], // Matches DB Schema
                'subtotal' => $data['total'], // Placeholder, updated below if needed
                'discount_amount' => $data['discount'] ?? 0,
                'status' => 'completed',
                'created_at' => $orderDate,
                'updated_at' => $orderDate,
            ]);

            $calculatedTotal = 0;
            $affectedProductIds = [];

            // 3. Process Items
            foreach ($data['cart'] as $item) {
                $itemTotal = $this->processCartItem($item, $order, $data['store_id'], $allowOverselling, $userId, $orderDate);
                $calculatedTotal += $itemTotal;
                
                // Track standard products to refresh stock for frontend
                if (!empty($item['product_id'])) {
                    $affectedProductIds[] = $item['product_id'];
                }
            }
            
            // Optional: Recalculate total to ensure backend accuracy
            // $order->update(['subtotal' => $calculatedTotal]);

            // 4. Real-time Stock Calculation for Frontend
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
                        'qty' => (int)$inv->total_qty // Frontend expects 'qty' or 'new_stock'
                    ];
                }
            }

            // 5. Send Email Receipt (Queued)
            try {
                $store = Store::find($data['store_id']);
                $user = User::find($userId);
                $recipient = $store->email ?? $user->email ?? null;

                if ($recipient && class_exists(PosOrderReceipt::class)) {
                    Mail::to($recipient)->later(now()->addSeconds(5), new PosOrderReceipt($order, $store));
                }
            } catch (\Exception $e) {
                Log::error("POS Email Error Order #{$order->id}: " . $e->getMessage());
                // Don't fail the transaction just because email failed
            }

            return response()->json([
                'success' => true,
                'message' => 'Checkout successful',
                'order_id' => $order->id,
                'updated_stock' => $updatedStock 
            ], 200);
        });
    }

    /**
     * Process Individual Cart Items (Stock Deduction)
     */
    private function processCartItem($item, $order, $storeId, $allowOverselling, $userId, $date)
    {
        // A. Custom Item Handling (Misc)
        if (empty($item['product_id'])) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => null,
                'product_name' => $item['custom_name'] ?? 'Custom Item',
                'variant_id' => null,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'total' => $item['quantity'] * $item['price'],
                'created_at' => $date,
                'updated_at' => $date,
            ]);
            return $item['price'] * $item['quantity'];
        }

        // B. Standard Product Handling
        $product = Product::find($item['product_id']);
        if(!$product) return 0;

        $multiplier = 1;
        $variantName = null;
        $unitCost = ($product->quantity_in_pack > 0) ? ($product->cost / $product->quantity_in_pack) : $product->cost;

        // Check for Variant
        if (!empty($item['variant_id'])) {
            $variant = Variant::find($item['variant_id']);
            if ($variant) {
                $multiplier = $variant->unit_qty ?? 1;
                $variantName = $variant->unit_type ?? $variant->variant_name;
            }
        }

        $qtyRequired = $item['quantity'] * $multiplier;
        $totalCost = $unitCost * $qtyRequired;

        // Lock Inventory Row for Safety
        $inventory = StoreInventory::where('store_id', $storeId)
                                   ->where('product_id', $product->id)
                                   ->lockForUpdate()
                                   ->first();

        // Stock Deduction Logic
        if ($inventory) {
            if (!$allowOverselling && $inventory->quantity < $qtyRequired) {
                throw new \Exception("Insufficient stock for: " . $product->name);
            }
            $inventory->decrement('quantity', $qtyRequired);
        } else {
            // Create negative stock record if allowed
            if (!$allowOverselling) {
                throw new \Exception("No stock record for: " . $product->name);
            }
            StoreInventory::create([
                'store_id' => $storeId, 
                'product_id' => $product->id, 
                'quantity' => -$qtyRequired
            ]);
        }

        // Create Order Item
        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'variant_id' => $item['variant_id'] ?? null,
            'product_name' => $product->name,
            'variant_name' => $variantName,
            'quantity' => $item['quantity'],
            'price' => $item['price'],
            'total' => $item['quantity'] * $item['price'],
            'cost' => $totalCost, // Track cost for profit reports
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        return $item['price'] * $item['quantity'];
    }

    /**
     * Fallback: Save to JSON file if DB is down
     */
    private function saveOfflineOrder($data)
    {
        $file = storage_path('app/offline_orders.json');
        
        $orderEntry = [
            'id' => (string) Str::uuid(),
            'data' => $data,
            'saved_at' => now()->toIso8601String(),
        ];

        // Safe File Write with Locking
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
}