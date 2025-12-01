<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Success</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #4CAF50;
        }
        .order-summary {
            margin: 20px 0;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fafafa;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin: 10px 0;
            border: none;
            border-radius: 4px;
            background-color: #4CAF50;
            color: #fff;
            text-decoration: none;
            text-align: center;
            font-size: 16px;
        }
        .button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Thank You!</h1>
        <p>Your order has been placed successfully.</p>
        <div class="order-summary">
            <h2>Order Summary</h2>
            <!-- Replace with dynamic order details if available -->
            <p><strong>Order Number:</strong> #123456789</p>
            <p><strong>Date:</strong> {{ \Carbon\Carbon::now()->format('F j, Y') }}</p>
            <p><strong>Total Amount:</strong> $99.99</p>
        </div>
        <a href="{{ route('home') }}" class="button">Return to Home</a>
        <a href="{{ route('shop') }}" class="button">Continue Shopping</a>
    </div>
</body>
</html>
