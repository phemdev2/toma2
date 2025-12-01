<?php 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartController extends Controller
{
    /**
     * Display the list of products for the selected store.
     */
    public function index(Request $request)
    {
        // Get store_id from request, default to 1
        $storeId = $request->input('store_id', 1);

        // Fetch products with their variants and inventories for the selected store
        $products = Product::with(['variants', 'inventories' => function ($query) use ($storeId) {
            $query->where('store_id', $storeId);
        }])->get();

        return view('pos.index', compact('products', 'storeId'));
    }

    /**
     * Display the cart page.
     */
    public function showCart()
    {
        // Retrieve cash balance from your model or service
        $cashBalance = Auth::user()->cashBalance; // Example, adjust as needed
    
        return view('cart', compact('cashBalance'));
    }
    

    /**
     * Display the store selection page.
     */
    public function selectStorePage(Request $request)
    {
        $storeId = $request->input('store_id', 1);
        return view('cart.select', compact('storeId'));
    }

    /**
     * Clear the cart.
     */
    public function clearCart(Request $request)
    {
        // Example of clearing the cart using session
        $request->session()->forget('cart');
        return response()->json(['success' => true]);
    }

    /**
     * Handle the checkout process.
     */
    public function checkout(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'cart' => 'required|array',
            'checkout_type' => 'required|string'
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Calculate the total amount
            $cart = $request->input('cart');
            $total = 0;

            // Validate and calculate total for each cart item
            foreach ($cart as $item) {
                $this->validateCartItem($item);
                $total += $item['price'] * $item['quantity'];
            }

            // Create a new Transaction
            $transaction = Transaction::create([
                'type' => $request->input('checkout_type'),
                'total' => $total
            ]);

            // Create TransactionItem records
            foreach ($cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'name' => $item['name'],
                    'variant' => $item['variant'] ?? null, // Default to null if not provided
                    'unit_qty' => $item['unit_qty'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity']
                ]);
            }

            // Commit the transaction
            DB::commit();

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            // Rollback the transaction in case of error
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'An error occurred during checkout.'], 500);
        }
    }

    /**
     * Validate individual cart items.
     *
     * @param array $item
     * @throws ValidationException
     */
    protected function validateCartItem(array $item)
    {
        $validator = \Validator::make($item, [
            'name' => 'required|string|max:255',
            'variant' => 'nullable|string|max:255',
            'unit_qty' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
