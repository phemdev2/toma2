<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class ReceiptController extends Controller
{
    /**
     * Display the receipt for an online order.
     *
     * @param Request $request
     * @param int $orderId
     * @return \Illuminate\View\View
     */
    public function showReceipt(Request $request, $orderId)
    {
        // Retrieve the order with its items
        $order = Order::with('items')->findOrFail($orderId);

        // Retrieve store_id from the request query parameters
        $storeId = $request->query('store_id');

        // You can use $storeId to fetch or display additional information if needed
        $storeName = "Your Store Name"; // You might use $storeId to dynamically set this

        // Pass the order, storeName, and storeId to the view
        return view('receipt', [
            'order' => $order,
            'storeName' => $storeName,
            'storeId' => $storeId
        ]);
    }

    /**
     * Display the offline receipt page.
     *
     * @return \Illuminate\View\View
     */
    public function showOfflineReceipt()
    {
        return view('offline_receipt');
    }
}

