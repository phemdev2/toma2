<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction; // Ensure you have a Transaction model
use App\Models\TransactionItem; // Ensure you have a TransactionItem model
use Illuminate\Support\Facades\DB; // Import DB for transactions

class CheckoutController extends Controller
{
    public function cashCheckout(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'cart_data' => 'required|json',
        ]);

        $cartData = json_decode($request->input('cart_data'), true);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Save transaction details
            $transaction = Transaction::create([
                'type' => 'cash',
                'total' => array_sum(array_column($cartData, 'total')), // Calculate total
            ]);

            foreach ($cartData as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_name' => $item['name'],
                    'variant' => $item['variant'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                ]);
            }

            // Clear the cart
            $request->session()->forget('cart');

            // Commit the transaction
            DB::commit();

            // Redirect to the receipt page
            return redirect()->route('receipt', ['id' => $transaction->id])
                             ->with('success', 'Order placed successfully with Cash Checkout.');

        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred during Cash Checkout: ' . $e->getMessage());
        }
    }

    public function posCheckout(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'cart_data' => 'required|json',
        ]);

        $cartData = json_decode($request->input('cart_data'), true);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Save transaction details
            $transaction = Transaction::create([
                'type' => 'pos',
                'total' => array_sum(array_column($cartData, 'total')), // Calculate total
            ]);

            foreach ($cartData as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_name' => $item['name'],
                    'variant' => $item['variant'],
                    'price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['total'],
                ]);
            }

            // Clear the cart
            $request->session()->forget('cart');

            // Commit the transaction
            DB::commit();

            // Redirect to the receipt page
            return redirect()->route('receipt', ['id' => $transaction->id])
                             ->with('success', 'Order placed successfully with POS Checkout.');

        } catch (\Exception $e) {
            // Rollback in case of error
            DB::rollback();
            return redirect()->back()->with('error', 'An error occurred during POS Checkout: ' . $e->getMessage());
        }
    }

    public function showReceipt($id)
    {
        // Fetch the transaction with its items
        $transaction = Transaction::with('items')->findOrFail($id);

        return view('receipt', compact('transaction'));
    }
}