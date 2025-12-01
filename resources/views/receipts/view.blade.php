<!DOCTYPE html>
<html>

<head>
    <title>Receipt #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-width: 300px;
            margin: 0 auto;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bold {
            font-weight: bold;
        }

        table {
            width: 100%;
        }

        .border-top {
            border-top: 1px dashed #000;
        }

        .my-2 {
            margin: 10px 0;
        }
    </style>
</head>

<body onload="window.print()">
    <div class="text-center">
        <h3 class="my-2">{{ $order->store->name }}</h3>
        <p>{{ $order->store->address }}</p>
        <p>Tel: {{ $order->store->phone }}</p>
    </div>

    <div class="border-top my-2"></div>

    <p>Receipt: #{{ $order->id }}</p>
    <p>Date: {{ $order->created_at->format('Y-m-d H:i') }}</p>
    <p>Cashier: {{ $order->user->name }}</p>

    <div class="border-top my-2"></div>

    <table>
        @foreach($order->items as $item)
            <tr>
                <td colspan="2" class="bold">
                    {{ $item->product->name }}
                    @if($item->variant) <br><small>({{ $item->variant->unit_type }})</small> @endif
                </td>
            </tr>
            <tr>
                <td>{{ $item->quantity }} x {{ number_format($item->price, 2) }}</td>
                <td class="text-right">{{ number_format($item->price * $item->quantity, 2) }}</td>
            </tr>
        @endforeach
    </table>

    <div class="border-top my-2"></div>

    <table>
        <tr class="bold">
            <td>TOTAL</td>
            <td class="text-right">₦{{ number_format($order->total, 2) }}</td>
        </tr>
        <tr>
            <td>Payment</td>
            <td class="text-right">{{ ucfirst($order->payment_method) }}</td>
        </tr>
    </table>

    <div class="text-center my-2">
        <p>Thank you for your patronage!</p>
    </div>
</body>

</html>