<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f9f9f9;
        }
        .cart {
            width: 40%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .cart h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
        }
        .cart table {
            width: 100%;
            border-collapse: collapse;
        }
        .cart table, .cart th, .cart td {
            border: 1px solid #ddd;
        }
        .cart th, .cart td {
            padding: 12px;
            text-align: left;
        }
        .cart th {
            background-color: #f4f4f4;
        }
        .cart .total {
            font-weight: bold;
            margin-top: 20px;
            font-size: 1.2em;
        }
        .delete-icon {
            cursor: pointer;
            color: red;
            font-size: 1.2em;
            text-align: center;
            user-select: none;
        }
        .checkout-buttons button {
            display: block;
            width: 100%;
            padding: 10px;
            font-size: 1em;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 10px;
        }
        .checkout-buttons .clear-cart {
            background-color: #17a2b8;
        }
        .checkout-buttons .checkout {
            background-color: #28a745;
        }
        .checkout-buttons button:hover {
            opacity: 0.9;
        }
        @media (max-width: 600px) {
            .cart table, .cart th, .cart td {
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="cart container">
        <h2>Shopping Cart</h2>
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if(count($cart) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cart as $itemName => $item)
                        <tr>
                            <td>{{ $item['name'] }}</td>
                            <td>${{ number_format($item['price'], 2) }}</td>
                            <td>{{ $item['quantity'] }}</td>
                            <td>${{ number_format($item['price'] * $item['quantity'], 2) }}</td>
                            <td>
                                <form action="{{ route('cart.remove') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="item_name" value="{{ $itemName }}">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <p class="total">Total: ${{ number_format($total, 2) }}</p>
            <div class="checkout-buttons">
                <form action="{{ route('cart.clear') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-info clear-cart">Clear Cart</button>
                </form>
                <form action="{{ route('cart.checkout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-success checkout">Checkout</button>
                </form>
            </div>
        @else
            <p>Your cart is empty!</p>
        @endif
    </div>
</body>
</html>
