<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        @media print {
            body { margin: 0; font-size: 14px; }
            .receipt { width: 100%; max-width: 100%; } /* Full width for print */
            table { width: 100%; }
            th, td { font-size: 14px; }
            .no-print { display: none; } /* Hide elements with no-print class in print */
        }
    </style>
</head>
<body class="bg-gray-100 p-4">

<div class="receipt mx-auto bg-white p-4 border border-gray-300 rounded-lg shadow-md sm:w-80 md:w-96 lg:w-1/2">

    <div class="header text-center mb-2">
        <h1 class="text-xl font-bold">De-Omeze Nigeria Limited</h1>
        <p class="text-sm">Tel: 07042712082</p>
        <p class="text-sm">Email: deomeze@gmail.com</p>
        <p class="text-sm"><a href="https://deomezemart.com.ng" class="text-blue-500" target="_blank">deomezemart.com.ng</a></p>
        <h3 class="text-lg mt-2">{{ Auth::user()->store->name }}</h3>
        <div class="text-sm">Served by: <strong>{{ Auth::user()->name }}</strong></div>
    </div>

    <h3 class="text-center text-lg font-semibold mb-4">Receipt</h3>
    <div class="details mb-4 text-sm">
        <p><strong>Order ID:</strong> 101{{ $order->id }} || <strong>Order Date:</strong> {{ $order->order_date }}</p>
        <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
    </div>

    <table class="w-full border-collapse mb-4 text-sm">
        <thead>
            <tr>
                <th class="border-b border-gray-300 text-left p-2">Item</th>
                <th class="border-b border-gray-300 text-left p-2">Variant</th>
                
                <th class="border-b border-gray-300 text-left p-2">Qty</th>
                <th class="border-b border-gray-300 text-left p-2">Price</th>
                <th class="border-b border-gray-300 text-left p-2">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td class="border-b border-gray-200 p-2">{{ $item->product->name }}</td>
                    <td class="border-b border-gray-200 p-2">{{ $item->variant ? $item->variant->unit_type : 'Unit' }}</td>
                    <td class="border-b border-gray-200 p-2">{{ $item->quantity }}</td>
                    <td class="border-b border-gray-200 p-2">&#8358;{{ number_format($item->price, 2) }}</td>
                    <td class="border-b border-gray-200 p-2">&#8358;{{ number_format($item->price * $item->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
   
    <div class="total text-right font-bold text-lg mb-4">
        Total: &#8358;{{ number_format($order->items->sum(fn($item) => $item->price * $item->quantity), 2) }}
    </div>

    <div class="footer text-center">
        <p class="text-sm text-gray-600">Thank you for your purchase!</p>
        <p class="text-sm text-gray-600">Visit us again!</p>
    </div>

    <!-- Print and New Order Buttons -->
    <div class="flex justify-end space-x-4 mt-6 no-print">
        <button class="px-4 py-2 bg-green-500 text-white rounded shadow" onclick="printReceipt()">Print Receipt</button>
        <a href="{{ url()->previous() }}" id="newOrder" class="px-4 py-2 bg-blue-500 text-white rounded shadow">New Order</a>
    </div>
</div>

<!-- Print and JavaScript -->
<script>
    function printReceipt() {
        window.print(); // Trigger the print dialog
    }

    document.addEventListener('DOMContentLoaded', () => {
        const newOrderButton = document.getElementById('newOrder');
        newOrderButton.addEventListener('click', (event) => {
            event.preventDefault();
            window.history.back(); // Go back to the previous page
        });
    });
</script>

</body>
</html>
