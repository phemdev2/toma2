<?php
// app/Http/Controllers/OrderItemController.php

namespace App\Http\Controllers;

use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    public function index()
    {
        // Retrieve all order items
        $orderItems = OrderItem::all();

        // Pass the order items to the view
        return view('order_items', ['orderItems' => $orderItems]);
    }
    public function showOrderItems($orderId)
{
    $orderItems = OrderItem::with(['product', 'variant']) // Eager load related models
        ->where('order_id', $orderId)
        ->get();

    return view('order_items.index', compact('orderItems'));
}
}
