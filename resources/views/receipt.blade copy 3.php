<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        #newOrder {
            background-color: #007bff;
            width: 100%;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 18px;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
            position: fixed;
            bottom: 0;
            left: 0;
        }

        #newOrder:hover {
            background-color: #0056b3;
        }
        @media print {
            .no-print {
                display: none;
            }
            body * {
                visibility: hidden;
            }
            
        }
    </style>
</head>
<body>
<div class="container">
<div>De-Omeze Nigeria Limited</div>
                            <div>Tel: 07042712082</div>
                            <div>deomeze@gmail.com</div>
                            <div>https://deomezesupermarket.com.ng</div>
                            <h3>NAMEZ PHARMACY</h3>
                            <div class="cashier">Served by Manager</div>

    <p><strong>Order ID:</strong> {{ $order->id }}</p>
    <p><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d H:i:s') }}</p>
    <p><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</p>

    <h3 class="my-3">Items</h3>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Product</th>
                <th>Variant</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->variant ? $item->variant->unit_type : 'N/A' }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>${{ number_format($item->price, 2) }}</td>
                    <td>${{ number_format($item->quantity * $item->price, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="4" class="text-right">Total</th>
                <th>${{ number_format($order->items->sum(function($item) {
                    return $item->quantity * $item->price;
                }), 2) }}</th>
            </tr>
        </tfoot>
    </table>
    <div class="footer mt-2">
                            <div class="hours">
                                <p>OPERATIONAL HOURS:</p>
                                <p>Weekdays and Saturdays: 8:00AM to 10:30PM</p>
                                <p>Sundays: 4:00PM - 10:30PM</p>
                            </div>
                            <p>Goods Sold And Paid For In Good Condition Are Not Returnable.</p>
                            <p>THANKS FOR SHOPPING WITH US. PLEASE CALL AGAIN!!!</p>
                        </div>

                        <div class="order-data mt-2">
                            <div>Order 00444-009-0001</div>
                            <div>08/16/2024 11:05:34</div>
                        </div>
    <a href="{{ url('/') }}" class="btn btn-primary no-print">Back to Home</a>
    <button class="btn btn-secondary no-print" onclick="printReceipt()">Print</button>
</div>
<a href="{{ route('pos.index') }}" id="newOrder" class="btn btn-primary btn-lg">New Order</a>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
function printReceipt() {
    window.print();
}
</script>
</body>
</html>
