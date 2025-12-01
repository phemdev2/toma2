<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>POS Receipt</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
        }

        .pos-receipt {
            width: 100%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }

        .pos-receipt-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .pos-receipt-header div {
            margin-bottom: 5px;
        }

        .pos-receipt-header h3 {
            margin: 10px 0;
            font-size: 18px;
            font-weight: bold;
        }

        .cashier {
            font-style: italic;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th,
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: center;
        }

        table th {
            background-color: #f8f9fa;
        }

        .footer,
        .order-data {
            text-align: center;
        }

        .footer p {
            margin: 5px 0;
            font-size: 12px;
        }

        .order-data div {
            margin: 5px 0;
        }

        .actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 20px;
        }

        .actions .form-control {
            width: 200px;
        }

        .actions .btn {
            margin: 0 5px;
        }

        #newOrder {
            display: block;
            width: 200px;
            margin: 20px auto;
            text-align: center;
        }

        @media print {
            .actions,
            #newOrder {
                display: none;
            }

            .pos-receipt {
                border: none;
                padding: 0;
                margin: 0;
                box-shadow: none;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row">
            <div class="col-md-12" id="pos">
                <div class="pos-receipt">
                    <div class="pos-receipt-header">
                        <div>De-Omeze Nigeria Limited</div>
                        <div>Tel: 07042712082</div>
                        <div>deomeze@gmail.com</div>
                        <div><a href="https://deomezesupermarket.com.ng" target="_blank">deomezesupermarket.com.ng</a></div>
                        <h3>NAMEZ PHARMACY</h3>
                        <div class="cashier">Served by Manager</div>
                    </div>

                    <table>
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
                                <th colspan="4" class="text-end">Total</th>
                                <th>${{ number_format($order->items->sum(function($item) {
                                    return $item->quantity * $item->price;
                                }), 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="footer">
                        <div class="hours">
                            <p>OPERATIONAL HOURS:</p>
                            <p>Weekdays and Saturdays: 8:00AM to 10:30PM</p>
                            <p>Sundays: 4:00PM - 10:30PM</p>
                        </div>
                        <p>Goods Sold And Paid For In Good Condition Are Not Returnable.</p>
                        <p>THANKS FOR SHOPPING WITH US. PLEASE CALL AGAIN!!!</p>
                    </div>

                    <div class="order-data">
                        <div><strong>Order ID:</strong> {{ $order->id }}</div>
                        <div><strong>Date:</strong> {{ \Carbon\Carbon::parse($order->order_date)->format('Y-m-d H:i:s') }}</div>
                        <div><strong>Payment Method:</strong> {{ ucfirst($order->payment_method) }}</div>
                    </div>

                    <div class="actions">
                        <button class="btn btn-success" onclick="printReceipt()">Print Receipt</button>
                        <form class="send-email d-flex" method="POST" action="#">
                            @csrf
                            <input type="email" class="form-control me-2" placeholder="Email Receipt" name="email" required>
                            <!-- <button class="btn btn-warning" type="submit">Send</button> -->
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
   
    <a href="{{ route('cart.index') }}" id="newOrder" class="btn btn-primary btn-lg">New Order</a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function printReceipt() {
            window.print();
        }
    </script>
</body>

</html>
