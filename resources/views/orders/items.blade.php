<!-- resources/views/orders/items.blade.php -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Items for Order #{{ $order->id }}</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}"> <!-- Link to your CSS file -->
</head>
<body>
    <div class="container mx-auto mt-5">
        <h1 class="text-2xl font-bold mb-4">Order Items for Order #{{ $order->id }}</h1>
        <h2 class="text-lg mb-2">Payment Method: {{ $order->payment_method }}</h2>
        <h2 class="text-lg mb-4">Order Date: {{ $order->order_date }}</h2>

        <table class="min-w-full border-collapse border border-gray-200">
            <thead>
                <tr>
                    <th class="border border-gray-300 px-2 py-1">Product</th>
                    <th class="border border-gray-300 px-2 py-1">Variant</th>
                    <th class="border border-gray-300 px-2 py-1">Quantity</th>
                    <th class="border border-gray-300 px-2 py-1">Price</th>
                    <th class="border border-gray-300 px-2 py-1">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td class="border border-gray-300 px-2 py-1">{{ $item->product->name ?? 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1">{{ $item->variant->variant_name ?? 'N/A' }}</td>
                        <td class="border border-gray-300 px-2 py-1">{{ $item->quantity }}</td>
                        <td class="border border-gray-300 px-2 py-1">&#8358;{{ number_format($item->price, 2) }}</td>
                        <td class="border border-gray-300 px-2 py-1">&#8358;{{ number_format($item->totalPrice(), 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="mt-4">
            <strong>Total Amount:</strong> &#8358;{{ number_format($order->totalPrice(), 2) }}
        </div>
    </div>
    {{ $orderItems->links() }}
</body>
</html>