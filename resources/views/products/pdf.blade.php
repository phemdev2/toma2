<!DOCTYPE html>
<html>
<head>
    <title>Product List</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <h1>Product List</h1>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Barcode</th>
                <th>Cost</th>
                <th>Sale</th>
                <th>Variants</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $product)
                <tr>
                    <td>{{ $product->name }}</td>
                    <td>{{ $product->barcode }}</td>
                    <td>${{ number_format($product->cost, 2) }}</td>
                    <td>${{ number_format($product->sale, 2) }}</td>
                    <td>
                        @foreach($product->variants as $variant)
                            <div>{{ $variant->unit_type }} - {{ $variant->unit_qty }} units - ${{ number_format($variant->price, 2) }}</div>
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>